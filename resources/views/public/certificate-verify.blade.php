<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification — G-Luper</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', system-ui, sans-serif; background: #f8fafc; color: #0f172a; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; max-width: 520px; width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,.06); }
        .band { height: 5px; background: linear-gradient(90deg, #f59e0b, #eab308); }
        .body { padding: 2.5rem; text-align: center; }
        .seal { width: 64px; height: 64px; background: #f0fdf4; border: 2px solid #86efac; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1.25rem; }
        .status { display: inline-flex; align-items: center; gap: 6px; background: #f0fdf4; color: #166534; font-size: .75rem; font-weight: 700; padding: 4px 12px; border-radius: 99px; margin-bottom: 1.5rem; }
        h1 { font-size: 1.3rem; font-weight: 800; margin-bottom: .4rem; }
        .program { font-size: 1rem; font-weight: 600; color: #2563eb; margin-bottom: 1.75rem; }
        .meta { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 1.75rem; text-align: left; }
        .meta-row { display: flex; justify-content: space-between; align-items: center; padding: .7rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: .875rem; }
        .meta-row:last-child { border-bottom: none; }
        .meta-row span:first-child { color: #64748b; }
        .meta-row span:last-child { font-weight: 600; }
        .cert-key { font-family: monospace; font-size: .8rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 8px; }
        .footer-note { font-size: .75rem; color: #94a3b8; line-height: 1.6; }
        .home-link { display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-size: .82rem; text-decoration: none; margin-top: 1.5rem; }
        .home-link:hover { color: #2563eb; }
    </style>
</head>
<body>

<div class="card">
    <div class="band"></div>
    <div class="body">
       

        <div class="status">
            <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            Certificate Verified
        </div>

        <h1>{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</h1>
        <div class="program">{{ $enrollment->program->name }}</div>

        <div class="meta">
            <div class="meta-row">
                <span>Status</span>
                <span style="color:#16a34a;">Graduated ✓</span>
            </div>
            @if($enrollment->final_exam_score !== null)
            <div class="meta-row">
                <span>Final exam score</span>
                <span>{{ number_format($enrollment->final_exam_score, 0) }}%</span>
            </div>
            @endif
            @if($enrollment->graduation_approved_at)
            <div class="meta-row">
                <span>Issued on</span>
                <span>{{ $enrollment->graduation_approved_at->format('F j, Y') }}</span>
            </div>
            @endif
            @if($enrollment->program->mentor)
            <div class="meta-row">
                <span>Instructor</span>
                <span>{{ $enrollment->program->mentor->first_name }} {{ $enrollment->program->mentor->last_name }}</span>
            </div>
            @endif
            <div class="meta-row">
                <span>Certificate no.</span>
                <span class="cert-key">{{ $enrollment->certificate_key }}</span>
            </div>
        </div>

        <p class="footer-note">
            This certificate was issued by G-Luper and is valid proof of course completion.<br>
            Verified on {{ now()->format('F j, Y') }}.
        </p>

    </div>
</div>

<a href="{{ route('home') }}" class="home-link">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to G-Luper
</a>

</body>
</html>