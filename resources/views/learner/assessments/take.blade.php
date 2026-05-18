@extends('layouts.learner')
@section('title', ($assessment->is_final ? 'Final Examination: ' : 'Quiz: ') . $assessment->title)

@push('styles')
<style>
    body { background: #f8fafc; }
</style>
@endpush

@section('content')
<div style="font-family:'DM Sans',sans-serif;min-height:calc(100vh - 60px);background:#f8fafc;">

    {{-- Sticky top bar --}}
    <div style="position:sticky;top:60px;z-index:50;background:#fff;border-bottom:1px solid {{ $assessment->is_final ? '#ddd6fe' : '#e2e8f0' }};">
        <div style="max-width:860px;margin:0 auto;padding:0 2rem;height:52px;display:flex;align-items:center;justify-content:space-between;gap:1rem;">

            <div style="display:flex;align-items:center;gap:.75rem;min-width:0;">
                @if($assessment->is_final)
                <span style="display:inline-flex;align-items:center;gap:4px;background:#7c3aed;color:#fff;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:3px 10px;border-radius:99px;flex-shrink:0;">
                    🎓 Final
                </span>
                @endif
                <span style="font-size:.9rem;font-weight:700;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $assessment->title }}</span>
            </div>

            <div style="display:flex;align-items:center;gap:1.25rem;flex-shrink:0;">
                @if($assessment->time_limit_minutes)
                <div id="timer-display"
                     style="display:flex;align-items:center;gap:5px;{{ $assessment->is_final ? 'background:#f5f3ff;color:#5b21b6;padding:4px 12px;border-radius:99px;' : 'color:#334155;' }}font-weight:700;font-size:.82rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/>
                    </svg>
                    <span id="timer-text">{{ $assessment->time_limit_minutes }}:00</span>
                </div>
                @endif
                <div style="font-size:.82rem;color:#64748b;font-weight:600;">
                    <span id="answered-count">0</span> / {{ $questions->count() }} answered
                </div>
            </div>
        </div>
    </div>

    {{-- Questions --}}
    <div style="max-width:860px;margin:0 auto;padding:1rem 2rem 4rem;">

        @foreach($questions as $index => $question)
        @php $opts = is_array($question->options) ? $question->options : (json_decode($question->options, true) ?? []); @endphp

        <div style="padding:2.5rem 0;border-bottom:1px solid #f1f5f9;">

            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.25rem;">
                <span style="font-size:.8rem;font-weight:700;color:{{ $assessment->is_final ? '#7c3aed' : '#6366f1' }};">{{ $index + 1 }}.</span>
                <span style="font-size:.75rem;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:2px 10px;border-radius:99px;">
                    {{ $question->points }} {{ $question->points === 1 ? 'point' : 'points' }}
                </span>
            </div>

            <p style="font-size:1.05rem;font-weight:500;color:#0f172a;line-height:1.65;margin:.5rem 0 1.25rem;">
                {{ $question->question_text }}
                @if($question->question_type === 'multiple_select')
                <span style="font-size:.8rem;color:#64748b;font-weight:400;"> — Select all that apply.</span>
                @endif
            </p>

            <div>
                @foreach($opts as $opt)
                <div class="{{ $assessment->is_final ? 'final-option-row' : 'option-row' }}"
                     data-qid="{{ $question->id }}"
                     data-value="{{ $opt }}"
                     data-type="{{ $question->question_type }}"
                     onclick="selectOpt(this)">
                    <div class="{{ $assessment->is_final ? 'final-option-radio' : 'option-radio' }}"></div>
                    <span style="font-size:.9rem;color:#334155;">{{ $opt }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Submit section --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.75rem 2rem;margin-top:2rem;">

            @if(!$assessment->is_final)
            {{-- Weekly quiz — honor code --}}
            <h3 style="font-size:.95rem;font-weight:700;color:#0f172a;margin:0 0 .75rem;">Honor Code</h3>
            <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;margin-bottom:1rem;">
                <input type="checkbox" id="honor-check"
                       style="width:18px;height:18px;accent-color:#6366f1;margin-top:2px;flex-shrink:0;"
                       onchange="updateSubmitBtn()">
                <span style="font-size:.875rem;color:#475569;line-height:1.6;">
                    I confirm that the answers I submit are my own work and reflect my understanding of the material.
                </span>
            </label>
            @else
            <h3 style="font-size:.95rem;font-weight:700;color:#0f172a;margin:0 0 .5rem;">Ready to submit?</h3>
            <p style="font-size:.82rem;color:#64748b;margin:0 0 1.25rem;line-height:1.6;">
                Once submitted, your score is recorded. Unanswered questions count as incorrect.
                A failed attempt triggers a <strong>48-hour cooldown</strong> before you can retry.
            </p>
            @endif

            <p id="unanswered-warning"
               style="display:none;font-size:.82rem;color:#b45309;background:#fef3c7;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;"></p>

            <button type="button" id="submit-btn" onclick="confirmSubmit()"
                    style="background:{{ $assessment->is_final ? '#7c3aed' : '#e2e8f0' }};color:{{ $assessment->is_final ? '#fff' : '#94a3b8' }};padding:13px 32px;border-radius:10px;border:none;font-size:.95rem;font-weight:700;cursor:{{ $assessment->is_final ? 'pointer' : 'not-allowed' }};transition:all .2s;box-shadow:{{ $assessment->is_final ? '0 4px 14px rgba(124,58,237,.25)' : 'none' }};">
                Submit {{ $assessment->is_final ? 'Examination' : 'Quiz' }}
            </button>
        </div>

    </div>
</div>

{{-- Confirm modal --}}
<div id="confirm-modal"
     style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:200;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:2.5rem 2rem;max-width:440px;width:90%;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.15);">
        <div style="width:52px;height:52px;background:{{ $assessment->is_final ? '#f5f3ff' : '#eef2ff' }};border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
            <svg width="26" height="26" fill="none" stroke="{{ $assessment->is_final ? '#7c3aed' : '#6366f1' }}" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 style="font-size:1.15rem;font-weight:800;color:#0f172a;margin:0 0 .75rem;">
            Submit {{ $assessment->is_final ? 'Examination' : 'Quiz' }}?
        </h3>
        <p id="modal-summary" style="font-size:.9rem;color:#64748b;margin:0 0 .5rem;line-height:1.6;"></p>
        <p id="modal-unanswered" style="display:none;font-size:.82rem;color:#b45309;background:#fef3c7;border-radius:8px;padding:.65rem 1rem;margin:.75rem 0 0;"></p>
        <div style="display:flex;gap:.75rem;justify-content:center;margin-top:1.75rem;">
            <button onclick="closeConfirm()"
                    style="padding:11px 22px;border-radius:10px;border:2px solid #e2e8f0;background:transparent;color:#475569;font-size:.9rem;font-weight:700;cursor:pointer;">
                Review
            </button>
            <button id="confirm-btn" onclick="doSubmit()"
                    style="padding:11px 26px;border-radius:10px;background:{{ $assessment->is_final ? '#7c3aed' : '#4f46e5' }};color:#fff;border:none;font-size:.9rem;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(79,70,229,.25);">
                Submit Now
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const IS_FINAL       = {{ $assessment->is_final ? 'true' : 'false' }};
const QUESTION_IDS   = @json($questions->pluck('id'));
const TOTAL_Q        = {{ $questions->count() }};
const CSRF           = document.querySelector('meta[name="csrf-token"]').content;
const userAnswers    = {};

// ── Option selection ──────────────────────────────────────────────────────────
function selectOpt(el) {
    const qid  = el.dataset.qid;
    const type = el.dataset.type;
    const cls  = IS_FINAL ? 'final-option-row' : 'option-row';

    if (type === 'multiple_select') {
        el.classList.toggle('selected');
        const selected = [...document.querySelectorAll(`.${cls}[data-qid="${qid}"].selected`)]
            .map(o => o.dataset.value);
        userAnswers[qid] = selected.length ? selected : undefined;
    } else {
        document.querySelectorAll(`.${cls}[data-qid="${qid}"]`).forEach(o => o.classList.remove('selected'));
        el.classList.add('selected');
        userAnswers[qid] = el.dataset.value;
    }
    updateAnsweredCount();
}

function updateAnsweredCount() {
    const count = QUESTION_IDS.filter(id => {
        const a = userAnswers[id];
        return a !== undefined && a !== null && !(Array.isArray(a) && a.length === 0);
    }).length;
    document.getElementById('answered-count').textContent = count;
    if (!IS_FINAL) updateSubmitBtn();
}

// ── Honor code toggle (weekly quiz only) ──────────────────────────────────────
function updateSubmitBtn() {
    const honored = document.getElementById('honor-check')?.checked ?? true;
    const btn = document.getElementById('submit-btn');
    if (honored) {
        btn.style.background = '#4f46e5';
        btn.style.color      = '#fff';
        btn.style.cursor     = 'pointer';
        btn.style.boxShadow  = '0 4px 14px rgba(79,70,229,.25)';
    } else {
        btn.style.background = '#e2e8f0';
        btn.style.color      = '#94a3b8';
        btn.style.cursor     = 'not-allowed';
        btn.style.boxShadow  = 'none';
    }
}

// ── Timer ─────────────────────────────────────────────────────────────────────
@if($assessment->time_limit_minutes)
const endTime = new Date('{{ $attempt->started_at->toIso8601String() }}').getTime()
              + {{ $assessment->time_limit_minutes }} * 60000;

function tick() {
    const remaining = Math.max(0, endTime - Date.now());
    const m  = Math.floor(remaining / 60000);
    const s  = Math.floor((remaining % 60000) / 1000);
    const el = document.getElementById('timer-text');
    if (el) el.textContent = m + ':' + String(s).padStart(2,'0');

    const disp = document.getElementById('timer-display');
    if (disp && IS_FINAL) {
        if      (remaining < 120000) { disp.style.background = '#fef2f2'; disp.style.color = '#dc2626'; }
        else if (remaining < 300000) { disp.style.background = '#fffbeb'; disp.style.color = '#b45309'; }
    } else if (disp && !IS_FINAL && m < 5) {
        disp.style.color = '#dc2626';
    }

    if (remaining === 0) { doSubmit(); return; }
    setTimeout(tick, 1000);
}
tick();
@endif

// ── Confirm flow ──────────────────────────────────────────────────────────────
function confirmSubmit() {
    if (!IS_FINAL) {
        const honored = document.getElementById('honor-check')?.checked;
        if (!honored) {
            document.getElementById('unanswered-warning').textContent = 'Please agree to the honor code before submitting.';
            document.getElementById('unanswered-warning').style.display = '';
            return;
        }
    }
    document.getElementById('unanswered-warning').style.display = 'none';

    const answered   = parseInt(document.getElementById('answered-count').textContent);
    const unanswered = TOTAL_Q - answered;

    document.getElementById('modal-summary').textContent =
        `You have answered ${answered} of ${TOTAL_Q} questions.`;

    const mw = document.getElementById('modal-unanswered');
    if (unanswered > 0) {
        mw.textContent = `${unanswered} unanswered question${unanswered > 1 ? 's' : ''} will be marked incorrect.`;
        mw.style.display = '';
    } else {
        mw.style.display = 'none';
    }

    document.getElementById('confirm-modal').style.display = 'flex';
}

function closeConfirm() {
    document.getElementById('confirm-modal').style.display = 'none';
}

// ── Submit ────────────────────────────────────────────────────────────────────
function doSubmit() {
    const btn = document.getElementById('confirm-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Submitting…'; }

    const payload = QUESTION_IDS.map(id => ({
        question_id: id,
        answer: userAnswers[id] ?? null,
    }));

    fetch('{{ route("learner.attempts.submit", $attempt->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ answers: payload }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (IS_FINAL) {
                window.location.href = '{{ route("learner.attempts.final-result", $attempt->id) }}';
            } else {
                window.location.href = '{{ route("learner.attempts.results", $attempt->id) }}';
            }
        } else {
            toastr.error(data.message || 'Submission failed.');
            if (btn) { btn.disabled = false; btn.textContent = 'Submit Now'; }
            closeConfirm();
        }
    })
    .catch(() => {
        toastr.error('A network error occurred. Please try again.');
        if (btn) { btn.disabled = false; btn.textContent = 'Submit Now'; }
        closeConfirm();
    });
}

// Prevent accidental navigation
window.addEventListener('beforeunload', e => { e.preventDefault(); e.returnValue = ''; });
</script>
@endpush