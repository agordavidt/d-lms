@extends('layouts.learner')
@section('title', 'Final Examination Result')

@section('content')
<div style="min-height:calc(100vh - 60px);background:#f8fafc;display:flex;align-items:flex-start;justify-content:center;padding:3.5rem 1.5rem 5rem;font-family:'DM Sans',sans-serif;">
<div style="width:100%;max-width:520px;">

    {{-- Result card --}}
    <div style="background:#fff;border-radius:20px;overflow:hidden;border:1.5px solid {{ $attempt->passed ? '#86efac' : '#fca5a5' }};box-shadow:0 8px 32px rgba(0,0,0,.06);">
       

        <div style="padding:2.5rem 2.5rem 2rem;text-align:center;">
 
            {{-- Headline --}}
            <h1 style="font-size:1.55rem;font-weight:800;color:#0f172a;margin:0 0 .5rem;">
                {{ $attempt->passed ? 'Examination Passed' : 'Not Passed' }}
            </h1>

            {{-- Score --}}
            <p style="font-size:3rem;font-weight:800;color:{{ $attempt->passed ? '#16a34a' : '#dc2626' }};line-height:1;margin:.25rem 0 .5rem;font-variant-numeric:tabular-nums;">
                {{ number_format($attempt->percentage, 0) }}%
            </p>
            <p style="font-size:.82rem;color:#94a3b8;margin:0 0 2rem;">
                Required: {{ $assessment->pass_percentage }}%
                &nbsp;·&nbsp; Attempt #{{ $attempt->attempt_number }}
            </p>

            @if($attempt->passed)
            {{-- ── PASS ── --}}
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:1.1rem 1.25rem;margin-bottom:1.75rem;text-align:left;">
                <p style="font-size:.9rem;font-weight:700;color:#14532d;margin:0 0 .3rem;">Certificate pending approval</p>
                <p style="font-size:.82rem;color:#166534;margin:0;line-height:1.6;">
                    Your result has been submitted for review.
                    An administrator will issue your certificate — check your Graduation Status for updates.
                </p>
            </div>

            <a href="{{ route('learner.graduation.status', $enrollment->id) }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#16a34a;color:#fff;padding:13px 28px;border-radius:10px;font-size:.95rem;font-weight:700;text-decoration:none;margin-bottom:.75rem;box-shadow:0 4px 14px rgba(22,163,74,.2);">
               
                View Graduation Status
            </a>
            <br>
            <a href="{{ route('learner.my-learning') }}"
               style="font-size:.82rem;color:#64748b;text-decoration:none;">← My Learning</a>

            @else
            {{-- ── FAIL ── --}}
            @if($onCooldown && $cooldownEnd)
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:1.75rem;text-align:left;">
                <p style="font-size:.875rem;font-weight:700;color:#92400e;margin:0 0 .5rem;">Next attempt available in:</p>
                <p id="cooldown-display" style="font-size:2rem;font-weight:800;color:#b45309;font-variant-numeric:tabular-nums;margin:0 0 .4rem;">--:--:--</p>
                <p style="font-size:.78rem;color:#b45309;margin:0;">
                    Available: <strong>{{ $cooldownEnd->format('M d, Y \a\t g:i A') }}</strong>
                </p>
            </div>

            <p style="font-size:.875rem;color:#64748b;margin:0 0 1.5rem;line-height:1.7;">
                Use this time to revisit the course material. There is no limit on attempts.
            </p>

            <a href="{{ route('learner.learning.week', [$enrollment->id, $assessment->moduleWeek->id]) }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#4f46e5;color:#fff;padding:13px 28px;border-radius:10px;font-size:.95rem;font-weight:700;text-decoration:none;margin-bottom:.75rem;box-shadow:0 4px 14px rgba(79,70,229,.2);">
                Review Course Material
            </a>

            @else
            {{-- Cooldown expired — ready to retry --}}
            <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.75rem;text-align:left;">
                <p style="font-size:.875rem;color:#92400e;margin:0;">
                    You did not pass. Review the material and try again when ready.
                </p>
            </div>

            <a href="{{ route('learner.learning.week', [$enrollment->id, $assessment->moduleWeek->id]) }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#7c3aed;color:#fff;padding:13px 28px;border-radius:10px;font-size:.95rem;font-weight:700;text-decoration:none;margin-bottom:.75rem;box-shadow:0 4px 14px rgba(124,58,237,.2);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
                Retry Examination
            </a>
            @endif

            <br>
            <a href="{{ route('learner.my-learning') }}"
               style="font-size:.82rem;color:#64748b;text-decoration:none;">← My Learning</a>
            @endif

        </div>
    </div>

    {{-- Attempt number note --}}
    @if($attempt->attempt_number > 1)
    <p style="text-align:center;font-size:.78rem;color:#94a3b8;margin-top:1.25rem;">
        This was attempt #{{ $attempt->attempt_number }}. There is no limit on the number of attempts.
    </p>
    @endif

</div>
</div>

@if(!$attempt->passed && $onCooldown && $cooldownEnd)
@push('scripts')
<script>
const cooldownEnd = new Date('{{ $cooldownEnd->toIso8601String() }}').getTime();
function tick() {
    const diff = Math.max(0, cooldownEnd - Date.now());
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    const el = document.getElementById('cooldown-display');
    if (el) el.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    if (diff > 0) setTimeout(tick, 1000);
    else location.reload(); // reload when cooldown expires so retry button appears
}
tick();
</script>
@endpush
@endif

@endsection