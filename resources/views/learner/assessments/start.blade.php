@extends('layouts.learner')

@section('title', $assessment->title . ' — Assessment')

@push('styles')
<style>
    body { background: #f8fafc; }
</style>
@endpush

@section('content')
<div class="min-vh-100 d-flex flex-column" style="background:#f8fafc; font-family:'DM Sans',sans-serif;">

    {{-- Top nav bar --}}
    <div style="background:#fff; border-bottom:1px solid #e2e8f0; padding:16px 0;">
        <div style="max-width:720px; margin:0 auto; padding:0 24px; display:flex; align-items:center; gap:16px;">
            <a href="{{ route('learner.learning.index', $enrollment->id) }}"
               style="display:flex; align-items:center; gap:6px; color:#64748b; font-size:14px; font-weight:600; text-decoration:none;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <div style="height:20px; width:1px; background:#e2e8f0;"></div>
            <div>
                <span style="font-size:15px; font-weight:700; color:#1e293b;">{{ $assessment->title }}</span>
                <span style="font-size:13px; color:#94a3b8; margin-left:8px;">Practice Assignment • {{ $assessment->time_limit_minutes ? $assessment->time_limit_minutes . ' min' : 'No time limit' }}</span>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div style="flex:1; display:flex; align-items:flex-start; justify-content:center; padding:48px 24px;">
        <div style="width:100%; max-width:680px;">

            {{-- Assessment card --}}
            <div style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden;">

                {{-- Header --}}
                <div style="padding:40px 48px 32px; border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                        <span style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6366f1; background:#eef2ff; padding:4px 12px; border-radius:100px;">
                            Week {{ $week->week_number }} Assessment
                        </span>
                        @if($assessment->is_graded)
                        <span style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#0f766e; background:#f0fdf4; padding:4px 12px; border-radius:100px;">
                            Graded
                        </span>
                        @endif
                    </div>
                    <h1 style="font-size:26px; font-weight:800; color:#0f172a; margin:0 0 8px; letter-spacing:-0.02em;">
                        {{ $assessment->title }}
                    </h1>
                    <p style="font-size:14px; color:#64748b; margin:0;">{{ $week->title }}</p>
                </div>

                {{-- Stats grid --}}
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:0; border-bottom:1px solid #f1f5f9;">
                    <div style="padding:24px 32px; border-right:1px solid #f1f5f9; border-bottom:1px solid #f1f5f9;">
                        <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin:0 0 6px;">Questions</p>
                        <p style="font-size:22px; font-weight:800; color:#0f172a; margin:0;">{{ $assessment->questions->count() }}</p>
                    </div>
                    <div style="padding:24px 32px; border-bottom:1px solid #f1f5f9;">
                        <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin:0 0 6px;">Total Points</p>
                        <p style="font-size:22px; font-weight:800; color:#0f172a; margin:0;">{{ $assessment->total_points }}</p>
                    </div>
                    <div style="padding:24px 32px; border-right:1px solid #f1f5f9;">
                        <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin:0 0 6px;">To Pass</p>
                        <p style="font-size:22px; font-weight:800; color:#0f172a; margin:0;">{{ $assessment->passing_score ?? 80 }}%</p>
                    </div>
                    <div style="padding:24px 32px;">
                        <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin:0 0 6px;">Attempts Used</p>
                        <p style="font-size:22px; font-weight:800; color:#0f172a; margin:0;">
                            {{ $attemptsUsed }}<span style="font-size:14px; font-weight:600; color:#94a3b8;">/{{ $assessment->max_attempts }}</span>
                        </p>
                    </div>
                </div>

                {{-- Best score (if any) --}}
                @if($bestScore !== null)
                <div style="padding:20px 48px; background:#f8fafc; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px;">
                    <svg width="18" height="18" fill="none" stroke="#6366f1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    <span style="font-size:14px; color:#475569;">Your highest score: <strong style="color:#6366f1;">{{ number_format($bestScore, 1) }}%</strong></span>
                    <span style="font-size:13px; color:#94a3b8;">— We keep your highest score</span>
                </div>
                @endif

                {{-- Description --}}
                @if($assessment->description)
                <div style="padding:24px 48px; border-bottom:1px solid #f1f5f9;">
                    <p style="font-size:15px; color:#475569; line-height:1.7; margin:0;">{{ $assessment->description }}</p>
                </div>
                @endif

                {{-- CTA --}}
                <div style="padding:32px 48px;">
                    @if($attemptsUsed >= $assessment->max_attempts)
                        <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:10px; padding:16px 20px; margin-bottom:24px;">
                            <p style="font-size:14px; color:#92400e; margin:0;">You have used all {{ $assessment->max_attempts }} attempts. Your highest score of <strong>{{ number_format($bestScore, 1) }}%</strong> has been recorded.</p>
                        </div>
                        <a href="{{ route('learner.assessments.results', ['assessment' => $assessment->id, 'attempt' => $allAttempts->first()->id]) }}"
                           style="display:inline-flex; align-items:center; gap:8px; background:#f1f5f9; color:#475569; padding:14px 28px; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none;">
                            View Last Results
                        </a>
                    @else
                        @if($inProgressAttempt)
                        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:16px 20px; margin-bottom:24px;">
                            <p style="font-size:14px; color:#1e40af; margin:0;">You have an assessment in progress. Continue where you left off.</p>
                        </div>
                        <a href="{{ route('learner.attempts.show', $inProgressAttempt->id) }}"
                           style="display:inline-flex; align-items:center; gap:8px; background:#4f46e5; color:#fff; padding:14px 32px; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none; box-shadow:0 4px 14px rgba(79,70,229,.25);">
                            Continue Assessment
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        @else
                        <button type="button" onclick="startAssessment(this)"
                                style="display:inline-flex; align-items:center; gap:8px; background:#4f46e5; color:#fff; padding:14px 32px; border-radius:10px; font-size:15px; font-weight:700; border:none; cursor:pointer; box-shadow:0 4px 14px rgba(79,70,229,.25);">
                            {{ $attemptsUsed > 0 ? 'Retake Assessment' : 'Begin Assessment' }}
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        @endif
                    @endif
                </div>

            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function startAssessment(btn) {
    btn.disabled = true;
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="spin"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Starting...`;

    fetch('{{ route("learner.assessments.attempt", $assessment->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            toastr.error(data.message || 'Failed to start. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '{{ $attemptsUsed > 0 ? "Retake Assessment" : "Begin Assessment" }} <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>';
        }
    })
    .catch(() => {
        toastr.error('A network error occurred. Please try again.');
        btn.disabled = false;
    });
}
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 1s linear infinite; }
</style>
@endpush