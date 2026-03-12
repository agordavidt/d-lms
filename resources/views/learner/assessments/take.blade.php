@extends('layouts.learner')

@section('title', 'Taking: ' . $assessment->title)

@push('styles')
<style>
    body { background: #f8fafc; }

    .option-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 20px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        margin-bottom: 10px;
    }
    .option-row:hover { border-color: #a5b4fc; background: #f5f3ff; }
    .option-row.selected { border-color: #6366f1; background: #eef2ff; }
    .option-row input { flex-shrink:0; width:18px; height:18px; margin-top:2px; accent-color:#6366f1; cursor:pointer; }
    .option-row .opt-text { font-size:15px; color:#334155; line-height:1.6; }
    .option-row.selected .opt-text { color:#3730a3; font-weight:500; }

    .question-block { padding: 40px 0; border-bottom: 1px solid #f1f5f9; }
    .question-block:last-of-type { border-bottom: none; }

    .honor-section { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:28px 32px; margin-top:32px; }
</style>
@endpush

@section('content')
<div style="font-family:'DM Sans',sans-serif; min-height:100vh; background:#f8fafc;">

    {{-- Sticky top bar --}}
    <div id="topBar" style="position:sticky; top:0; z-index:100; background:#fff; border-bottom:1px solid #e2e8f0; padding:14px 0;">
        <div style="max-width:860px; margin:0 auto; padding:0 32px; display:flex; justify-content:space-between; align-items:center; gap:16px;">

            <div>
                <span style="font-size:15px; font-weight:700; color:#0f172a;">{{ $assessment->title }}</span>
                <span style="font-size:13px; color:#94a3b8; margin-left:8px;">Practice Assignment</span>
            </div>

            <div style="display:flex; align-items:center; gap:20px;">
                @if($assessment->time_limit_minutes)
                <div id="timerDisplay" style="display:flex; align-items:center; gap:6px; font-size:14px; font-weight:700; color:#334155;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="M12 6v6l4 2"/>
                    </svg>
                    <span id="timerText">{{ $assessment->time_limit_minutes }}:00</span>
                </div>
                @endif
                <div style="font-size:13px; color:#64748b; font-weight:600;">
                    <span id="answeredCount">0</span> / {{ $questions->count() }} answered
                </div>
            </div>

        </div>
    </div>

    {{-- Questions --}}
    <div style="max-width:860px; margin:0 auto; padding:8px 32px 60px;">

        <form id="assessmentForm" onsubmit="return false;">

            @foreach($questions as $index => $question)
            <div class="question-block" data-qindex="{{ $index }}">

                {{-- Question header --}}
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-size:13px; font-weight:700; color:#6366f1;">{{ $index + 1 }}.</span>
                    <span style="font-size:12px; font-weight:700; color:#94a3b8; background:#f1f5f9; padding:3px 10px; border-radius:100px;">{{ $question->points }} {{ Str::plural('point', $question->points) }}</span>
                </div>

                <p style="font-size:17px; font-weight:500; color:#0f172a; line-height:1.65; margin:8px 0 24px;">
                    {{ $question->question_text }}
                    @if($question->question_type === 'multiple_select')
                    <span style="font-size:13px; color:#64748b; font-weight:400;"> (Select all that apply.)</span>
                    @endif
                </p>

                @if($question->question_image ?? false)
                <img src="{{ Storage::url($question->question_image) }}" alt="Question image"
                     style="max-width:100%; border-radius:8px; margin-bottom:20px; border:1px solid #e2e8f0;">
                @endif

                {{-- Options --}}
                <div class="options-wrapper">
                    @if(in_array($question->question_type, ['multiple_choice', 'true_false']))
                        @foreach($question->getOptionsForDisplay() as $key => $option)
                        <label class="option-row" data-qid="{{ $question->id }}">
                            <input type="radio"
                                   name="answers[{{ $question->id }}]"
                                   value="{{ $key }}"
                                   onchange="handleAnswerChange()">
                            <span class="opt-text">{{ $option }}</span>
                        </label>
                        @endforeach
                    @elseif($question->question_type === 'multiple_select')
                        @foreach($question->options as $key => $option)
                        <label class="option-row" data-qid="{{ $question->id }}">
                            <input type="checkbox"
                                   name="answers[{{ $question->id }}][]"
                                   value="{{ $key }}"
                                   onchange="handleAnswerChange()">
                            <span class="opt-text">{{ $option }}</span>
                        </label>
                        @endforeach
                    @endif
                </div>

            </div>
            @endforeach

            {{-- Honor code + Submit --}}
            <div class="honor-section">
                <h3 style="font-size:15px; font-weight:700; color:#0f172a; margin:0 0 12px;">
                    Honor Code
                    <a href="#" style="font-size:13px; font-weight:600; color:#6366f1; margin-left:8px; text-decoration:none;">
                        Learn more ↗
                    </a>
                </h3>

                <label style="display:flex; align-items:flex-start; gap:12px; cursor:pointer; margin-bottom:16px;">
                    <input type="checkbox" id="honorCheckbox"
                           style="width:18px; height:18px; accent-color:#6366f1; margin-top:2px; flex-shrink:0;"
                           onchange="updateSubmitBtn()">
                    <span style="font-size:14px; color:#475569; line-height:1.6;">
                        I understand that submitting work that isn't my own may result in permanent failure of this course or deactivation of my account.*
                    </span>
                </label>
                <p id="honorError" style="font-size:13px; color:#dc2626; display:none; margin:0 0 16px;">
                    You must select the checkbox in order to submit the assessment.
                </p>

                <input type="text" id="learnerName" placeholder="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}"
                       style="width:260px; padding:12px 16px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; color:#334155; outline:none;"
                       onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                <p style="font-size:12px; color:#94a3b8; margin:6px 0 24px;">Use the name on your government issued ID *</p>

                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                    <button type="button" id="submitBtn" onclick="confirmSubmit()"
                            style="background:#e2e8f0; color:#94a3b8; padding:14px 36px; border-radius:10px; border:none; font-size:15px; font-weight:700; cursor:not-allowed; transition:all .2s;">
                        Submit
                    </button>
                    <button type="button" onclick="saveDraft()"
                            style="background:transparent; color:#6366f1; padding:14px 24px; border-radius:10px; border:2px solid #e0e7ff; font-size:15px; font-weight:700; cursor:pointer; transition:all .2s;"
                            onmouseover="this.style.background='#eef2ff'" onmouseout="this.style.background='transparent'">
                        Save draft
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>

{{-- Confirm Submit Modal --}}
<div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:40px; max-width:460px; width:90%; text-align:center; box-shadow:0 25px 50px rgba(0,0,0,.15);">
        <div style="width:56px; height:56px; background:#eef2ff; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="28" height="28" fill="none" stroke="#6366f1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 style="font-size:20px; font-weight:800; color:#0f172a; margin:0 0 12px;">Submit Assessment?</h3>
        <p id="modalSummary" style="font-size:15px; color:#64748b; margin:0 0 8px; line-height:1.6;"></p>
        <p id="unansweredWarning" style="display:none; font-size:14px; color:#b45309; background:#fef3c7; border-radius:8px; padding:10px 16px; margin:12px 0 0;"></p>
        <div style="display:flex; gap:12px; justify-content:center; margin-top:28px;">
            <button onclick="closeModal()"
                    style="padding:12px 24px; border-radius:10px; border:2px solid #e2e8f0; background:transparent; color:#475569; font-size:15px; font-weight:700; cursor:pointer;">
                Review Answers
            </button>
            <button id="confirmSubmitBtn" onclick="submitAssessment()"
                    style="padding:12px 28px; border-radius:10px; background:#4f46e5; color:#fff; border:none; font-size:15px; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(79,70,229,.25);">
                Submit Now
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const questionIds = @json($questions->pluck('id'));
const totalQuestions = {{ $questions->count() }};

// ── Highlight selected options ──
document.querySelectorAll('.option-row input').forEach(input => {
    input.addEventListener('change', function() {
        const name = this.name;
        if (this.type === 'radio') {
            document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                r.closest('.option-row').classList.remove('selected');
            });
            if (this.checked) this.closest('.option-row').classList.add('selected');
        } else {
            this.closest('.option-row').classList.toggle('selected', this.checked);
        }
    });
});

// ── Answer count tracker ──
function handleAnswerChange() {
    let count = 0;
    questionIds.forEach(id => {
        const radios = document.querySelectorAll(`input[name="answers[${id}]"]:checked`);
        const checks = document.querySelectorAll(`input[name="answers[${id}][]"]:checked`);
        if (radios.length > 0 || checks.length > 0) count++;
    });
    document.getElementById('answeredCount').textContent = count;
    updateSubmitBtn();
}

function updateSubmitBtn() {
    const honored = document.getElementById('honorCheckbox').checked;
    const btn = document.getElementById('submitBtn');
    if (honored) {
        btn.style.background = '#4f46e5';
        btn.style.color = '#fff';
        btn.style.cursor = 'pointer';
        btn.style.boxShadow = '0 4px 14px rgba(79,70,229,.25)';
    } else {
        btn.style.background = '#e2e8f0';
        btn.style.color = '#94a3b8';
        btn.style.cursor = 'not-allowed';
        btn.style.boxShadow = 'none';
    }
}

// ── Timer ──
@if($assessment->time_limit_minutes)
const endTime = new Date('{{ $attempt->started_at }}').getTime() + {{ $assessment->time_limit_minutes }} * 60000;
function tick() {
    const remaining = Math.max(0, endTime - Date.now());
    const m = Math.floor(remaining / 60000);
    const s = Math.floor((remaining % 60000) / 1000);
    const el = document.getElementById('timerText');
    el.textContent = `${m}:${s.toString().padStart(2,'0')}`;
    const disp = document.getElementById('timerDisplay');
    if (m < 5) { disp.style.color = '#dc2626'; }
    if (remaining === 0) { submitAssessment(); return; }
    setTimeout(tick, 1000);
}
tick();
@endif

// ── Save draft (auto-save) ──
function collectAnswers() {
    const answers = {};
    questionIds.forEach(id => {
        const multi = [...document.querySelectorAll(`input[name="answers[${id}][]"]:checked`)].map(i => i.value);
        const single = document.querySelector(`input[name="answers[${id}]"]:checked`);
        if (multi.length > 0) answers[id] = multi;
        else if (single) answers[id] = single.value;
        else answers[id] = null;
    });
    return answers;
}

function saveDraft() {
    const btn = event.target;
    btn.textContent = 'Saving...';
    fetch('{{ route("learner.attempts.save-draft", $attempt->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json', 'Accept': 'application/json'
        },
        body: JSON.stringify({ answers: collectAnswers() })
    })
    .then(r => r.json())
    .then(() => { btn.textContent = 'Saved ✓'; setTimeout(() => btn.textContent = 'Save draft', 2000); })
    .catch(() => { btn.textContent = 'Save draft'; toastr.error('Could not save draft.'); });
}

// ── Submit flow ──
function confirmSubmit() {
    if (!document.getElementById('honorCheckbox').checked) {
        document.getElementById('honorError').style.display = 'block';
        document.getElementById('honorCheckbox').focus();
        return;
    }
    const answered = parseInt(document.getElementById('answeredCount').textContent);
    const unanswered = totalQuestions - answered;
    document.getElementById('modalSummary').textContent =
        `You have answered ${answered} out of ${totalQuestions} questions.`;
    const warn = document.getElementById('unansweredWarning');
    if (unanswered > 0) {
        warn.textContent = `You have ${unanswered} unanswered question${unanswered > 1 ? 's' : ''}. Unanswered questions will be marked as incorrect.`;
        warn.style.display = 'block';
    } else {
        warn.style.display = 'none';
    }
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function submitAssessment() {
    const btn = document.getElementById('confirmSubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Submitting...';

    fetch('{{ route("learner.attempts.submit", $attempt->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json', 'Accept': 'application/json'
        },
        body: JSON.stringify({ answers: collectAnswers() })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            toastr.error(data.message || 'Submission failed. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Submit Now';
            closeModal();
        }
    })
    .catch(() => {
        toastr.error('A network error occurred. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Submit Now';
        closeModal();
    });
}

// ── Prevent accidental leave ──
window.addEventListener('beforeunload', e => { e.preventDefault(); e.returnValue = ''; });
</script>
@endpush