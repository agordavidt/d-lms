@extends('layouts.learner')

@section('title', 'Program Complete — ' . $enrollment->program->name)


@push('styles')
<style>
@keyframes pop-in {
    0%   { opacity: 0; transform: scale(0.85) translateY(20px); }
    70%  { transform: scale(1.03) translateY(-4px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50%       { transform: translateY(-10px); }
}
@keyframes confetti-fall {
    0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.pop-in   { animation: pop-in 0.5s cubic-bezier(.34,1.56,.64,1) both; }
.float    { animation: float 3s ease-in-out infinite; }
.confetti {
    position: fixed; top: -20px;
    width: 10px; height: 10px; border-radius: 2px;
    animation: confetti-fall linear forwards;
    pointer-events: none;
}
.review-spinner {
    width: 32px; height: 32px;
    border: 3px solid #fde68a;
    border-top-color: #d97706;
    border-radius: 50%;
    animation: spin .8s linear infinite;
    flex-shrink: 0;
}
</style>
@endpush

@section('content')

{{-- Confetti (only on pass/graduation states, not pending/dropped) --}}
@if(in_array($enrollment->graduation_status, ['active','pending_review','graduated']))
<div id="confetti-container" aria-hidden="true"></div>
@endif

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50/30 to-purple-50/20 flex items-center justify-center px-4 py-16">
    <div class="max-w-2xl w-full text-center">

        {{-- ── Flash message ─────────────────────────────────────────────── --}}
        @if(session('message'))
        @php
            $alertCls = match(session('alert-type','info')) {
                'success' => 'bg-green-50 border-green-200 text-green-800',
                'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
                'error'   => 'bg-red-50 border-red-200 text-red-800',
                default   => 'bg-blue-50 border-blue-200 text-blue-800',
            };
        @endphp
        <div class="pop-in mb-6 text-left border rounded-2xl px-5 py-4 text-sm font-semibold {{ $alertCls }}">
            {{ session('message') }}
        </div>
        @endif

        

        {{-- ── Headline ─────────────────────────────────────────────────── --}}
        <div class="pop-in" style="animation-delay:.1s">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-500 mb-3">
                @if($enrollment->graduation_status === 'graduated')
                    Graduated
                @else
                    Program Complete
                @endif
            </p>
            <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight mb-4">
                @if($enrollment->graduation_status === 'graduated')
                    Congratulations, {{ auth()->user()->first_name }}!
                @else
                    You did it, {{ auth()->user()->first_name }}!
                @endif
            </h1>
            <p class="text-lg text-slate-500 font-medium max-w-lg mx-auto mb-10">
                You've completed every week of
                <span class="font-bold text-slate-800">{{ $enrollment->program->name }}</span>.
                @if($enrollment->graduation_status === 'graduated')
                    Your certificate is ready to download.
                @elseif($enrollment->graduation_status === 'pending_review')
                    Your graduation is being reviewed by our team.
                @else
                    Your achievement is now pending review for graduation.
                @endif
            </p>
        </div>

        {{-- ── Stats strip ─────────────────────────────────────────────── --}}
        <div class="pop-in grid grid-cols-3 gap-4 mb-10" style="animation-delay:.2s">
            @php
                $completedItems = \App\Models\ContentProgress::where('enrollment_id', $enrollment->id)
                    ->where('is_completed', true)->count();
                $attendedCount = \App\Models\LiveSession::where('cohort_id', $enrollment->cohort_id)
                    ->where('status', 'completed')
                    ->whereJsonContains('attendees', auth()->id())->count();
            @endphp
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-3xl font-black text-indigo-600 mb-1">0</p>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Weeks Done</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-3xl font-black text-green-600 mb-1">{{ $completedItems }}</p>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Lessons Done</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-3xl font-black text-purple-600 mb-1">
                    {{ $enrollment->final_grade_avg ? round($enrollment->final_grade_avg) . '%' : '—' }}
                </p>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Avg Score</p>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             GRADUATION STATUS BLOCK
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="pop-in mb-10" style="animation-delay:.3s">

            @if($enrollment->graduation_status === 'graduated')
            {{-- ── GRADUATED — certificate ready ──────────────────────── --}}
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-6 text-left">
                <p class="text-green-800 font-bold text-base mb-1">🎓 Graduation approved!</p>
                <p class="text-green-700 text-sm mb-4">
                    Approved {{ $enrollment->graduation_approved_at?->format('F j, Y') ?? 'recently' }}.
                    Your certificate has been issued.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('learner.certificate.download', $enrollment->certificate_key) }}"
                       class="inline-flex items-center gap-2 bg-green-700 text-white font-bold px-6 py-3 rounded-xl hover:bg-green-800 transition-all text-sm shadow-lg shadow-green-300/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Certificate
                    </a>
                    <a href="{{ route('certificate.verify', $enrollment->certificate_key) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 text-green-700 font-bold px-6 py-3 rounded-xl border border-green-300 hover:bg-green-100 transition-all text-sm">
                        Verify Certificate
                    </a>
                </div>
            </div>

            @elseif($enrollment->graduation_status === 'pending_review')
            {{-- ── PENDING REVIEW ──────────────────────────────────────── --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6">
                <div class="flex items-start gap-4">
                    <div class="review-spinner mt-0.5"></div>
                    <div class="text-left">
                        <p class="text-amber-900 font-bold text-sm mb-1">Graduation under review</p>
                        <p class="text-amber-700 text-sm">
                            Submitted {{ $enrollment->graduation_requested_at?->diffForHumans() ?? 'recently' }}.
                            Our team will review your progress and issue your certificate — usually within 2 business days.
                            You'll be notified once a decision is made.
                        </p>
                    </div>
                </div>
            </div>

            @else
            {{-- ── ACTIVE — completed but graduation not yet auto-triggered
                 (safety fallback — should rarely be seen in normal flow,
                  since checkGraduationEligibility() fires automatically
                  after the last assessment is scored) ───────────────── --}}
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6 mb-6 text-left">
                <p class="text-indigo-900 font-bold text-sm mb-1">Ready to graduate?</p>
                <p class="text-indigo-700 text-sm mb-4">
                    Submit your graduation request and our team will review your progress and issue your certificate.
                </p>
                <form action="{{ route('learner.graduation.request', $enrollment->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-indigo-700 transition-all text-sm shadow-lg shadow-indigo-300/30">                        
                        Request Graduation Certificate
                    </button>
                </form>
            </div>
            @endif

        </div>

        {{-- ── Secondary actions — learner always has course access ──────── --}}
        <div class="pop-in flex flex-wrap justify-center gap-3" style="animation-delay:.4s">
            {{-- Always available: go back to the course (review, re-watch, etc.) --}}
            <a href="{{ route('learner.learning.index', $enrollment->id) }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-slate-900 transition-colors px-5 py-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50">
                ← Back to Course
            </a>

            @if($enrollment->graduation_status === 'graduated')
            <a href="{{ route('learner.certifications') }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-green-700 hover:text-green-800 transition-colors px-5 py-3 bg-green-50 border border-green-200 rounded-xl hover:bg-green-100">
                My Certifications
            </a>
            @else
            <a href="{{ route('explore') }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors px-5 py-3 bg-indigo-50 border border-indigo-100 rounded-xl hover:bg-indigo-100">
                Explore More Programs
            </a>
            @endif

            <a href="{{ route('learner.my-learning') }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors px-5 py-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50">
                My Learning
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
@if(in_array($enrollment->graduation_status, ['active','pending_review','graduated']))
(function spawnConfetti() {
    var colors = ['#4f46e5','#7c3aed','#059669','#d97706','#db2777','#0891b2'];
    var container = document.getElementById('confetti-container');
    if (!container) return;
    var count = 80;

    for (var i = 0; i < count; i++) {
        (function(i) {
            setTimeout(function() {
                var el = document.createElement('div');
                el.className = 'confetti';
                el.style.left              = Math.random() * 100 + 'vw';
                el.style.backgroundColor   = colors[Math.floor(Math.random() * colors.length)];
                el.style.width             = (Math.random() * 8 + 6) + 'px';
                el.style.height            = (Math.random() * 8 + 6) + 'px';
                el.style.borderRadius      = Math.random() > 0.5 ? '50%' : '2px';
                el.style.animationDuration = (Math.random() * 2 + 2) + 's';
                el.style.animationDelay    = (Math.random() * 0.5) + 's';
                container.appendChild(el);
                setTimeout(function() { el.remove(); }, 4000);
            }, i * 30);
        })(i);
    }
})();
@endif
</script>
@endpush