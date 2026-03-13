@extends('mentor.layouts.app')
@section('title', $program->name)

@section('content')

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('mentor.programs.index') }}">Course Management</a></div>
        <h1>{{ $program->name }}</h1>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        @if($program->status === 'draft')
            <a href="{{ route('mentor.programs.edit', $program) }}" class="btn btn-ghost btn-sm">Edit Details</a>
            <button onclick="submitForReview()" class="btn btn-primary btn-sm">Submit for Review</button>
        @elseif($program->status === 'under_review')
            <span class="badge badge-yellow" style="padding: 0.35rem 0.85rem;">Awaiting Admin Review</span>
        @elseif($program->status === 'active')
            <span class="badge badge-green" style="padding: 0.35rem 0.85rem;">Live</span>
        @else
            <span class="badge badge-gray" style="padding: 0.35rem 0.85rem;">Offline</span>
        @endif
    </div>
</div>

{{-- Stats bar --}}
<div style="background: var(--white); border-bottom: 1px solid var(--border); padding: 0 2rem;">
    <div style="max-width: 1100px; margin: 0 auto; display: flex; gap: 2.5rem; padding: 0.9rem 0;">
        @foreach(['modules' => 'Modules', 'weeks' => 'Weeks', 'contents' => 'Content items', 'assessments' => 'Assessments', 'enrolled' => 'Learners'] as $key => $label)
        <div style="text-align: center;">
            <div style="font-weight: 600; font-size: 1.1rem;">{{ $stats[$key] }}</div>
            <div class="text-muted text-small">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</div>

@if($program->review_notes)
<div style="padding: 0.75rem 2rem; background: #fffbeb; border-bottom: 1px solid #fde68a; font-size: 0.875rem; color: #92400e;">
    <strong>Admin feedback:</strong> {{ $program->review_notes }}
</div>
@endif

