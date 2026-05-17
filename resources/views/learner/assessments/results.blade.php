@extends('layouts.learner')
@section('title', 'Quiz Results — ' . $assessment->title)

@section('content')
<div style="min-height:calc(100vh - 60px);background:#f8fafc;font-family:'DM Sans',sans-serif;">

    {{-- Top bar --}}
    <div style="background:#fff;border-bottom:1px solid #e2e8f0;padding:0;">
        <div style="max-width:720px;margin:0 auto;padding:0 2rem;height:52px;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <a href="{{ route('learner.learning.week', [$enrollment->id, $week->id]) }}"
               style="display:flex;align-items:center;gap:6px;color:#64748b;font-size:.875rem;font-weight:600;text-decoration:none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Week
            </a>
            <span style="font-size:.875rem;font-weight:600;color:#0f172a;">{{ $assessment->title }}</span>
            <span style="font-size:.78rem;color:#94a3b8;">Week {{ $week->week_number }}</span>
        </div>
    </div>

    <div style="max-width:720px;margin:0 auto;padding:2.5rem 2rem 5rem;">

        {{-- Result card --}}
        <div style="background:#fff;border-radius:16px;border:1.5px solid {{ $attempt->passed ? '#86efac' : '#fca5a5' }};overflow:hidden;margin-bottom:1.5rem;box-shadow:0 4px 20px rgba(0,0,0,.04);">

            <div style="height:4px;background:{{ $attempt->passed ? 'linear-gradient(90deg,#16a34a,#22c55e)' : 'linear-gradient(90deg,#dc2626,#ef4444)' }};"></div>

            <div style="padding:2rem 2rem 1.5rem;">
                <div style="display:flex;align-items:flex-start;gap:1.25rem;">

                    <div style="width:56px;height:56px;border-radius:50%;background:{{ $attempt->passed ? '#f0fdf4' : '#fef2f2' }};border:2px solid {{ $attempt->passed ? '#86efac' : '#fca5a5' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        @if($attempt->passed)
                        <svg width="24" height="24" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @else
                        <svg width="24" height="24" fill="none" stroke="#dc2626" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/>
                        </svg>
                        @endif
                    </div>

                    <div style="flex:1;">
                        <p style="font-size:1.25rem;font-weight:800;color:{{ $attempt->passed ? '#15803d' : '#dc2626' }};margin-bottom:.25rem;">
                            {{ $attempt->passed ? 'Quiz Passed' : 'Not Passed Yet' }}
                        </p>
                        <p style="font-size:.875rem;color:#64748b;margin-bottom:1rem;">
                            Score: <strong style="color:{{ $attempt->passed ? '#15803d' : '#dc2626' }};">{{ number_format($attempt->percentage, 0) }}%</strong>
                            &nbsp;·&nbsp; Required: <strong>100%</strong>
                            &nbsp;·&nbsp; Attempt #{{ $attempt->attempt_number }}
                        </p>

                        {{-- Score stats --}}
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.9rem 1rem;">
                                <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:.3rem;">Score</p>
                                <p style="font-size:1.3rem;font-weight:800;color:{{ $attempt->passed ? '#15803d' : '#dc2626' }};margin:0;">{{ number_format($attempt->percentage, 0) }}%</p>
                            </div>
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.9rem 1rem;">
                                <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:.3rem;">Points</p>
                                <p style="font-size:1.3rem;font-weight:800;color:#0f172a;margin:0;">{{ $attempt->score_earned }}<span style="font-size:.8rem;color:#94a3b8;">/{{ $attempt->total_points }}</span></p>
                            </div>
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.9rem 1rem;">
                                <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:.3rem;">Attempts</p>
                                <p style="font-size:1.3rem;font-weight:800;color:#0f172a;margin:0;">{{ $allAttempts->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Message --}}
            <div style="padding:1rem 2rem 1.5rem;">
                @if($attempt->passed)
                <p style="font-size:.875rem;color:#166534;background:#f0fdf4;border-radius:8px;padding:.85rem 1rem;margin:0;">
                    ✓ You answered all questions correctly. You can now progress to the next week.
                </p>
                @else
                <p style="font-size:.875rem;color:#92400e;background:#fffbeb;border-radius:8px;padding:.85rem 1rem;margin:0;">
                    You need to answer all questions correctly (100%) to pass this quiz and unlock the next week.
                    Review the week content and try again — there is no limit on retakes.
                </p>
                @endif
            </div>
        </div>

        {{-- Attempt history --}}
        @if($allAttempts->count() > 1)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
            <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:.75rem;">Attempt History</p>
            <div style="display:grid;grid-template-columns:60px 1fr 80px 120px;gap:.5rem;font-size:.78rem;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding-bottom:.5rem;border-bottom:1px solid #f1f5f9;margin-bottom:.5rem;">
                <span>#</span><span>Score</span><span>Status</span><span>Date</span>
            </div>
            @foreach($allAttempts as $h)
            <div style="display:grid;grid-template-columns:60px 1fr 80px 120px;gap:.5rem;font-size:.875rem;padding:.5rem 0;{{ $h->id === $attempt->id ? 'font-weight:700;' : '' }}">
                <span style="color:#6366f1;font-weight:700;">#{{ $h->attempt_number }}</span>
                <span style="color:{{ $h->passed ? '#15803d' : '#dc2626' }};">{{ number_format($h->percentage,0) }}%</span>
                <span style="color:{{ $h->passed ? '#15803d' : '#64748b' }};">{{ $h->passed ? 'Passed' : 'Failed' }}</span>
                <span style="color:#94a3b8;font-size:.78rem;">{{ $h->submitted_at->format('M d, g:i A') }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Actions --}}
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
            @if($attempt->passed)
            <a href="{{ route('learner.learning.index', $enrollment->id) }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;padding:13px 24px;border-radius:10px;font-size:.9rem;font-weight:700;text-decoration:none;">
                Continue Learning →
            </a>
            @else
            <a href="{{ route('learner.learning.week', [$enrollment->id, $week->id]) }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#4f46e5;color:#fff;padding:13px 24px;border-radius:10px;font-size:.9rem;font-weight:700;text-decoration:none;">
                Retry Quiz
            </a>
            <a href="{{ route('learner.learning.week', [$enrollment->id, $week->id]) }}"
               style="display:inline-flex;align-items:center;padding:13px 20px;border-radius:10px;border:1.5px solid #e2e8f0;background:transparent;color:#475569;font-size:.9rem;font-weight:700;text-decoration:none;">
                Review Week Content
            </a>
            @endif
        </div>

    </div>
</div>
@endsection