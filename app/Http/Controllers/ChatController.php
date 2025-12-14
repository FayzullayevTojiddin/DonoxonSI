<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Data;
use Gemini\Laravel\Facades\Gemini;
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
            \Log::error('donoxonReply xato: ' . $e->getMessage());
            return "Kechirasiz, hozirda javob berishda xatolik yuz berdi.";
        }
    }

  private function buildPrompt(string $text, $intents): string
{
    return
"VAZIFA:
Foydalanuvchi savolini tahlil qiling va FAQAT 100% TO‘LIQ VA ANIQLIK BILAN MOS KELADIGAN BITTA intentni tanlang.
Agar 100% aniqlik BO‘LMASA — HECH QACHON intent tanlamang.

BU AI FAQAT QAT’IY MANTIQ BILAN ISHLAYDI.
UMUMLASHTIRISH, TAXMIN QILISH, YAQIN MA’NO QAT’IYAN TAQIQLANADI.

──────────────── QAT’IY QOIDALAR ────────────────

1️⃣ Agar savolda FAQAT salomlashuv bo‘lsa
→ FAOL intent: 'Salom'

2️⃣ Agar savolda salomlashuv + real savol bo‘lsa
→ salomlashuvni BUTUNLAY INKOR QILING
→ faqat real savolni tahlil qiling

3️⃣ Intent FAQAT quyidagi SHARTLAR BARCHASI bajarilgandagina tanlanadi:
   ✔ Savolda ANIQ lavozim nomi OCHIQ aytilgan
   ✔ Lavozim intentdagi lavozim bilan SO‘ZMA-SO‘Z mos
   ✔ Hech qanday umumiylik yoki keng talqin YO‘Q

4️⃣ QAT’IYAN TAQIQLANADI:
   ❌ Lavozimni umumiy ma’noda talqin qilish
   ❌ “Boshliq”, “rahbar”, “mas’ul” kabi so‘zlarni
      hokim, direktor yoki boshqa lavozimlarga tenglashtirish
   ❌ Bir lavozimni boshqa lavozim o‘rniga qo‘yish

5️⃣ LAVOZIMLAR BIR-BIRIGA TENG EMAS (ESLAB QOL):
   - Hokim ≠ Ichki ishlar boshlig‘i
   - Ichki ishlar boshlig‘i ≠ prokuror
   - Hokim ≠ bo‘lim boshlig‘i
   - Rahbar so‘zi ANIQ lavozim EMAS

6️⃣ Agar savolda:
   - \"Ichki ishlar boshlig‘i\" so‘zi bo‘lsa
   → FAQAT shu lavozimga MOS intent tanlanadi
   → Hokim, rahbar yoki boshqa lavozim QAT’IYAN tanlanmaydi

7️⃣ Agar savol Uzun tumani bilan bog‘liq bo‘lsa,
   lekin RO‘YXATDAGI intentlardan BIRORTASI HAM 100% MOS KELMASA
   → MAJBURAN 'Javob topilmaganda' key li intentni tanlang

8️⃣ Agar savol Uzun tumani bilan bog‘liq BO‘LMASA
   (Toshkent, Termiz, boshqa hududlar)
   → id = null qaytaring

9️⃣ Agar savolda lavozim:
   - noaniq
   - umumiy
   - qisqartirilgan
   - chalkash
   bo‘lsa
   → 'Javob topilmaganda' intentni tanlang

10️⃣ ASOSIY QOIDA:
   → 100% aniqlik = intent bor
   → 99% yoki kamroq = intent YO‘Q

──────────────── FOYDALANUVCHI SAVOLI ────────────────
\"{$text}\"

──────────────── INTENTLAR RO‘YXATI ────────────────
".json_encode(
        $intents->map(fn ($i) => [
            'id' => $i->id,
            'key' => $i->key,
        ])->toArray(),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    )."

──────────────── JAVOB FORMATI ────────────────
FAQAT JSON. HECH QANDAY IZOH YO‘Q.

{
  \"id\": <faqat 100% mos kelsa intent_id, aks holda null yoki 'Javob topilmaganda'>,
  \"confidence\": <1.0 yoki 0.0>
}

ESLATMA:
- Intent tanlansa → confidence = 1.0
- Intent tanlanmasa → confidence = 0.0
";
}
}