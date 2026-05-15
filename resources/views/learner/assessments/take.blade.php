@extends('layouts.learner')
@section('title', ($assessment->is_final ? 'Final Examination: ' : 'Assessment: ') . $assessment->title)

@section('content')
<div style="font-family:'DM Sans',sans-serif;min-height:100vh;background:#f8fafc;">

    {{-- Sticky top bar --}}
    <div style="position:sticky;top:0;z-index:100;background:#fff;border-bottom:1px solid {{ $assessment->is_final ? '#ddd6fe' : '#e2e8f0' }};padding:14px 0;">
        <div style="max-width:860px;margin:0 auto;padding:0 32px;display:flex;justify-content:space-between;align-items:center;gap:16px;">

            <div style="display:flex;align-items:center;gap:10px;">
                @if($assessment->is_final)
                <span style="display:inline-flex;align-items:center;gap:5px;background:#4f46e5;color:#fff;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:3px 10px;border-radius:999px;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Final Examination
                </span>
                @endif
                <span style="font-size:15px;font-weight:700;color:#0f172a;">{{ $assessment->title }}</span>
            </div>

            <div style="display:flex;align-items:center;gap:20px;">
                @if($assessment->time_limit_minutes)
                <div id="timerDisplay" class="final-timer-bar" style="{{ !$assessment->is_final ? 'background:transparent;padding:0;color:#334155;' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                    <span id="timerText" class="final-timer-countdown">{{ $assessment->time_limit_minutes }}:00</span>
                </div>
                @endif
                <div style="font-size:13px;color:#64748b;font-weight:600;">
                    <span id="answeredCount">0</span> / {{ $questions->count() }} answered
                </div>
            </div>
        </div>
    </div>

    {{-- Questions --}}
    <div style="max-width:860px;margin:0 auto;padding:8px 32px 60px;">
        <form id="assessmentForm" onsubmit="return false;">

            @foreach($questions as $index => $question)
            @php $opts = is_array($question->options) ? $question->options : (json_decode($question->options, true) ?? []); @endphp
            <div style="padding:36px 0;border-bottom:1px solid #f1f5f9;" data-qindex="{{ $index }}">

                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px;">
                    <span style="font-size:13px;font-weight:700;color:{{ $assessment->is_final ? '#7c3aed' : '#6366f1' }};">{{ $index + 1 }}.</span>
                    <span style="font-size:12px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:3px 10px;border-radius:100px;">
                        {{ $question->points }} {{ Str::plural('point', $question->points) }}
                    </span>
                </div>

                <p style="font-size:17px;font-weight:500;color:#0f172a;line-height:1.65;margin:8px 0 24px;">
                    {{ $question->question_text }}
                    @if($question->question_type === 'multiple_select')
                    <span style="font-size:13px;color:#64748b;font-weight:400;"> (Select all that apply.)</span>
                    @endif
                </p>

                <div>
                    @foreach($opts as $opt)
                    <div class="{{ $assessment->is_final ? 'final-option-row' : 'option-row' }}"
                         data-qid="{{ $question->id }}"
                         data-value="{{ $opt }}"
                         onclick="selectOpt(this)">
                        <div class="{{ $assessment->is_final ? 'final-option-radio' : 'option-radio' }}"></div>
                        <span style="font-size:.9rem;color:#334155;">{{ $opt }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Submit section --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:28px 32px;margin-top:32px;">
                @if($assessment->is_final)
                {{-- Final exam: no honor code, just confirm --}}
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 8px;">Ready to submit?</h3>
                <p style="font-size:13px;color:#64748b;margin:0 0 20px;line-height:1.6;">
                    Once submitted, your score will be recorded. Ensure all questions are answered.
                    If you did not pass, a <strong>48-hour cooldown</strong> applies before your next attempt.
                </p>
                @else
                {{-- Weekly: honor code --}}
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 12px;">Honor Code</h3>
                <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;margin-bottom:16px;">
                    <input type="checkbox" id="honorCheckbox" style="width:18px;height:18px;accent-color:#6366f1;margin-top:2px;flex-shrink:0;" onchange="updateSubmitBtn()">
                    <span style="font-size:14px;color:#475569;line-height:1.6;">
                        I understand that submitting work that isn't my own may result in permanent failure of this course.
                    </span>
                </label>
                @endif

                <p id="unansweredWarning" style="display:none;font-size:13px;color:#b45309;background:#fef3c7;border-radius:8px;padding:10px 16px;margin-bottom:16px;"></p>

                <button type="button" id="submitBtn" onclick="confirmSubmit()"
                        style="background:{{ $assessment->is_final ? '#4f46e5' : '#e2e8f0' }};color:{{ $assessment->is_final ? '#fff' : '#94a3b8' }};padding:14px 36px;border-radius:10px;border:none;font-size:15px;font-weight:700;cursor:{{ $assessment->is_final ? 'pointer' : 'not-allowed' }};transition:all .2s;box-shadow:{{ $assessment->is_final ? '0 4px 14px rgba(79,70,229,.25)' : 'none' }};">
                    Submit {{ $assessment->is_final ? 'Examination' : 'Assessment' }}
                </button>
            </div>

        </form>
    </div>
</div>

{{-- Confirm modal --}}
<div id="confirmModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:40px;max-width:460px;width:90%;text-align:center;box-shadow:0 25px 50px rgba(0,0,0,.15);">
        <div style="width:56px;height:56px;background:{{ $assessment->is_final ? '#ede9fe' : '#eef2ff' }};border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <svg width="28" height="28" fill="none" stroke="{{ $assessment->is_final ? '#7c3aed' : '#6366f1' }}" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 12px;">Submit {{ $assessment->is_final ? 'Examination' : 'Assessment' }}?</h3>
        <p id="modalSummary" style="font-size:15px;color:#64748b;margin:0 0 8px;line-height:1.6;"></p>
        <p id="unansweredWarningModal" style="display:none;font-size:14px;color:#b45309;background:#fef3c7;border-radius:8px;padding:10px 16px;margin:12px 0 0;"></p>
        <div style="display:flex;gap:12px;justify-content:center;margin-top:28px;">
            <button onclick="closeModal()" style="padding:12px 24px;border-radius:10px;border:2px solid #e2e8f0;background:transparent;color:#475569;font-size:15px;font-weight:700;cursor:pointer;">
                Review Answers
            </button>
            <button id="confirmSubmitBtn" onclick="doSubmit()"
                    style="padding:12px 28px;border-radius:10px;background:{{ $assessment->is_final ? '#4f46e5' : '#4f46e5' }};color:#fff;border:none;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(79,70,229,.25);">
                Submit Now
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const IS_FINAL       = {{ $assessment->is_final ? 'true' : 'false' }};
const questionIds    = @json($questions->pluck('id'));
const totalQuestions = {{ $questions->count() }};
const userAnswers    = {};

// ── Option selection ───────────────────────────────────────────────────────
function selectOpt(el) {
    const qid = el.dataset.qid;
    const cls = IS_FINAL ? 'final-option-row' : 'option-row';
    document.querySelectorAll('.' + cls + '[data-qid="' + qid + '"]').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    userAnswers[qid] = el.dataset.value;
    handleAnswerChange();
}

function handleAnswerChange() {
    let count = 0;
    questionIds.forEach(id => { if (userAnswers[id]) count++; });
    document.getElementById('answeredCount').textContent = count;
    if (!IS_FINAL) updateSubmitBtn();
}

function updateSubmitBtn() {
    const honored = document.getElementById('honorCheckbox')?.checked ?? true;
    const btn = document.getElementById('submitBtn');
    if (honored) {
        btn.style.background = '#4f46e5'; btn.style.color = '#fff';
        btn.style.cursor = 'pointer'; btn.style.boxShadow = '0 4px 14px rgba(79,70,229,.25)';
    } else {
        btn.style.background = '#e2e8f0'; btn.style.color = '#94a3b8';
        btn.style.cursor = 'not-allowed'; btn.style.boxShadow = 'none';
    }
}

// ── Timer ──────────────────────────────────────────────────────────────────
@if($assessment->time_limit_minutes)
const endTime = new Date('{{ $attempt->started_at->toIso8601String() }}').getTime() + {{ $assessment->time_limit_minutes }} * 60000;
function tick() {
    const remaining = Math.max(0, endTime - Date.now());
    const m = Math.floor(remaining / 60000);
    const s = Math.floor((remaining % 60000) / 1000);
    const el = document.getElementById('timerText');
    if (el) el.textContent = m + ':' + String(s).padStart(2,'0');
    const disp = document.getElementById('timerDisplay');
    if (disp) {
        if (IS_FINAL) {
            disp.className = remaining < 300000 ? 'final-timer-bar danger' : (remaining < 600000 ? 'final-timer-bar warning' : 'final-timer-bar');
        } else if (m < 5) {
            disp.style.color = '#dc2626';
        }
    }
    if (remaining === 0) { doSubmit(); return; }
    setTimeout(tick, 1000);
}
tick();
@endif

// ── Submit flow ────────────────────────────────────────────────────────────
function confirmSubmit() {
    if (!IS_FINAL && !document.getElementById('honorCheckbox').checked) {
        document.getElementById('unansweredWarning').textContent = 'You must agree to the honor code.';
        document.getElementById('unansweredWarning').style.display = 'block';
        return;
    }
    const answered = parseInt(document.getElementById('answeredCount').textContent);
    const unanswered = totalQuestions - answered;
    document.getElementById('modalSummary').textContent = 'You have answered ' + answered + ' of ' + totalQuestions + ' questions.';
    const warn = document.getElementById('unansweredWarningModal');
    if (unanswered > 0) {
        warn.textContent = unanswered + ' unanswered question' + (unanswered > 1 ? 's' : '') + ' will be marked incorrect.';
        warn.style.display = 'block';
    } else {
        warn.style.display = 'none';
    }
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeModal() { document.getElementById('confirmModal').style.display = 'none'; }

function doSubmit() {
    const btn = document.getElementById('confirmSubmitBtn');
    btn.disabled = true; btn.textContent = 'Submitting…';

    const payload = questionIds.map(id => ({ question_id: id, answer: userAnswers[id] || null }));

    fetch('{{ route("learner.attempts.submit", $attempt->id) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ answers: payload }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (IS_FINAL) {
                // Redirect to a clean result page (no question breakdown)
                window.location.href = '/learner/attempts/{{ $attempt->id }}/final-result';
            } else {
                window.location.href = '{{ route("learner.attempts.results", $attempt->id) }}';
            }
        } else {
            toastr.error(data.message || 'Submission failed.');
            btn.disabled = false; btn.textContent = 'Submit Now';
            closeModal();
        }
    })
    .catch(() => {
        toastr.error('A network error occurred.');
        btn.disabled = false; btn.textContent = 'Submit Now';
        closeModal();
    });
}

window.addEventListener('beforeunload', e => { e.preventDefault(); e.returnValue = ''; });
</script>
@endpush