@extends('layouts.app')
@section('title', $assessment->is_final ? 'Final Exam Questions' : 'Quiz Questions')

@php $isFinal = $assessment->is_final; @endphp

@section('content')

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('mentor.programs.index') }}">Course Management</a> /
            <a href="{{ route('mentor.programs.show', ['program' => $program, 'tab' => $isFinal ? 'final-exam' : 'curriculum']) }}">
                {{ $program->name }}
            </a> /
            {{ $isFinal ? 'Final Examination' : 'Week '.$week->week_number }}
        </div>
        <h1>{{ $assessment->title }}</h1>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a href="{{ route('mentor.assessments.questions.template') }}" class="btn btn-ghost btn-sm">Download Template</a>
        <button onclick="openModal('import-modal')" class="btn btn-outline btn-sm">Import CSV</button>
        <button onclick="openAddQuestion()" class="btn btn-primary btn-sm">Add Question</button>
        <a href="{{ route('mentor.programs.show', ['program' => $program, 'tab' => $isFinal ? 'final-exam' : 'curriculum']) }}"
           class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

{{-- Assessment meta bar --}}
<div style="background:{{ $isFinal ? '#f5f3ff' : 'var(--white)' }};border-bottom:1px solid {{ $isFinal ? '#ddd6fe' : 'var(--border)' }};padding:0 2rem;">
    <div style="max-width:1100px;margin:0 auto;display:flex;gap:2.5rem;padding:0.8rem 0;flex-wrap:wrap;align-items:center;">
        @if($isFinal)
        <span style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#7c3aed;">
            🎓 Final Examination
        </span>
        @else
        <span style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--blue);">
            Week {{ $week->week_number }} Quiz
        </span>
        @endif
        <span style="font-size:0.875rem;color:var(--muted);">
            Pass mark:
            <strong style="color:var(--text);">
                {{ $isFinal ? $assessment->pass_percentage.'%' : '100% (all correct)' }}
            </strong>
        </span>
        <span style="font-size:0.875rem;color:var(--muted);">
            Time limit:
            <strong style="color:var(--text);">{{ $assessment->time_limit_minutes ? $assessment->time_limit_minutes.' min' : 'None' }}</strong>
        </span>
        <span style="font-size:0.875rem;color:var(--muted);">
            Questions: <strong style="color:var(--text);">{{ $assessment->questions->count() }}</strong>
        </span>
        <span style="font-size:0.875rem;color:var(--muted);">
            Total points: <strong style="color:var(--text);">{{ $assessment->questions->sum('points') }}</strong>
        </span>
        @if($isFinal)
        <span style="font-size:0.875rem;color:var(--muted);">
            Cooldown on fail: <strong style="color:var(--text);">48 hours</strong>
        </span>
        @endif
    </div>
</div>

<div class="container section">

    @if($assessment->questions->isEmpty())
    <div class="card card-body" style="text-align:center;color:var(--muted);padding:3rem;">
        <p style="margin-bottom:1rem;">No questions yet. Import a CSV or add questions manually.</p>
        <div style="display:flex;gap:0.5rem;justify-content:center;">
            <button onclick="openModal('import-modal')" class="btn btn-outline">Import CSV</button>
            <button onclick="openAddQuestion()" class="btn btn-primary">Add Question</button>
        </div>
    </div>
    @else

    <div id="questions-list">
    @foreach($assessment->questions as $i => $q)
    @php $opts = is_array($q->options) ? $q->options : (json_decode($q->options, true) ?? []); @endphp
    <div class="card" style="margin-bottom:0.75rem;" id="q-{{ $q->id }}">
        <div class="card-body">
            <div style="display:flex;gap:1rem;justify-content:space-between;align-items:flex-start;">
                <div style="flex:1;">
                    <div style="display:flex;gap:0.75rem;align-items:baseline;margin-bottom:0.5rem;">
                        <span style="font-size:0.75rem;font-weight:600;color:var(--muted);">Q{{ $i + 1 }}</span>
                        <span class="badge badge-gray" style="font-size:0.7rem;">{{ str_replace('_',' ',$q->question_type) }}</span>
                        <span class="text-muted text-small">{{ $q->points }} pt{{ $q->points !== 1 ? 's' : '' }}</span>
                    </div>
                    <div style="font-weight:500;margin-bottom:0.75rem;">{{ $q->question_text }}</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.35rem 1rem;">
                        @foreach($opts as $opt)
                        @php $correct = is_array($q->correct_answer) ? $q->correct_answer : (json_decode($q->correct_answer, true) ?? []); @endphp
                        <div style="font-size:0.875rem;display:flex;gap:0.4rem;align-items:center;">
                            <span style="color:{{ in_array($opt,$correct) ? 'var(--success)' : 'var(--muted)' }};font-weight:{{ in_array($opt,$correct) ? '600' : '400' }};">
                                {{ in_array($opt,$correct) ? '✓' : '·' }}
                            </span>
                            {{ $opt }}
                        </div>
                        @endforeach
                    </div>
                    @if($q->explanation)
                    <div class="text-muted text-small" style="margin-top:0.6rem;font-style:italic;">{{ $q->explanation }}</div>
                    @endif
                </div>
                <div style="display:flex;gap:0.35rem;flex-shrink:0;">
                    <button onclick="openEditQuestion({{ $q->id }}, {{ json_encode($q) }})" class="btn btn-sm btn-ghost">Edit</button>
                    <button onclick="deleteQuestion({{ $q->id }})" class="btn btn-sm btn-danger">Remove</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    </div>

    <div style="margin-top:1.5rem;text-align:right;">
        <a href="{{ route('mentor.programs.show', ['program' => $program, 'tab' => $isFinal ? 'final-exam' : 'curriculum']) }}"
           style="font-size:0.875rem;color:var(--muted);text-decoration:none;">
            ← Back to {{ $program->name }}
        </a>
    </div>

    @endif
