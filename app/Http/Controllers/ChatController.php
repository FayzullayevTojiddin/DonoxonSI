<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Data;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Enums\RequestType;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\RateLimiter;

class ChatController extends Controller
{

    public function index()
    {
        return view('chat.index', [
            'requestTypes' => RequestType::toArray(),
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

        if (RateLimiter::tooManyAttempts($key, 10)) {

            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'status'  => 'blocked',
                'message' => "Siz juda ko‘p xabar yubordingiz. Iltimos " .
                            ceil($seconds / 60) . " daqiqadan keyin urinib ko‘ring.",
            ], 429);
        }
        RateLimiter::hit($key, 300);

        try {
            $validated = $request->validate([
                'full_name'    => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'organization' => ['required', new Enum(RequestType::class)],
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

                'details_from' => [
                    'phone_number' => $validated['phone_number'],
                    'organization' => $validated['organization'],
                    'organization_label' => RequestType::from(
                        $validated['organization']
                    )->label(),

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

            // Cache tekshirish
            if (Cache::has($cacheKey)) {
                $cachedId = Cache::get($cacheKey);
                $item = Data::find($cachedId);
                return $item?->value ?? "Kechirasiz, ma'lumot topilmadi.";
            }

            // TEZKOR SALOM TEKSHIRUVI (AI ga yubormasdan)
            $salomPatterns = ['salom', 'assalomu', 'hayr', 'qalaysan', 'qalaysiz', 'hello', 'hi'];
            foreach ($salomPatterns as $pattern) {
                if (str_contains($question, $pattern)) {
                    $salomIntent = Data::where('key', 'Salom')->first();
                    if ($salomIntent) {
                        Cache::forever($cacheKey, $salomIntent->id);
                        return $salomIntent->value;
                    }
                }
            }

            $intents = Data::where('status', true)->get(['id', 'key', 'value']);
            if ($intents->isEmpty()) {
                return "Kechirasiz, hozircha ma'lumotlar mavjud emas.";
            }

            // AI ga so'rov
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
                \Log::error('OpenAI API xato: ' . $response->body());
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

            if ($confidence < 0.35) { // 0.40 dan 0.35 ga tushirildi
                return "Kechirasiz, aniq ma'lumot topilmadi.";
            }

            $item = Data::find($intentId);
            if (!$item) {
                return "Kechirasiz, ma'lumot topilmadi.";
            }

            Cache::put($cacheKey, $intentId, now()->addDays(7)); // forever emas, 7 kun

            return $item->value;

        } catch (\Throwable $e) {
            \Log::error('donoxonReply xato: ' . $e->getMessage());
            return "Kechirasiz, hozirda javob berishda xatolik yuz berdi.";
        }
    }

    private function buildPrompt(string $text, $intents): string
    {
        return 
    "VAZIFA: Foydalanuvchi savolini tahlil qiling va ENG MOS intentni aniqlang.

    MUHIM QOIDALAR:
    1️⃣ SALOMLASHUV so'zlari (salom, assalomu alaykum, hayr, qalaysan, ishlar qalaysiz) → DOIMO \"Salom\" intentini tanlang
    2️⃣ Uzun tumani bilan bog'liq savollar → mos intentni tanlang  
    3️⃣ Boshqa tuman/shahar savollari (Termiz, Toshkent) → id = null
    4️⃣ So'z emas, SAVOL MA'NOSI muhim!

    FOYDALANUVCHI SAVOLI:
    \"{$text}\"

    INTENTLAR RO'YXATI:
    ".json_encode(
        $intents->map(fn ($i) => [
            'id' => $i->id,
            'nom' => $i->key,
            'tavsif' => mb_substr($i->value, 0, 120)
        ])->toArray(),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    )."

    JAVOB FORMATI (faqat JSON):
    {
    \"id\": <intent_id yoki null>,
    \"confidence\": <0.0 dan 1.0 gacha>
    }";
    }
}