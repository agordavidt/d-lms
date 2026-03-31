<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Link Expired — G-Luper</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #1e293b;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            max-width: 460px;
            width: 100%;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            padding: 36px 32px 28px;
            text-align: center;
        }

        .icon-wrap {
            width: 56px; height: 56px;
            background: rgba(255,255,255,.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }

        .card-header h1 {
            font-size: 20px; font-weight: 700;
            color: #ffffff; margin-bottom: 6px;
        }

        .card-header p {
            font-size: 14px; color: rgba(255,255,255,.85);
        }

        .card-body {
            padding: 32px;
            text-align: center;
        }

        .body-text {
            font-size: 15px; line-height: 1.65;
            color: #475569; margin-bottom: 28px;
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            font-size: 15px; font-weight: 600;
            padding: 13px 32px;
            border-radius: 999px;
            text-decoration: none;
            transition: background .15s;
        }

        .btn:hover { background: #1d4ed8; }

        .divider {
            border: none; border-top: 1px solid #e2e8f0;
            margin: 28px 0;
        }

        .footer-note {
            font-size: 13px; color: #94a3b8; line-height: 1.6;
        }

        /* Logo bar */
        .logo-bar {
            padding: 20px 32px 0;
            display: flex; align-items: center; gap: 8px;
        }

        .logo-mark {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #fff;
        }

        .logo-text {
            font-size: 17px; font-weight: 700; color: #0f172a;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="logo-bar">
        <div class="logo-mark">G</div>
        <span class="logo-text">Luper</span>
    </div>

    <div class="card-header">
        <div class="icon-wrap">
            {{-- Clock / warning icon --}}
            <svg width="26" height="26" fill="none" stroke="#ffffff" stroke-width="1.75"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <h1>Verification link expired</h1>
        <p>This link is no longer valid</p>
    </div>

    <div class="card-body">
        <p class="body-text">
            Email verification links expire after
            <strong>{{ config('auth.verification.expire', 60) }} minutes</strong>
            and can only be used once. Request a fresh link and verify your account straight away.
        </p>

        <a href="{{ route('verification.notice') }}" class="btn">
            Request a New Link
        </a>

        <hr class="divider">

        <p class="footer-note">
            If you keep seeing this message, make sure you're clicking the
            <em>most recent</em> email — earlier links are invalidated when a new
            one is sent.<br><br>
            Need help? <a href="mailto:support@g-luper.com" style="color:#2563eb;">Contact support</a>.
        </p>
    </div>
</div>

</body>
</html>