</div>

{{-- ════ MODALS ════ --}}

{{-- Add / Edit Question --}}
<div class="modal-overlay" id="question-modal">
    <div class="modal" style="max-width:600px;">
        <button class="modal-close" onclick="closeModal('question-modal')">&#215;</button>
        <h2 id="q-modal-title">Add Question</h2>
        <form id="question-form" onsubmit="saveQuestion(event)">
            <input type="hidden" id="q-id">
            <div class="form-group">
                <label class="form-label">Question Type</label>
                <select id="q-type" class="form-control" onchange="onQTypeChange(this.value)" required>
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True / False</option>
                    <option value="multiple_select">Multiple Select (all that apply)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Question</label>
                <textarea id="q-text" class="form-control" rows="3" required placeholder="Write your question here..."></textarea>
            </div>
            <div id="options-section">
                <div class="form-label" style="margin-bottom:0.5rem;">Answer Options</div>
                <div style="display:grid;gap:0.5rem;" id="options-rows">
                    @foreach(['A','B','C','D'] as $letter)
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        <span style="min-width:1.2rem;font-size:0.8rem;color:var(--muted);font-weight:600;">{{ $letter }}</span>
                        <input type="text" class="form-control option-input" id="opt-{{ strtolower($letter) }}" placeholder="Option {{ $letter }}">
                        <input type="checkbox" class="correct-check" id="correct-{{ strtolower($letter) }}" style="width:16px;height:16px;" title="Mark as correct">
                    </div>
                    @endforeach
                </div>
                <div class="form-hint" id="correct-hint">Check the correct answer(s)</div>
            </div>
            <div id="tf-section" style="display:none;">
                <div class="form-label" style="margin-bottom:0.5rem;">Correct Answer</div>
                <select id="tf-correct" class="form-control">
                    <option value="True">True</option>
                    <option value="False">False</option>
                </select>
            </div>
            <div class="grid-2" style="margin-top:1rem;">
                <div class="form-group">
                    <label class="form-label">Points</label>
                    <input type="number" id="q-points" class="form-control" value="1" min="1">
                </div>
                <div></div>
            </div>
            <div class="form-group">
                <label class="form-label">
                    Explanation
                    <span class="text-muted text-small">(optional — shown to mentor only, never to learners)</span>
                </label>
                <textarea id="q-explanation" class="form-control" rows="2" placeholder="Why is this the correct answer?"></textarea>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button type="submit" class="btn btn-primary" id="q-save-btn">Save Question</button>
                <button type="button" onclick="closeModal('question-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- CSV Import --}}
<div class="modal-overlay" id="import-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('import-modal')">&#215;</button>
        <h2>Import Questions from CSV</h2>
        <p class="text-muted text-small" style="margin-bottom:1rem;">
            Download the template, fill it in, then upload it here.
            Existing questions are kept — imported questions are added.
        </p>
        <form method="POST"
              action="{{ route('mentor.assessments.questions.import', [$program, $assessment]) }}"
              enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">CSV File</label>
                <input type="file" name="csv_file" accept=".csv,.txt" class="form-control" required>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Import</button>
                <a href="{{ route('mentor.assessments.questions.template') }}" class="btn btn-ghost">Download Template</a>
                <button type="button" onclick="closeModal('import-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const PROGRAM_ID    = {{ $program->id }};
const ASSESSMENT_ID = {{ $assessment->id }};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); }));

// ── Question type toggle ──────────────────────────────────────────────────────
function onQTypeChange(type) {
    const isTF = type === 'true_false';
    document.getElementById('options-section').style.display = isTF ? 'none' : 'block';
    document.getElementById('tf-section').style.display      = isTF ? 'block' : 'none';
    document.getElementById('correct-hint').textContent =
        type === 'multiple_select' ? 'Check all correct answers' : 'Check the one correct answer';
}

