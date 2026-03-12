<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your G-Luper email</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            max-width: 580px;
            margin: 40px auto;
            padding: 0 16px 40px;
        }
        /* Logo header */
        .logo-bar {
            text-align: center;
            padding: 32px 0 24px;
        }
        .logo-inner {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo-mark {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            color: #ffffff;
            line-height: 1;
        }
        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.02em;
        }
        /* Card */
        .card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #1e40af, #3730a3);
            padding: 36px 40px;
            text-align: center;
        }
        .card-header-icon {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.15);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        .card-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }
        .card-header p {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
        }
        /* Body */
        .card-body {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 16px;
            color: #1e293b;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .body-text {
            font-size: 14px;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        /* Button */
        .btn-wrap {
            text-align: center;
            margin-bottom: 28px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 100px;
            letter-spacing: -0.01em;
        }
        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 24px 0;
        }
        /* Fallback link */
        .fallback {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .fallback a {
            color: #2563eb;
            word-break: break-all;
        }
        /* Expiry notice */
        .expiry-note {
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 12px;
            color: #713f12;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        /* Footer */
        .footer {
            text-align: center;
            padding-top: 24px;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.7;
        }
        .footer a {
            color: #64748b;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Logo --}}
    <div class="logo-bar">
        <span class="logo-inner">
            <span class="logo-mark">G</span>
            <span class="logo-text">Luper</span>
        </span>
    </div>

    <div class="card">

        {{-- Header --}}
        <div class="card-header">
            <div class="card-header-icon">
                {{-- Envelope icon (inline SVG — works in all email clients) --}}
                <svg width="26" height="26" fill="none" stroke="#ffffff" stroke-width="1.75"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1>Verify your email address</h1>
            <p>One last step to activate your account</p>
        </div>

        {{-- Body --}}
        <div class="card-body">
            <p class="greeting">Hi {{ $user->first_name }},</p>
            <p class="body-text">
                Thanks for creating a G-Luper account. Click the button below to verify
                your email address and gain full access to our learning platform.
            </p>

            <div class="btn-wrap">
                <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
            </div>

            <div class="expiry-note">
                ⏱ This link expires in <strong>{{ $expiresInMinutes }} minutes</strong>.
                If it expires, you can request a new one from the login page.
            </div>

            <hr class="divider">

            <p class="fallback">
                If the button above doesn't work, copy and paste the link below into your browser:<br>
                <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
            </p>

            <hr class="divider">

            <p class="fallback">
                If you did not create a G-Luper account, you can safely ignore this email.
                No account will be activated without verification.
            </p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} G-Luper Learning &nbsp;&middot;&nbsp;
            <a href="#">Privacy Policy</a> &nbsp;&middot;&nbsp;
            <a href="#">Support</a>
        </p>
        <p style="margin-top: 6px;">
            You're receiving this because you created an account at g-luper.com.
        </p>
    </div>

</div>
</body>
</html>