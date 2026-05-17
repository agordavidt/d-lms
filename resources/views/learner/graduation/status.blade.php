@extends('layouts.learner')
@section('title', 'Graduation Status')

@section('content')
<div style="max-width:640px;margin:0 auto;padding:3rem 1.5rem 5rem;font-family:'DM Sans',sans-serif;">

    {{-- Back + heading --}}
    <div style="margin-bottom:2rem;">
        <a href="{{ route('learner.my-learning') }}"
           style="font-size:.82rem;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:.75rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            My Learning
        </a>
        <h1 style="font-size:1.45rem;font-weight:800;color:#0f172a;margin:0 0 .25rem;">Graduation Status</h1>
        <p style="font-size:.875rem;color:#64748b;margin:0;">{{ $enrollment->program->name }}</p>
    </div>

    {{-- ── GRADUATED ── --}}
    @if($enrollment->graduation_status === 'graduated')
    <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:16px;padding:1.75rem 1.5rem;display:flex;align-items:center;gap:1.25rem;margin-bottom:1.5rem;">
        <div style="width:52px;height:52px;background:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="26" height="26" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
        </div>
        <div>
            <p style="font-weight:800;font-size:1.05rem;color:#14532d;margin:0 0 .3rem;">Congratulations — You've graduated!</p>
            <p style="font-size:.82rem;color:#166534;margin:0;">Your certificate is ready in the Certifications section.</p>
        </div>
    </div>
    <a href="{{ route('learner.certifications') }}"
       style="display:inline-flex;align-items:center;gap:8px;background:#16a34a;color:#fff;padding:13px 24px;border-radius:10px;font-size:.9rem;font-weight:700;text-decoration:none;box-shadow:0 4px 14px rgba(22,163,74,.2);">
        View Certificate →
    </a>

    {{-- ── PENDING REVIEW ── --}}
    @elseif($enrollment->graduation_status === 'pending_review')
    <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:16px;padding:1.75rem 1.5rem;display:flex;align-items:center;gap:1.25rem;margin-bottom:1.5rem;">
        <div style="width:52px;height:52px;background:#3b82f6;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/>
            </svg>
        </div>
        <div>
            <p style="font-weight:800;font-size:1.05rem;color:#1e3a8a;margin:0 0 .3rem;">Certificate request submitted</p>
            <p style="font-size:.82rem;color:#1d4ed8;margin:0;line-height:1.6;">
                An administrator will review and issue your certificate shortly.
                @if($enrollment->final_exam_score)
                Your final exam score was <strong>{{ number_format($enrollment->final_exam_score, 0) }}%</strong>.
                @endif
            </p>
        </div>
    </div>
    <a href="{{ route('learner.certifications') }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:.875rem;color:#2563eb;font-weight:600;text-decoration:none;">
        Check Certifications →
    </a>

    {{-- ── ACTIVE — show progress checklist ── --}}
    @else

    {{-- Step 1: Course completion --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;margin-bottom:1rem;">
        <div style="padding:14px 18px;background:{{ $allWeeksComplete ? '#f0fdf4' : '#f8fafc' }};border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:.75rem;">
            <div style="width:28px;height:28px;border-radius:50%;background:{{ $allWeeksComplete ? '#16a34a' : '#e5e7eb' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @if($allWeeksComplete)
                <svg width="14" height="14" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                @else
                <span style="font-size:.75rem;font-weight:700;color:#9ca3af;">1</span>
                @endif
            </div>
            <div style="flex:1;">
                <p style="font-size:.9rem;font-weight:700;color:{{ $allWeeksComplete ? '#166534' : '#374151' }};margin:0;">
                    Complete all course modules
                </p>
                <p style="font-size:.78rem;color:{{ $allWeeksComplete ? '#16a34a' : '#6b7280' }};margin:.1rem 0 0;">
                    {{ $allWeeksComplete ? 'All weeks and weekly quizzes passed ✓' : 'Finish all weeks and pass all weekly quizzes' }}
                </p>
            </div>
            @if(!$allWeeksComplete)
            <a href="{{ route('learner.learning.index', $enrollment->id) }}"
               style="font-size:.78rem;color:#2563eb;font-weight:600;text-decoration:none;white-space:nowrap;">
                Continue →
            </a>
            @endif
        </div>
    </div>

    {{-- Step 2: Final examination --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;margin-bottom:1.5rem;">
        <div style="padding:14px 18px;background:{{ $finalExamPassed ? '#f0fdf4' : ($allWeeksComplete ? '#f5f3ff' : '#f8fafc') }};border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:.75rem;">
            <div style="width:28px;height:28px;border-radius:50%;background:{{ $finalExamPassed ? '#16a34a' : ($allWeeksComplete ? '#7c3aed' : '#e5e7eb') }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @if($finalExamPassed)
                <svg width="14" height="14" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                @else
                <span style="font-size:.75rem;font-weight:700;color:{{ $allWeeksComplete ? '#fff' : '#9ca3af' }};">2</span>
                @endif
            </div>
            <div style="flex:1;">
                <p style="font-size:.9rem;font-weight:700;color:{{ $finalExamPassed ? '#166534' : ($allWeeksComplete ? '#4c1d95' : '#374151') }};margin:0;">
                    Pass the Final Examination
                </p>
                <p style="font-size:.78rem;color:{{ $finalExamPassed ? '#16a34a' : ($allWeeksComplete ? '#7c3aed' : '#6b7280') }};margin:.1rem 0 0;">
                    @if($finalExamPassed)
                        Passed — {{ number_format($finalAttempt->percentage, 0) }}% ✓
                    @elseif(!$allWeeksComplete)
                        Complete all modules first to unlock
                    @elseif($onCooldown && $cooldownEnd)
                        Next attempt available {{ $cooldownEnd->diffForHumans() }}
                    @elseif($finalAttempt)
                        Last attempt: {{ number_format($finalAttempt->percentage, 0) }}% — retry when ready
                    @else
                        Ready to take — minimum {{ $finalAssessment?->pass_percentage ?? 75 }}% required
                    @endif
                </p>
            </div>

            {{-- CTA depending on state --}}
            @if($finalAssessment)
                @if($finalExamPassed)
                    {{-- nothing --}}
                @elseif($allWeeksComplete && !$onCooldown)
                <a href="{{ route('learner.learning.week', [$enrollment->id, $finalAssessment->moduleWeek->id]) }}"
                   style="font-size:.78rem;color:#7c3aed;font-weight:700;text-decoration:none;white-space:nowrap;">
                    {{ $finalAttempt ? 'Retry →' : 'Begin →' }}
                </a>
                @elseif($onCooldown && $cooldownEnd)
                <span id="status-countdown"
                      style="font-size:.78rem;color:#b45309;font-weight:700;font-variant-numeric:tabular-nums;white-space:nowrap;">
                    --:--:--
                </span>
                @endif
            @endif
        </div>

        {{-- Final exam detail block --}}
        @if($finalAssessment && $allWeeksComplete && !$finalExamPassed)
        <div style="padding:1rem 1.25rem 1.25rem;">
            @if($onCooldown && $cooldownEnd)
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:1rem 1.25rem;">
                <p style="font-size:.82rem;font-weight:700;color:#92400e;margin:0 0 .35rem;">Cooldown active — next attempt in:</p>
                <p id="cooldown-big" style="font-size:1.6rem;font-weight:800;color:#b45309;font-variant-numeric:tabular-nums;margin:0 0 .25rem;">--:--:--</p>
                <p style="font-size:.75rem;color:#b45309;margin:0;">Available: <strong>{{ $cooldownEnd->format('M d, Y \a\t g:i A') }}</strong></p>
            </div>
            @else
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;">
                <div style="font-size:.82rem;color:#6b7280;">
                    <span style="font-weight:600;">{{ $finalAssessment->questions->count() }}</span> questions ·
                    Pass mark: <span style="font-weight:600;">{{ $finalAssessment->pass_percentage }}%</span>
                    @if($finalAssessment->time_limit_minutes)
                    · <span style="font-weight:600;">{{ $finalAssessment->time_limit_minutes }} min</span>
                    @endif
                </div>
                <a href="{{ route('learner.learning.week', [$enrollment->id, $finalAssessment->moduleWeek->id]) }}"
                   style="display:inline-flex;align-items:center;gap:6px;background:#7c3aed;color:#fff;padding:9px 18px;border-radius:8px;font-size:.82rem;font-weight:700;text-decoration:none;box-shadow:0 3px 10px rgba(124,58,237,.2);">
                    {{ $finalAttempt ? 'Retry Examination' : 'Begin Examination' }} →
                </a>
            </div>
            @if($finalAttempt && !$finalExamPassed)
            <p style="font-size:.78rem;color:#94a3b8;margin:.75rem 0 0;">
                Last attempt: {{ number_format($finalAttempt->percentage, 0) }}% on {{ $finalAttempt->submitted_at->format('M d, Y') }}.
                Attempt #{{ $finalAttempt->attempt_number }}.
            </p>
            @endif
            @endif
        </div>
        @endif

    </div>

    {{-- Checklist note --}}
    <p style="font-size:.78rem;color:#94a3b8;line-height:1.7;margin:0;">
        Once you pass the final examination, your completion is automatically submitted for review.
        An administrator will issue your certificate — typically within 1–2 business days.
    </p>

    @endif

</div>

{{-- Cooldown timers --}}
@if($onCooldown && $cooldownEnd)
@push('scripts')
<script>
const cooldownEnd = new Date('{{ $cooldownEnd->toIso8601String() }}').getTime();

function tick() {
    const diff = Math.max(0, cooldownEnd - Date.now());
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    const formatted = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');

    const c1 = document.getElementById('status-countdown');
    const c2 = document.getElementById('cooldown-big');
    if (c1) c1.textContent = formatted;
    if (c2) c2.textContent = formatted;

    if (diff > 0) setTimeout(tick, 1000);
    else location.reload(); // refresh when cooldown expires
}
tick();
</script>
@endpush
@endif

@endsection