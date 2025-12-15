<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Launching Soon | Video KYC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #020617);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .container {
            text-align: center;
            max-width: 600px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        h1 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        h1 span {
            color: #38bdf8;
        }

        p {
            font-size: 16px;
            color: #cbd5f5;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .divider {
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, #38bdf8, #22c55e);
            margin: 0 auto 30px;
            border-radius: 999px;
        }

        .footer-text {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 30px;
        }

        .footer-text span {
            color: #e5e7eb;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="badge">🚀 Coming Soon</div>

    <h1>Video <span>KYC</span> Platform</h1>

    <div class="divider"></div>

    <p>
        We are building a secure, real-time Video KYC platform with
        manual verification, document upload, and live video validation.
        <br><br>
        Our system is currently under development and will be live soon.
    </p>

    <div class="footer-text">
        © {{ date('Y') }} <span>{{ config('app.name', 'Video KYC') }}</span>
        <br>Launching Soon
    </div>
</div>

</body>
</html>
