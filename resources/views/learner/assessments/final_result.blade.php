@extends('layouts.learner')
@section('title', 'Examination Result')

@section('content')
@php
    $enrollment = $attempt->enrollment;
    $assessment = $attempt->assessment;
    $passed     = $attempt->passed;
@endphp

<div style="min-height:calc(100vh - 60px);background:#f8fafc;display:flex;align-items:flex-start;justify-content:center;padding:56px 24px 80px;font-family:'DM Sans',sans-serif;">
    <div style="width:100%;max-width:520px;">

        {{-- Result card --}}
        <div style="background:#fff;border-radius:20px;overflow:hidden;border:1px solid {{ $passed ? '#bbf7d0' : '#fecaca' }};box-shadow:0 8px 32px rgba(0,0,0,.06);">

            {{-- Accent bar --}}
            <div style="height:5px;background:{{ $passed ? 'linear-gradient(90deg,#16a34a,#22c55e)' : 'linear-gradient(90deg,#dc2626,#ef4444)' }};"></div>

            <div style="padding:48px 40px;text-align:center;">

                {{-- Icon --}}
                <div style="width:72px;height:72px;border-radius:50%;background:{{ $passed ? '#f0fdf4' : '#fef2f2' }};border:2px solid {{ $passed ? '#86efac' : '#fca5a5' }};display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                    @if($passed)
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    @else
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/>
                    </svg>
                    @endif
                </div>

                {{-- Headline --}}
                <h1 style="font-size:1.6rem;font-weight:800;color:#0f172a;margin:0 0 8px;">
                    {{ $passed ? 'Examination Passed' : 'Not Passed' }}
                </h1>

                {{-- Score --}}
                <p class="final-result-score {{ $passed ? 'pass' : 'fail' }}" style="margin:0 0 6px;">
                    {{ number_format($attempt->percentage, 0) }}%
                </p>
                <p style="font-size:.82rem;color:#94a3b8;margin:0 0 32px;">
                    Required: {{ $assessment->pass_percentage }}% &nbsp;·&nbsp;
                    Attempt #{{ $attempt->attempt_number }} &nbsp;·&nbsp;
                    {{ $attempt->getFormattedTimeSpent() }}
                </p>

                @if($passed)
                {{-- Pass state --}}
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:18px 20px;margin-bottom:28px;text-align:left;">
                    <p style="font-size:.9rem;font-weight:700;color:#14532d;margin:0 0 4px;">Certificate pending approval</p>
                    <p style="font-size:.82rem;color:#166534;margin:0;line-height:1.6;">
                        Your completion has been flagged for certificate issuance.
                        Once an administrator approves, it will appear in your Certifications page.
                    </p>
                </div>
                <a href="{{ route('learner.certifications') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:#0056d2;color:#fff;padding:13px 28px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;margin-bottom:12px;">
                    View Certifications
                </a>
                <br>
                <a href="{{ route('learner.my-learning') }}"
                   style="font-size:13px;color:#64748b;text-decoration:none;">← My Learning</a>

                @else
                {{-- Fail state + cooldown --}}
                @php $cooldownEnd = $attempt->next_attempt_at; @endphp
                <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:18px 20px;margin-bottom:28px;text-align:left;">
                    <p style="font-size:.875rem;font-weight:700;color:#92400e;margin:0 0 6px;">Next attempt available in:</p>
                    <p id="cooldown-display" style="font-size:1.8rem;font-weight:800;color:#b45309;font-variant-numeric:tabular-nums;margin:0 0 6px;">--:--:--</p>
                    <p style="font-size:.78rem;color:#b45309;margin:0;">
                        Available: <strong>{{ $cooldownEnd->format('M d, Y \a\t g:i A') }}</strong>
                    </p>
                </div>
                <p style="font-size:.875rem;color:#64748b;margin:0 0 24px;line-height:1.7;">
                    Use this time to revisit the course material. There is no limit on the number of attempts.
                </p>
                <a href="{{ route('learner.learning.week', [$enrollment->id, $assessment->moduleWeek->id]) }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:#4f46e5;color:#fff;padding:13px 28px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;margin-bottom:12px;">
                    Review Course Material
                </a>
                <br>
                <a href="{{ route('learner.my-learning') }}"
                   style="font-size:13px;color:#64748b;text-decoration:none;">← My Learning</a>
                @endif

            </div>
        </div>

    </div>
</div>

@if(!$passed)
@push('scripts')
<script>
const cooldownEnd = new Date('{{ $attempt->next_attempt_at->toIso8601String() }}').getTime();
function tick() {
    const diff = Math.max(0, cooldownEnd - Date.now());
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    const el = document.getElementById('cooldown-display');
    if (el) el.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    if (diff > 0) setTimeout(tick, 1000);
}
tick();
</script>
@endpush
@endif

@endsection