<div class="container section">

    {{-- Curriculum builder --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.1rem;">Curriculum</h2>
        @if(in_array($program->status, ['draft', 'inactive']))
        <button onclick="openModal('module-modal')" class="btn btn-outline btn-sm">Add Module</button>
        @endif
    </div>

    @if($program->modules->isEmpty())
    <div class="card card-body" style="text-align: center; color: var(--muted); padding: 3rem;">
        <p>Start building your curriculum by adding the first module.</p>
        @if(in_array($program->status, ['draft', 'inactive']))
        <button onclick="openModal('module-modal')" class="btn btn-primary" style="margin-top: 1rem;">Add Module</button>
        @endif
    </div>
    @endif

    <div id="modules-list">
    @foreach($program->modules as $module)
    <div class="card" style="margin-bottom: 1rem;" data-module-id="{{ $module->id }}">

        {{-- Module header --}}
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="font-weight: 600;">{{ $module->title }}</div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span class="text-muted text-small">{{ $module->weeks->count() }} week{{ $module->weeks->count() !== 1 ? 's' : '' }}</span>
                @if(in_array($program->status, ['draft', 'inactive']))
                <button onclick="openAddWeek({{ $module->id }}, '{{ addslashes($module->title) }}')" class="btn btn-sm btn-ghost">Add Week</button>
                <button onclick="editModule({{ $module->id }}, '{{ addslashes($module->title) }}')" class="btn btn-sm btn-ghost" style="color: var(--muted);">Edit</button>
                <button onclick="deleteModule({{ $module->id }}, '{{ addslashes($module->title) }}')" class="btn btn-sm" style="border: none; color: var(--muted); background: none; cursor: pointer; padding: 0.35rem 0.5rem;">Remove</button>
                @endif
            </div>
        </div>

        {{-- Weeks --}}
        @if($module->weeks->isEmpty())
        <div style="padding: 1.25rem; color: var(--muted); font-size: 0.875rem; text-align: center;">
            No weeks yet.
            @if(in_array($program->status, ['draft', 'inactive']))
            <button onclick="openAddWeek({{ $module->id }}, '{{ addslashes($module->title) }}')" style="background: none; border: none; color: var(--blue); cursor: pointer; font-size: 0.875rem;">Add week</button>
            @endif
        </div>
        @endif

        @foreach($module->weeks as $week)
        <div style="border-bottom: 1px solid var(--border);" id="week-{{ $week->id }}">

            {{-- Week row --}}
            <div style="padding: 0.85rem 1.25rem 0.85rem 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: var(--bg);">
                <div>
                    <span style="font-size: 0.78rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em;">Week {{ $week->week_number }}</span>
                    <div style="font-weight: 500; font-size: 0.9rem; margin-top: 0.1rem;">{{ $week->title }}</div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-shrink: 0;">
                    <span class="text-muted text-small">{{ $week->contents->count() }} item{{ $week->contents->count() !== 1 ? 's' : '' }}</span>
                    @if($week->has_assessment)
                    <span class="badge badge-blue" style="font-size: 0.7rem;">Assessment</span>
                    @endif
                    @if(in_array($program->status, ['draft', 'inactive']))
                    <button onclick="openAddContent({{ $week->id }}, {{ $module->id }}, '{{ addslashes($week->title) }}')" class="btn btn-sm btn-ghost">Add Content</button>
                    <button onclick="openManageAssessment({{ $week->id }}, {{ $week->assessment?->id ?? 'null' }}, '{{ addslashes($week->title) }}', {{ $week->has_assessment ? 'true' : 'false' }})" class="btn btn-sm btn-ghost">
                        {{ $week->has_assessment ? 'Assessment' : 'Add Assessment' }}
                    </button>
                    <button onclick="deleteWeek({{ $week->id }}, '{{ addslashes($week->title) }}')" style="background: none; border: none; color: var(--muted); cursor: pointer; font-size: 1rem; padding: 0.25rem 0.4rem;">&#215;</button>
                    @endif
                </div>
            </div>

            {{-- Content items --}}
            @foreach($week->contents as $content)
            <div style="padding: 0.6rem 1.25rem 0.6rem 3rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border);" class="content-row">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 0.7rem; text-transform: uppercase; color: var(--muted); font-weight: 500; min-width: 44px;">{{ $content->content_type }}</span>
                    <span style="font-size: 0.875rem;">{{ $content->title }}</span>
                    @if(!$content->is_required)
                    <span class="text-muted text-small">(optional)</span>
                    @endif
                </div>
                @if(in_array($program->status, ['draft', 'inactive']))
                <button onclick="deleteContent({{ $content->id }}, '{{ addslashes($content->title) }}')"
                        style="background: none; border: none; color: var(--muted); cursor: pointer; font-size: 1rem; padding: 0.2rem 0.4rem;">&#215;</button>
                @endif
            </div>
            @endforeach

        </div>
        @endforeach

    </div>
    @endforeach
    </div>

    {{-- Submit for review --}}
    @if($program->status === 'draft' && $stats['weeks'] > 0 && $stats['contents'] > 0)
    <div class="card card-body" style="margin-top: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
        <div>
            <div style="font-weight: 500;">Ready for review?</div>
            <div class="text-muted text-small">Admin will review and publish the program. You can continue editing until you submit.</div>
        </div>
        <button onclick="submitForReview()" class="btn btn-primary">Submit for Review</button>
    </div>
    @endif

</div>


{{-- ════════════════════ MODALS ════════════════════ --}}

{{-- Add Module --}}
<div class="modal-overlay" id="module-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('module-modal')">&#215;</button>
        <h2 id="module-modal-title">Add Module</h2>
        <form id="module-form" onsubmit="saveModule(event)">
            <input type="hidden" id="module-id" value="">
            <div class="form-group">
                <label class="form-label">Module Title</label>
                <input type="text" id="module-title-input" class="form-control" placeholder="e.g. Module 1: Foundations" required>
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" onclick="closeModal('module-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Week --}}
<div class="modal-overlay" id="week-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('week-modal')">&#215;</button>
        <h2>Add Week</h2>
        <p class="text-muted text-small" style="margin-bottom: 1rem;" id="week-modal-subtitle"></p>
        <form id="week-form" onsubmit="saveWeek(event)">
            <input type="hidden" id="week-module-id" value="">
            <div class="form-group">
                <label class="form-label">Week Title</label>
                <input type="text" id="week-title-input" class="form-control" placeholder="e.g. Introduction to the topic" required>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <input type="checkbox" id="week-has-assessment" style="width:16px; height:16px;">
                <label for="week-has-assessment" class="form-label" style="margin: 0;">Include an assessment for this week</label>
            </div>
            <div id="pass-pct-row" style="display: none;" class="form-group">
                <label class="form-label">Pass Percentage</label>
                <input type="number" id="week-pass-pct" class="form-control" value="70" min="1" max="100">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Add Week</button>
                <button type="button" onclick="closeModal('week-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Content --}}
<div class="modal-overlay" id="content-modal">
    <div class="modal" style="max-width: 580px;">
        <button class="modal-close" onclick="closeModal('content-modal')">&#215;</button>
        <h2>Add Content</h2>
        <p class="text-muted text-small" id="content-modal-subtitle" style="margin-bottom: 1rem;"></p>
        <form id="content-form" onsubmit="saveContent(event)" enctype="multipart/form-data">
            <input type="hidden" id="content-week-id">
            <input type="hidden" id="content-module-id">

            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" id="content-title" class="form-control" required placeholder="e.g. Introduction to Variables">
            </div>

            <div class="form-group">
                <label class="form-label">Content Type</label>
                <select id="content-type" class="form-control" onchange="onContentTypeChange(this.value)" required>
                    <option value="">Select type</option>
                    <option value="video">Video</option>
                    <option value="pdf">PDF</option>
                    <option value="link">Link</option>
                    <option value="article">Article</option>
                </select>
            </div>

            <div id="field-video" style="display:none;">
                <div class="form-group">
                    <label class="form-label">Video URL</label>
                    <input type="url" id="content-video-url" class="form-control" placeholder="https://youtube.com/...">
                </div>
                <div class="form-group">
                    <label class="form-label">Duration (minutes)</label>
                    <input type="number" id="content-video-duration" class="form-control" min="1" placeholder="e.g. 12">
                </div>
            </div>

            <div id="field-pdf" style="display:none;" class="form-group">
                <label class="form-label">Upload PDF</label>
                <input type="file" id="content-file" class="form-control" accept=".pdf">
                <div class="form-hint">Max 20MB</div>
            </div>

            <div id="field-link" style="display:none;" class="form-group">
                <label class="form-label">External URL</label>
                <input type="url" id="content-ext-url" class="form-control" placeholder="https://...">
            </div>

            <div id="field-article" style="display:none;" class="form-group">
                <label class="form-label">Article Content</label>
                <textarea id="content-text" class="form-control" rows="6" placeholder="Write the article here..."></textarea>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <input type="checkbox" id="content-required" checked style="width:16px;height:16px;">
                <label for="content-required" class="form-label" style="margin:0;">Required to complete this week</label>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Add Content</button>
                <button type="button" onclick="closeModal('content-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Assessment modal --}}
<div class="modal-overlay" id="assessment-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('assessment-modal')">&#215;</button>
        <h2 id="assessment-modal-title">Assessment</h2>
        <p class="text-muted text-small" id="assessment-modal-subtitle" style="margin-bottom: 1rem;"></p>
        <form id="assessment-form" onsubmit="saveAssessment(event)">
            <input type="hidden" id="assessment-week-id">
            <input type="hidden" id="assessment-id">
            <div class="form-group">
                <label class="form-label">Assessment Title</label>
                <input type="text" id="assessment-title" class="form-control" required placeholder="e.g. Week 1 Quiz">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Pass Percentage</label>
                    <input type="number" id="assessment-pass-pct" class="form-control" value="70" min="1" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Time Limit (minutes)</label>
                    <input type="number" id="assessment-time" class="form-control" min="1" placeholder="Leave blank for no limit">
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <input type="checkbox" id="assessment-randomize" style="width:16px;height:16px;">
                <label for="assessment-randomize" class="form-label" style="margin:0;">Randomise question order</label>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary">Save Assessment</button>
                <a id="assessment-questions-link" href="#" class="btn btn-outline" style="display:none;">Manage Questions</a>
                <button type="button" onclick="closeModal('assessment-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const PROGRAM_ID = {{ $program->id }};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close on overlay click
document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

// ── Modules ──────────────────────────────────────────────────────────────────
let editingModuleId = null;

function editModule(id, title) {
    editingModuleId = id;
    document.getElementById('module-modal-title').textContent = 'Edit Module';
    document.getElementById('module-id').value = id;
    document.getElementById('module-title-input').value = title;
    openModal('module-modal');
}

function openModal_newModule() {
    editingModuleId = null;
    document.getElementById('module-modal-title').textContent = 'Add Module';
    document.getElementById('module-id').value = '';
    document.getElementById('module-title-input').value = '';
    openModal('module-modal');
}
// Override the button
document.querySelector('[onclick="openModal(\'module-modal\')"]')
    ?.setAttribute('onclick', 'openModal_newModule()');

async function saveModule(e) {
    e.preventDefault();
    const title = document.getElementById('module-title-input').value.trim();
    const id    = document.getElementById('module-id').value;

    if (id) {
        await api('PUT', `/mentor/programs/${PROGRAM_ID}/modules/${id}`, { title });
    } else {
        await api('POST', `/mentor/programs/${PROGRAM_ID}/modules`, { title });
    }
    closeModal('module-modal');
    location.reload();
}

async function deleteModule(id, title) {
    if (!confirm(`Remove module "${title}" and all its weeks?`)) return;
    await api('DELETE', `/mentor/programs/${PROGRAM_ID}/modules/${id}`);
    location.reload();
}

// ── Weeks ─────────────────────────────────────────────────────────────────────
function openAddWeek(moduleId, moduleTitle) {
    document.getElementById('week-module-id').value = moduleId;
    document.getElementById('week-modal-subtitle').textContent = `Adding to: ${moduleTitle}`;
    document.getElementById('week-title-input').value = '';
    document.getElementById('week-has-assessment').checked = false;
    document.getElementById('pass-pct-row').style.display = 'none';
    openModal('week-modal');
}

document.getElementById('week-has-assessment')?.addEventListener('change', function() {
    document.getElementById('pass-pct-row').style.display = this.checked ? 'block' : 'none';
});

async function saveWeek(e) {
    e.preventDefault();
    const moduleId = document.getElementById('week-module-id').value;
    await api('POST', `/mentor/programs/${PROGRAM_ID}/modules/${moduleId}/weeks`, {
        title:                      document.getElementById('week-title-input').value,
        has_assessment:             document.getElementById('week-has-assessment').checked ? 1 : 0,
        assessment_pass_percentage: document.getElementById('week-pass-pct').value || 70,
    });
    closeModal('week-modal');
    location.reload();
}

async function deleteWeek(id, title) {
    if (!confirm(`Remove week "${title}"?`)) return;
    await api('DELETE', `/mentor/programs/${PROGRAM_ID}/weeks/${id}`);
    location.reload();
}

// ── Content ────────────────────────────────────────────────────────────────────
function openAddContent(weekId, moduleId, weekTitle) {
    document.getElementById('content-week-id').value = weekId;
    document.getElementById('content-module-id').value = moduleId;
    document.getElementById('content-modal-subtitle').textContent = weekTitle;
    document.getElementById('content-form').reset();
    onContentTypeChange('');
    openModal('content-modal');
}

function onContentTypeChange(type) {
    ['video','pdf','link','article'].forEach(t => {
        document.getElementById(`field-${t}`).style.display = (t === type) ? 'block' : 'none';
    });
}

async function saveContent(e) {
    e.preventDefault();
    const weekId = document.getElementById('content-week-id').value;
    const type   = document.getElementById('content-type').value;
    const form   = new FormData();
    form.append('_token', CSRF);
    form.append('title', document.getElementById('content-title').value);
    form.append('content_type', type);
    form.append('is_required', document.getElementById('content-required').checked ? 1 : 0);

    if (type === 'video') {
        form.append('video_url', document.getElementById('content-video-url').value);
        form.append('video_duration_minutes', document.getElementById('content-video-duration').value);
    } else if (type === 'pdf') {
        const file = document.getElementById('content-file').files[0];
        if (file) form.append('file', file);
    } else if (type === 'link') {
        form.append('external_url', document.getElementById('content-ext-url').value);
    } else if (type === 'article') {
        form.append('text_content', document.getElementById('content-text').value);
    }

    const res = await fetch(`/mentor/programs/${PROGRAM_ID}/weeks/${weekId}/contents`, {
        method: 'POST', body: form,
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    if (data.success) { closeModal('content-modal'); location.reload(); }
    else alert('Error saving content.');
}

async function deleteContent(id, title) {
    if (!confirm(`Remove "${title}"?`)) return;
    await api('DELETE', `/mentor/programs/${PROGRAM_ID}/contents/${id}`);
    location.reload();
}

// ── Assessment ─────────────────────────────────────────────────────────────────
function openManageAssessment(weekId, assessmentId, weekTitle, hasAssessment) {
    document.getElementById('assessment-week-id').value = weekId;
    document.getElementById('assessment-id').value = assessmentId || '';
    document.getElementById('assessment-modal-subtitle').textContent = weekTitle;
    document.getElementById('assessment-modal-title').textContent = hasAssessment ? 'Edit Assessment' : 'Add Assessment';
    document.getElementById('assessment-title').value = '';

    const qLink = document.getElementById('assessment-questions-link');
    if (assessmentId) {
        qLink.style.display = 'inline-flex';
        qLink.href = `/mentor/programs/${PROGRAM_ID}/assessments/${assessmentId}/questions`;
    } else {
        qLink.style.display = 'none';
    }

    openModal('assessment-modal');
}

async function saveAssessment(e) {
    e.preventDefault();
    const weekId = document.getElementById('assessment-week-id').value;
    const assId  = document.getElementById('assessment-id').value;
    const body   = {
        title:               document.getElementById('assessment-title').value,
        pass_percentage:     document.getElementById('assessment-pass-pct').value,
        time_limit_minutes:  document.getElementById('assessment-time').value || null,
        randomize_questions: document.getElementById('assessment-randomize').checked ? 1 : 0,
    };

    if (assId) {
        await api('PUT', `/mentor/programs/${PROGRAM_ID}/assessments/${assId}`, body);
    } else {
        await api('POST', `/mentor/programs/${PROGRAM_ID}/weeks/${weekId}/assessment`, body);
    }
    closeModal('assessment-modal');
    location.reload();
}

// ── Submit for review ──────────────────────────────────────────────────────────
async function submitForReview() {
    if (!confirm('Submit this program for admin review? You won\'t be able to edit it until they respond.')) return;
    const res = await fetch(`/mentor/programs/${PROGRAM_ID}/submit`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json().catch(() => ({}));
    if (res.ok) location.reload();
    else alert(data.message || 'Could not submit.');
}

// ── Generic API helper ────────────────────────────────────────────────────────
async function api(method, url, body = null) {
    const opts = {
        method,
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    };
    if (body) {
        if (method === 'DELETE' || method === 'PUT') {
            body['_method'] = method;
            opts.method = 'POST';
        }
        opts.body = JSON.stringify(body);
    }
    const res  = await fetch(url, opts);
    const data = await res.json().catch(() => ({}));
    if (!res.ok) { alert(data.message || 'An error occurred.'); throw new Error(); }
    return data;
}
</script>
@endpush