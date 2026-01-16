<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'G-Luper Learning' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .logo-icon {
            width: 48px;
            height: 48px;
            background-color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 900;
            color: #4f46e5;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
        }
        .email-title {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body p {
            line-height: 1.6;
            margin: 0 0 16px 0;
            color: #475569;
        }
        .email-body h2 {
            color: #1e293b;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 16px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            margin: 20px 0;
            text-align: center;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .info-box {
            background-color: #f1f5f9;
            border-left: 4px solid #4f46e5;
            padding: 16px 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .info-box p {
            margin: 0;
            color: #475569;
        }
        .credentials-box {
            background-color: #fef3c7;
            border: 2px solid #fbbf24;
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            text-align: center;
        }
        .credentials-box strong {
            color: #92400e;
            font-size: 16px;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .email-footer p {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #64748b;
        }
        .social-links {
            margin-top: 20px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #64748b;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="logo">
                    <div class="logo-icon">G</div>
                    <div class="logo-text">Luper</div>
                </div>
                <h1 class="email-title">{{ $title ?? 'G-Luper Learning' }}</h1>
            </div>

            <!-- Body -->
            <div class="email-body">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p><strong>G-Luper Learning Management System</strong></p>
                <p>Accelerating Digital Excellence</p>
                <p style="font-size: 12px; color: #94a3b8;">
                    This is an automated message. Please do not reply to this email.<br>
                    If you need assistance, contact us at <a href="mailto:support@gluper.com" style="color: #4f46e5;">support@gluper.com</a>
                </p>
                <p style="font-size: 12px; color: #94a3b8; margin-top: 20px;">
                    © {{ date('Y') }} G-Luper Learning. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>