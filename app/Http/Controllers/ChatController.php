<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Data;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Enums\UserRole;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\NotFoundData;

class ChatController extends Controller
{

    public function index()
    {
        return view('chat.index', [
            'userRoles' => UserRole::toArray(),
        ]);
    }

    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $text = trim($request->input('message'));

        $reply = $this->donoxonReply($text);

        return response()->json([
            'status' => 'ok',
            'name' => 'DonoxonSI',
            'reply' => $reply,
        ]);
    }

    public function submitRequest(Request $request)
    {
        $key = 'submit-request:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {

            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'status'  => 'blocked',
                'message' => "Siz juda ko‘p xabar yubordingiz. Iltimos " .
                            ceil($seconds / 60) . " daqiqadan keyin urinib ko‘ring.",
            ], 429);
        }
        RateLimiter::hit($key, 60*60);

        try {
            $validated = $request->validate([
                'full_name'    => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'organization' => ['required', new Enum(UserRole::class)],
                'request'      => 'required|string|max:5000',
            ], [
                'full_name.required'    => 'Ism-familiyangizni kiriting',
                'full_name.max'         => 'Ism-familiya juda uzun',

                'phone_number.required' => 'Telefon raqamingizni kiriting',
                'phone_number.max'      => 'Telefon raqam noto‘g‘ri',

                'organization.required' => 'Tashkilotni tanlang',
                'organization.enum'     => 'Noto‘g‘ri tashkilot tanlandi',

                'request.required'      => 'Xabar matnini kiriting',
                'request.max'           => 'Xabar juda uzun',
            ]);

            RequestModel::create([
                'full_name' => $validated['full_name'],
                'request'   => $validated['request'],
                'readed'    => false,
                'where' => UserRole::from($validated['organization'])->value,
                'details_from' => [
                    'phone_number' => $validated['phone_number'],
                    'organization' => $validated['organization'],
                    'ip'           => $request->ip(),
                    'user_agent'   => $request->userAgent(),
                    'submitted_at' => now()->toDateTimeString(),
                ],
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'So‘rovingiz muvaffaqiyatli yuborildi! Tez orada javob beramiz.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Iltimos, barcha maydonlarni to‘g‘ri to‘ldiring',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Xatolik yuz berdi. Iltimos, qaytadan urinib ko‘ring.',
            ], 500);
        }
    }

    protected function donoxonReply(string $text): string
    {
        $key = 'donoxon-chat:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 20)) {
            $seconds = RateLimiter::availableIn($key);

            return "❌ Juda ko‘p so‘rov yubordingiz. Iltimos "
                . ceil($seconds / 60)
                . " daqiqadan keyin yana urinib ko‘ring.";
        }

        RateLimiter::hit($key, 300);

        try {
            if (mb_strlen($text) < 2) {
                return "Iltimos, savolni biroz batafsilroq yozing 🙂";
            }
            
            $question = trim(mb_strtolower($text));
            $cacheKey = 'intent_answer_' . md5($question);

            if (Cache::has($cacheKey)) {
                $cachedId = Cache::get($cacheKey);
                $item = Data::find($cachedId);
                return $item?->value ?? "Kechirasiz, ma'lumot topilmadi.";
            }

            $intents = Data::where('status', true)->get(['id', 'key', 'value']);
            if ($intents->isEmpty()) {
                return "Kechirasiz, hozircha ma'lumotlar mavjud emas.";
            }

            $prompt = $this->buildPrompt($text, $intents);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Siz intent tanlovchisiz. Javobni FAQAT JSON formatda bering.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.0,
                    'response_format' => ['type' => 'json_object']
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI API xato: ' . $response->body());
                return "Kechirasiz, hozirda javob berishda xatolik yuz berdi.";
            }

            $data = $response->json();
            $raw = $data['choices'][0]['message']['content'] ?? null;

            if (!$raw) {
                return "Kechirasiz, aniq ma'lumot topilmadi.";
            }

            $parsed = json_decode($raw, true);
            $intentId = $parsed['id'] ?? null;
            $confidence = (float) ($parsed['confidence'] ?? 0);

            if ($intentId === null) {
                return "Men faqat Uzun tumani doirasidagi savollarga javob bera olaman.";
            }

            if($intentId === 39) {
                NotFoundData::firstOrCreate(
                    ['intent' => trim(mb_strtolower($text))],
                        [
                            'details_from' => [
                                'ip' => request()->ip(),
                                'user_agent' => request()->userAgent(),
                                'asked_at' => now(),
                            ],
                        ]
                    );
            }

            $item = Data::find($intentId);
            if (!$item) {
                return "Kechirasiz, ma'lumot topilmadi.";
            }

            Cache::put($cacheKey, $intentId, now()->addDays(7));

            return $item->value;

        } catch (\Throwable $e) {
            Log::error('donoxonReply xato: ' . $e->getMessage());
            return "Kechirasiz, hozirda javob berishda xatolik yuz berdi.";
        }
    }

  private function buildPrompt(string $text, $intents): string
{
    return
"VAZIFA:
Foydalanuvchi savoliga mos intentni ANIQLIK bilan tanlang.

QOIDALAR:

1️⃣ Agar savol Uzun tumani bilan bog‘liq bo‘lsa
   va savolda:
   - aniq LAVOZIM
   - yoki aniq BO‘LIM / TASHKILOT
   so‘ralgan bo‘lsa
   → mos intentni tanlang.

2️⃣ LAVOZIM va BO‘LIM tushunchalari FARQLANADI:
   - \"ichki ishlar boshlig‘i\" → lavozim
   - \"ichki ishlar bo‘limi\" → tashkilot

3️⃣ Agar savolda:
   - \"haqida ma’lumot\"
   - \"nima bilan shug‘ullanadi\"
   - \"vazifalari\"
   kabi so‘zlar bo‘lsa
   → bu UMUMIY MA’LUMOT so‘rovi hisoblanadi.

4️⃣ Agar savol Uzun tumani bilan bog‘liq,
   lekin RO‘YXATDAGI intentlardan hech biri mos kelmasa
   → 'Javob topilmaganda' intentni tanlang.

5️⃣ Agar savol Uzun tumani bilan bog‘liq EMAS
   → id = null.

FOYDALANUVCHI SAVOLI:
\"{$text}\"

INTENTLAR:
".json_encode(
        $intents->map(fn ($i) => [
            'id' => $i->id,
            'key' => $i->key,
        ])->toArray(),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    )."

