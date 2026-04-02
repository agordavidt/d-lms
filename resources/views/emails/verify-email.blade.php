<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your G-Luper email</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            max-width: 520px;
            margin: 48px auto;
            padding: 0 24px 48px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 40px 44px;
        }
        .label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 20px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .body-text {
            font-size: 14px;
            color: #475569;
            line-height: 1.75;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-block;
            background: #1e293b;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
        }
        .expiry {
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
        }
        .divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 32px 0;
        }
        .fallback {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.7;
        }
        .fallback a {
            color: #475569;
            word-break: break-all;
        }
        .footer {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: #cbd5e1;
        }
        .footer a {
            color: #94a3b8;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        <p class="label">G-Luper &mdash; Email Verification</p>

        <p class="greeting">Hi {{ $user->first_name }},</p>

        <p class="body-text">
            Thanks for signing up. Click the button below to verify your email address
            and activate your account.
        </p>

        <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>

        <p class="expiry">This link expires in {{ $expiresInMinutes }} minutes.</p>

        <hr class="divider">

        <p class="fallback">
            Button not working? Copy and paste this link into your browser:<br>
            <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
        </p>

        <hr class="divider">

        <p class="fallback">
            If you didn't create a G-Luper account, you can safely ignore this email.
        </p>

    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} G-Luper &nbsp;&middot;&nbsp;
            <a href="#">Privacy Policy</a> &nbsp;&middot;&nbsp;
            <a href="#">Support</a>
        </p>
    </div>
</div>
</body>
</html>