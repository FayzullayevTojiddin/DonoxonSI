<!doctype html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DonoxonSI haqida</title>

    <style>
        :root{
            --bg:#0f172a;
            --card:#1e293b;
            --text:#f1f5f9;
            --muted:#94a3b8;
            --border:rgba(255,255,255,.08);

            /* Claude / olovrang */
            --accent:#f59e0b;
            --accent-dark:#d97706;
        }

        body{
            margin:0;
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Arial,sans-serif;
            background:var(--bg);
            color:var(--text);
        }

        .container{
            max-width:960px;
            margin:0 auto;
            padding:48px 20px;
        }

        h1{
            font-size:34px;
            margin-bottom:6px;
        }

        .subtitle{
            color:var(--muted);
            margin-bottom:36px;
            font-size:15px;
        }

        .card{
            background:var(--card);
            border-radius:18px;
            padding:30px;
            margin-bottom:26px;
            border:1px solid var(--border);
        }

        .card h2{
            margin:0 0 14px;
            font-size:22px;
            display:flex;
            align-items:center;
            gap:10px;
            color:var(--accent);
        }

        .card p{
            line-height:1.75;
            color:#e5e7eb;
        }

        ul{
            padding-left:18px;
        }

        li{
            margin-bottom:10px;
        }

        /* LINKS AS CARDS */
        .links{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
            gap:18px;
            margin-top:18px;
        }

        .link-card{
            display:block;
            background:linear-gradient(
                135deg,
                rgba(245,158,11,.15),
                rgba(217,119,6,.08)
            );
            border:1px solid rgba(245,158,11,.35);
            border-radius:16px;
            padding:20px;
            color:var(--text);
            text-decoration:none;
            transition:.25s ease;
        }

        .link-card:hover{
            transform:translateY(-4px);
            box-shadow:0 10px 30px rgba(245,158,11,.25);
            border-color:var(--accent);
        }

        .link-title{
            font-weight:600;
            font-size:16px;
            margin-bottom:6px;
        }

        .link-desc{
            font-size:14px;
            color:var(--muted);
        }

        .back{
            display:inline-block;
            margin-top:34px;
            color:var(--muted);
            text-decoration:none;
            font-size:14px;
        }

        .back:hover{
            color:var(--accent);
        }

        @media(max-width:600px){
            h1{font-size:28px}
            .container{padding:36px 16px}
        }
    </style>
</head>
<body>

<div class="container">

    <h1>DonoxonSI</h1>
    <div class="subtitle">
        Uzun tumani uchun sun’iy intellekt yordamchi tizimi
    </div>

    <div class="card">
        <h2>📌 DonoxonSI nima?</h2>
        <p>
            DonoxonSI — fuqarolarga tezkor va tushunarli javob berish,
            davlat tashkilotlariga yo‘naltirish hamda murojaatlarni
            qabul qilish uchun ishlab chiqilgan sun’iy intellekt tizimidir.
        </p>
    </div>

    <div class="card">
        <h2>⚙️ Asosiy imkoniyatlar</h2>
        <ul>
            <li>Tabiiy tilda savol va javoblar</li>
            <li>Tashkilotlar bo‘yicha aniqlashtirilgan javoblar</li>
            <li>Murojaat qoldirish imkoniyati</li>
            <li>Doimiy yangilanadigan bilimlar bazasi</li>
        </ul>
    </div>

    <div class="card">
        <h2>🔗 Foydali havolalar</h2>

        <div class="links">

            <a href="https://uzun.uz" target="_blank" class="link-card">
                <div class="link-title">Uzun tumani rasmiy sayti</div>
                <div class="link-desc">
                    Tuman faoliyati va yangiliklar
                </div>
            </a>

            <a href="https://t.me/uzun_ax_x" target="_blank" class="link-card">
                <div class="link-title">Uzun tuman Telegram kanali</div>
                <div class="link-desc">
                    Tuman faoliyati va yangiliklar
                </div>
            </a>

            <a href="https://gov.uz" target="_blank" class="link-card">
                <div class="link-title">Hukumat portali</div>
                <div class="link-desc">
                    Davlat xizmatlari va rasmiy ma’lumotlar
                </div>
            </a>
        </div>
    </div>

    <a href="/" class="back">← Chatga qaytish</a>

</div>

</body>
</html>