JAVOB FORMATI (FAQAT JSON):
{
  \"id\": <intent_id | 'Javob topilmaganda' | null>,
  \"confidence\": <1.0 yoki 0.0>
}
";
}

    public function VoiceToText(Request $request) {
        try {
            $audioFile = $request->file('audio');
            
            if (!$audioFile) {
                return response()->json(['error' => 'Audio fayl topilmadi'], 400);
            }

            // 1. STT (Speech-to-Text) - Ovozdan matnga
            $sttResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('UZBEKVOICE_API_KEY'),
            ])->attach(
                'file', 
                file_get_contents($audioFile->getPathname()), 
                $audioFile->getClientOriginalName()
            )->post('https://uzbekvoice.ai/api/v1/stt', [
                'return_offsets' => false,
                'run_diarization' => false,
                'language' => 'uz',
                'blocking' => false,
            ]);

            if (!$sttResponse->successful()) {
                Log::error('STT API xatosi', [
                    'status' => $sttResponse->status(),
                    'response' => $sttResponse->json()
                ]);
                
                return response()->json([
                    'error' => 'Ovozni textga o\'girishda xatolik',
                    'details' => $sttResponse->json()
                ], 500);
            }

            $sttData = $sttResponse->json();
            
            // STT response: {"id": "string", "result": {"text": "string"}, "state": "string"}
            $text = $sttData['result']['text'] ?? '';
            $text = trim($text);

            if (empty($text)) {
                Log::warning('STT bo\'sh text qaytardi', ['stt_data' => $sttData]);
                return response()->json([
                    'error' => 'Text tanilmadi', 
                    'stt_response' => $sttData
                ], 400);
            }

            Log::info('STT natijasi', ['text' => $text]);

            // 2. AI javobi olish
            $reply = $this->donoxonReply($text);
            Log::info('AI javobi', ['reply' => $reply]);

            // 3. TTS (Text-to-Speech) - Matndan ovozga
            $ttsResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('UZBEKVOICE_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://uzbekvoice.ai/api/v1/tts', [
                'text' => $reply,
                'model' => 'lola',
                'blocking' => false,
            ]);

            if (!$ttsResponse->successful()) {
                Log::error('TTS API xatosi', [
                    'status' => $ttsResponse->status(),
                    'response' => $ttsResponse->json()
                ]);
                
                return response()->json([
                    'success' => true,
                    'text' => $text,
                    'reply' => $reply,
                    'audio_url' => null,
                    'warning' => 'Ovoz yaratishda xatolik'
                ]);
            }

            $ttsData = $ttsResponse->json();
            
            // TTS response: {"id": "...", "result": {"url": "..."}, "status": "SUCCESS"}
            $audioUrl = $ttsData['result']['url'] ?? null;

            if (empty($audioUrl)) {
                Log::warning('TTS URL topilmadi', ['tts_data' => $ttsData]);
                return response()->json([
                    'success' => true,
                    'text' => $text,
                    'reply' => $reply,
                    'audio_url' => null,
                    'warning' => 'Ovozli fayl URL i topilmadi'
                ]);
            }

            Log::info('TTS natijasi', ['audio_url' => $audioUrl]);

            return response()->json([
                'success' => true,
                'text' => $text,
                'reply' => $reply,
                'audio_url' => $audioUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('VoiceToText xatosi', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Server xatosi: ' . $e->getMessage()
            ], 500);
        }
    }
}