// ── Reset form ────────────────────────────────────────────────────────────────
function resetForm() {
    document.getElementById('q-id').value = '';
    document.getElementById('q-modal-title').textContent = 'Add Question';
    document.getElementById('q-type').value = 'multiple_choice';
    document.getElementById('q-text').value = '';
    document.getElementById('q-points').value = '1';
    document.getElementById('q-explanation').value = '';
    document.getElementById('q-save-btn').disabled = false;
    document.getElementById('q-save-btn').textContent = 'Save Question';
    ['a','b','c','d'].forEach(l => {
        document.getElementById(`opt-${l}`).value = '';
        document.getElementById(`correct-${l}`).checked = false;
    });
    onQTypeChange('multiple_choice');
}

function openAddQuestion() {
    resetForm();
    openModal('question-modal');
}

// ── Edit question ─────────────────────────────────────────────────────────────
function openEditQuestion(id, q) {
    resetForm();
    document.getElementById('q-modal-title').textContent = 'Edit Question';
    document.getElementById('q-id').value          = id;
    document.getElementById('q-type').value        = q.question_type;
    document.getElementById('q-text').value        = q.question_text;
    document.getElementById('q-points').value      = q.points;
    document.getElementById('q-explanation').value = q.explanation || '';

    const opts    = Array.isArray(q.options)        ? q.options        : JSON.parse(q.options        || '[]');
    const correct = Array.isArray(q.correct_answer) ? q.correct_answer : JSON.parse(q.correct_answer || '[]');

    onQTypeChange(q.question_type);

    if (q.question_type === 'true_false') {
        document.getElementById('tf-correct').value = correct[0] || 'True';
    } else {
        ['a','b','c','d'].forEach((l, i) => {
            const opt = opts[i];
            if (opt !== undefined) {
                document.getElementById(`opt-${l}`).value = opt;
                document.getElementById(`correct-${l}`).checked = correct.includes(opt);
            }
        });
    }
    openModal('question-modal');
}

// ── Save question ─────────────────────────────────────────────────────────────
async function saveQuestion(e) {
    e.preventDefault();
    const btn  = document.getElementById('q-save-btn');
    btn.disabled = true; btn.textContent = 'Saving…';

    const id   = document.getElementById('q-id').value;
    const type = document.getElementById('q-type').value;
    let options = [], correct = [];

    if (type === 'true_false') {
        options = ['True', 'False'];
        correct = [document.getElementById('tf-correct').value];
    } else {
        ['a','b','c','d'].forEach(l => {
            const val = document.getElementById(`opt-${l}`).value.trim();
            if (val) {
                options.push(val);
                if (document.getElementById(`correct-${l}`).checked) correct.push(val);
            }
        });
    }

    if (correct.length === 0) {
        alert('Mark at least one correct answer.');
        btn.disabled = false; btn.textContent = 'Save Question';
        return;
    }

    const body = {
        question_type:  type,
        question_text:  document.getElementById('q-text').value,
        options,
        correct_answer: correct,
        points:         parseInt(document.getElementById('q-points').value) || 1,
        explanation:    document.getElementById('q-explanation').value,
    };

    try {
        if (id) {
            await api('PUT', `/mentor/programs/${PROGRAM_ID}/assessments/${ASSESSMENT_ID}/questions/${id}`, body);
        } else {
            await api('POST', `/mentor/programs/${PROGRAM_ID}/assessments/${ASSESSMENT_ID}/questions`, body);
        }
        closeModal('question-modal');
        location.reload();
    } catch {
        btn.disabled = false; btn.textContent = 'Save Question';
    }
}

// ── Delete question ───────────────────────────────────────────────────────────
async function deleteQuestion(id) {
    if (!confirm('Remove this question?')) return;
    await api('DELETE', `/mentor/programs/${PROGRAM_ID}/questions/${id}`);
    document.getElementById(`q-${id}`)?.remove();
    // Update count in header
    const remaining = document.querySelectorAll('[id^="q-"]').length;
    if (remaining === 0) location.reload(); // show empty state
}

// ── Generic API helper ────────────────────────────────────────────────────────
async function api(method, url, body = null) {
    const needsSpoof = method === 'DELETE' || method === 'PUT';
    const fetchUrl   = needsSpoof ? `${url}?_method=${method}` : url;
    const opts = {
        method: needsSpoof ? 'POST' : method,
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    };
    if (body || needsSpoof) {
        opts.body = JSON.stringify(body || {});
    }
    const res  = await fetch(fetchUrl, opts);
    const data = await res.json().catch(() => ({}));
    if (!res.ok) { alert(data.message || 'An error occurred.'); throw new Error(); }
    return data;
}

// Reset form on modal close
document.getElementById('question-modal').querySelector('.modal-close')
    .addEventListener('click', resetForm);
</script>
@endpush