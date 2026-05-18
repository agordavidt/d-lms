@extends('layouts.app')
@section('title', $program->name)

@php
    $editable  = in_array($program->status, ['draft', 'inactive']);
    $activeTab = request('tab', 'curriculum');
    $finalExam = null;
    $finalWeek = null;
    foreach ($program->modules as $mod) {
        foreach ($mod->weeks as $wk) {
            if ($wk->is_final_week && $wk->assessment) {
                $finalExam = $wk->assessment;
                $finalWeek = $wk;
            }
        }
    }
@endphp

@section('content')

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('mentor.programs.index') }}">Course Management</a></div>
        <h1>{{ $program->name }}</h1>
    </div>
    <div style="display:flex;gap:0.5rem;align-items:center;">
        @if($program->status === 'draft')
            <a href="{{ route('mentor.programs.edit', $program) }}" class="btn btn-ghost btn-sm">Edit Details</a>
            <button onclick="submitForReview()" class="btn btn-primary btn-sm">Submit for Review</button>
        @elseif($program->status === 'under_review')
            <span class="badge badge-yellow" style="padding:0.35rem 0.85rem;">Awaiting Admin Review</span>
        @elseif($program->status === 'active')
            <span class="badge badge-green" style="padding:0.35rem 0.85rem;">Live</span>
        @else
            <span class="badge badge-gray" style="padding:0.35rem 0.85rem;">Offline</span>
        @endif
    </div>
</div>

{{-- Stats bar --}}
<div style="background:var(--white);border-bottom:1px solid var(--border);padding:0 2rem;">
    <div style="max-width:1100px;margin:0 auto;display:flex;gap:2.5rem;padding:0.9rem 0;flex-wrap:wrap;">
        @foreach(['modules'=>'Modules','weeks'=>'Weeks','contents'=>'Content Items','assessments'=>'Week Quizzes','enrolled'=>'Learners'] as $key=>$label)
        <div style="text-align:center;">
            <div style="font-weight:600;font-size:1.1rem;">{{ $stats[$key] }}</div>
            <div class="text-muted text-small">{{ $label }}</div>
        </div>
        @endforeach
        <div style="text-align:center;">
            <div style="font-weight:600;font-size:1.1rem;color:{{ $finalExam ? '#7c3aed' : 'var(--muted)' }};">{{ $finalExam ? '✓' : '—' }}</div>
            <div class="text-muted text-small">Final Exam</div>
        </div>
    </div>
</div>

@if($program->review_notes)
<div style="padding:0.75rem 2rem;background:#fffbeb;border-bottom:1px solid #fde68a;font-size:0.875rem;color:#92400e;">
    <strong>Admin feedback:</strong> {{ $program->review_notes }}
</div>
@endif

{{-- Tab bar --}}
<div style="background:var(--white);border-bottom:1px solid var(--border);padding:0 2rem;">
    <div style="max-width:1100px;margin:0 auto;display:flex;">
        <a href="{{ route('mentor.programs.show', ['program'=>$program,'tab'=>'curriculum']) }}"
           style="padding:0.75rem 1.25rem;font-size:0.875rem;font-weight:500;text-decoration:none;margin-bottom:-1px;
                  color:{{ $activeTab==='curriculum'?'var(--blue)':'var(--muted)' }};
                  border-bottom:2px solid {{ $activeTab==='curriculum'?'var(--blue)':'transparent' }};">
            Curriculum
        </a>
        <a href="{{ route('mentor.programs.show', ['program'=>$program,'tab'=>'final-exam']) }}"
           style="padding:0.75rem 1.25rem;font-size:0.875rem;font-weight:500;text-decoration:none;margin-bottom:-1px;display:flex;align-items:center;gap:0.4rem;
                  color:{{ $activeTab==='final-exam'?'#7c3aed':'var(--muted)' }};
                  border-bottom:2px solid {{ $activeTab==='final-exam'?'#7c3aed':'transparent' }};">
            Final Examination
            @if($finalExam)
                <span style="font-size:0.7rem;background:#f5f3ff;color:#7c3aed;padding:0.1rem 0.5rem;border-radius:99px;font-weight:600;">
                    {{ $finalExam->questions->count() }}Q
                </span>
            @else
                <span style="font-size:0.7rem;background:#fef3c7;color:#92400e;padding:0.1rem 0.5rem;border-radius:99px;font-weight:600;">Not set</span>
            @endif
        </a>
    </div>
</div>

<div class="container section">

{{-- ════ CURRICULUM TAB ════ --}}
@if($activeTab === 'curriculum')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <div>
        <h2 style="font-family:'Source Serif 4',serif;font-size:1.05rem;margin-bottom:0.15rem;">Course Modules</h2>
        <p class="text-muted text-small">Each week can have a short quiz — learners must answer all correctly (100%) to progress.</p>
    </div>
    @if($editable)
    <button onclick="openAddModule()" class="btn btn-outline btn-sm">Add Module</button>
    @endif
</div>

@if($program->modules->isEmpty())
<div class="card card-body" style="text-align:center;color:var(--muted);padding:3rem;">
    <p style="margin-bottom:1rem;">Start building your curriculum by adding the first module.</p>
    @if($editable)<button onclick="openAddModule()" class="btn btn-primary">Add Module</button>@endif
</div>
@endif

<div id="modules-list">
@foreach($program->modules as $module)
<div class="card" style="margin-bottom:1rem;" data-module-id="{{ $module->id }}">

    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;">
        <div>
            <div style="font-weight:600;">{{ $module->title }}</div>
            <div class="text-muted text-small">
                {{ $module->weeks->filter(fn($w)=>!$w->is_final_week)->count() }} week{{ $module->weeks->filter(fn($w)=>!$w->is_final_week)->count()!==1?'s':'' }}
            </div>
        </div>
        @if($editable)
        <div style="display:flex;gap:0.5rem;align-items:center;">
            <button onclick="openAddWeek({{ $module->id }}, '{{ addslashes($module->title) }}')" class="btn btn-sm btn-ghost">Add Week</button>
            <button onclick="openEditModule({{ $module->id }}, '{{ addslashes($module->title) }}')" class="btn btn-sm btn-ghost" style="color:var(--muted);">Edit</button>
            <button onclick="deleteModule({{ $module->id }}, '{{ addslashes($module->title) }}')" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:0.35rem 0.5rem;font-size:1rem;">&#215;</button>
        </div>
        @endif
    </div>

    @php $courseWeeks = $module->weeks->filter(fn($w)=>!$w->is_final_week)->sortBy('order')->values(); @endphp

    @if($courseWeeks->isEmpty())
    <div style="padding:1.25rem;color:var(--muted);font-size:0.875rem;text-align:center;">
        No weeks yet.
        @if($editable)<button onclick="openAddWeek({{ $module->id }}, '{{ addslashes($module->title) }}')" style="background:none;border:none;color:var(--blue);cursor:pointer;font-size:0.875rem;">Add week</button>@endif
    </div>
    @endif

    @foreach($courseWeeks as $weekIdx => $week)
    @php $qCount = $week->assessment?->questions->count() ?? 0; @endphp
    <div id="week-{{ $week->id }}" style="border-bottom:1px solid var(--border);">

        <div style="padding:0.85rem 1.25rem 0.85rem 2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;background:var(--bg);">
            <div style="display:flex;align-items:center;gap:0.75rem;min-width:0;">
                {{-- Up/Down reorder --}}
                @if($editable)
                <div style="display:flex;flex-direction:column;gap:2px;flex-shrink:0;">
                    <button onclick="moveWeek({{ $week->id }}, 'up')"
                            style="background:none;border:none;cursor:pointer;color:var(--muted);padding:1px 4px;line-height:1;font-size:0.7rem;"
                            title="Move up" {{ $weekIdx === 0 ? 'disabled' : '' }}>▲</button>
                    <button onclick="moveWeek({{ $week->id }}, 'down')"
                            style="background:none;border:none;cursor:pointer;color:var(--muted);padding:1px 4px;line-height:1;font-size:0.7rem;"
                            title="Move down" {{ $weekIdx === $courseWeeks->count()-1 ? 'disabled' : '' }}>▼</button>
                </div>
                @endif
                <div>
                    <span style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.04em;">Week {{ $week->week_number }}</span>
                    <div style="font-weight:500;font-size:0.9rem;margin-top:0.1rem;">{{ $week->title }}</div>
                </div>
            </div>

            <div style="display:flex;gap:0.5rem;align-items:center;flex-shrink:0;">
                <span class="text-muted text-small">{{ $week->contents->count() }} item{{ $week->contents->count()!==1?'s':'' }}</span>

                @if($week->has_assessment && $week->assessment)
                <span class="badge badge-blue" style="font-size:0.7rem;cursor:pointer;"
                      onclick="openEditQuiz({{ $week->id }}, {{ $week->assessment->id }}, '{{ addslashes($week->title) }}', {{ json_encode(['title'=>$week->assessment->title,'time_limit_minutes'=>$week->assessment->time_limit_minutes,'randomize_questions'=>(bool)$week->assessment->randomize_questions,'questions_count'=>$qCount]) }})">
                    ✓ Quiz · {{ $qCount > 0 ? $qCount.'Q' : 'No questions' }}
                </span>
                @endif

                @if($editable)
                <button onclick="openAddContent({{ $week->id }}, '{{ addslashes($week->title) }}')" class="btn btn-sm btn-ghost">Add Content</button>
                @if($week->has_assessment && $week->assessment)
                <button onclick="openEditQuiz({{ $week->id }}, {{ $week->assessment->id }}, '{{ addslashes($week->title) }}', {{ json_encode(['title'=>$week->assessment->title,'time_limit_minutes'=>$week->assessment->time_limit_minutes,'randomize_questions'=>(bool)$week->assessment->randomize_questions,'questions_count'=>$qCount]) }})"
                        class="btn btn-sm btn-ghost">Edit Quiz</button>
                @else
                <button onclick="openAddQuiz({{ $week->id }}, '{{ addslashes($week->title) }}')" class="btn btn-sm btn-ghost">Add Quiz</button>
                @endif
                <button onclick="deleteWeek({{ $week->id }}, '{{ addslashes($week->title) }}')"
                        style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:1rem;padding:0.25rem 0.4rem;">&#215;</button>
                @endif
            </div>
        </div>

        @foreach($week->contents as $content)
        <div style="padding:0.6rem 1.25rem 0.6rem 3rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <span style="font-size:0.7rem;text-transform:uppercase;color:var(--muted);font-weight:500;min-width:44px;">{{ $content->content_type }}</span>
                <span style="font-size:0.875rem;">{{ $content->title }}</span>
                @if(!$content->is_required)<span class="text-muted text-small">(optional)</span>@endif
            </div>
            @if($editable)
            <button onclick="deleteContent({{ $content->id }}, '{{ addslashes($content->title) }}')"
                    style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:1rem;padding:0.2rem 0.4rem;">&#215;</button>
            @endif
        </div>
        @endforeach

        @if($week->has_assessment && $week->assessment)
        <div style="padding:0.5rem 1.25rem 0.5rem 3rem;display:flex;align-items:center;gap:0.75rem;background:#f5f3ff08;">
            <span style="font-size:0.7rem;text-transform:uppercase;color:#7c3aed;font-weight:600;min-width:44px;">quiz</span>
            <span style="font-size:0.875rem;color:#4c1d95;">{{ $week->assessment->title }}</span>
            <span class="text-muted text-small">· 100% required · {{ $qCount }} question{{ $qCount!==1?'s':'' }}</span>
        </div>
        @endif

    </div>
    @endforeach

</div>
@endforeach
</div>

@if($program->status === 'draft' && $stats['weeks'] > 0 && $stats['contents'] > 0)
<div class="card card-body" style="margin-top:2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
    <div>
        <div style="font-weight:500;">Ready for review?</div>
        <div class="text-muted text-small">Admin will review and publish the program.</div>
    </div>
    <button onclick="submitForReview()" class="btn btn-primary">Submit for Review</button>
</div>
@endif

{{-- ════ FINAL EXAM TAB ════ --}}
@elseif($activeTab === 'final-exam')
<div style="max-width:680px;">

    <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.75rem;">
        <div style="font-weight:600;color:#4c1d95;margin-bottom:0.5rem;font-size:0.95rem;">About the Final Examination</div>
        <ul style="font-size:0.875rem;color:#5b21b6;line-height:1.8;margin:0;padding-left:1.2rem;">
            <li>Unlocked only after learners complete <strong>all modules and weekly quizzes</strong>.</li>
            <li>Pass mark is <strong>75%</strong>. Failed attempts trigger a <strong>48-hour cooldown</strong>.</li>
            <li>Learners see <strong>score only</strong> — no question breakdown on any attempt.</li>
            <li>Only <strong>one final exam</strong> allowed per program.</li>
        </ul>
    </div>

    @if($finalExam)
    <div class="card" style="margin-bottom:1.5rem;">
        <div style="padding:1.1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#7c3aed;margin-bottom:0.2rem;">Final Examination</div>
                <div style="font-weight:600;font-size:1rem;">{{ $finalExam->title }}</div>
            </div>
            @if($editable)
            <button onclick="openEditFinalExam()" class="btn btn-sm btn-ghost">Edit Settings</button>
            @endif
        </div>
        <div style="padding:1rem 1.25rem;display:flex;gap:2rem;font-size:0.875rem;flex-wrap:wrap;">
            <div><div class="text-muted text-small">Pass mark</div><div style="font-weight:600;">{{ $finalExam->pass_percentage }}%</div></div>
            <div><div class="text-muted text-small">Time limit</div><div style="font-weight:600;">{{ $finalExam->time_limit_minutes ? $finalExam->time_limit_minutes.' min' : 'None' }}</div></div>
            <div><div class="text-muted text-small">Questions</div><div style="font-weight:600;{{ $finalExam->questions->count()===0?'color:var(--warning);':'' }}">{{ $finalExam->questions->count()===0?'None yet':$finalExam->questions->count() }}</div></div>
            <div><div class="text-muted text-small">Randomise</div><div style="font-weight:600;">{{ $finalExam->randomize_questions?'Yes':'No' }}</div></div>
            <div><div class="text-muted text-small">Cooldown on fail</div><div style="font-weight:600;">48 hours</div></div>
        </div>
    </div>

    @if($finalExam->questions->count() === 0)
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.25rem;font-size:0.875rem;color:#92400e;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <span>⚠ No questions added yet.</span>
        <a href="{{ route('mentor.assessments.questions', [$program, $finalExam]) }}" class="btn btn-sm btn-outline" style="border-color:#fde68a;color:#92400e;">Add Questions</a>
    </div>
    @else
    <div style="display:flex;gap:0.75rem;margin-bottom:1.25rem;">
        <a href="{{ route('mentor.assessments.questions', [$program, $finalExam]) }}" class="btn btn-outline">
            Manage Questions ({{ $finalExam->questions->count() }})
        </a>
        @if($editable)
        <button onclick="deleteFinalExam({{ $finalExam->id }})" class="btn btn-ghost" style="color:var(--error);">Remove Exam</button>
        @endif
    </div>
    @endif

    @else
    @if($editable)
    <div class="card card-body" style="text-align:center;padding:2.5rem 2rem;color:var(--muted);">
        <div style="font-size:2rem;margin-bottom:0.75rem;">🎓</div>
        <p style="font-weight:500;margin-bottom:0.4rem;color:var(--text);">No final examination set up yet.</p>
        <p class="text-small" style="margin-bottom:1.5rem;">Set up the final exam after completing your curriculum.</p>
        @if($stats['weeks'] === 0)
        <p class="text-small" style="color:var(--warning);">Add at least one week to your curriculum first.</p>
        @else
        <button onclick="openAddFinalExam()" class="btn btn-primary">Set Up Final Examination</button>
        @endif
    </div>
    @else
    <div class="card card-body" style="text-align:center;padding:2.5rem;color:var(--muted);">No final examination configured.</div>
    @endif
    @endif
</div>
@endif

</div>

{{-- ════ MODALS ════ --}}

{{-- Add/Edit Module --}}
<div class="modal-overlay" id="module-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('module-modal')">&#215;</button>
        <h2 id="module-modal-title">Add Module</h2>
        <form id="module-form" onsubmit="saveModule(event)">
            <input type="hidden" id="module-id">
            <div class="form-group">
                <label class="form-label">Module Title</label>
                <input type="text" id="module-title-input" class="form-control" placeholder="e.g. Module 1: Foundations" required>
            </div>
            <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" onclick="closeModal('module-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Week — no quiz fields, just title --}}
<div class="modal-overlay" id="week-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('week-modal')">&#215;</button>
        <h2>Add Week</h2>
        <p class="text-muted text-small" id="week-modal-subtitle" style="margin-bottom:1rem;"></p>
        <form id="week-form" onsubmit="saveWeek(event)">
            <input type="hidden" id="week-module-id">
            <div class="form-group">
                <label class="form-label">Week Title</label>
                <input type="text" id="week-title-input" class="form-control" placeholder="e.g. Introduction to the topic" required>
            </div>
            <p class="text-muted text-small" style="margin-bottom:1rem;">You can add a quiz to this week after creating it.</p>
            <div style="display:flex;gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Add Week</button>
                <button type="button" onclick="closeModal('week-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Add/Edit Weekly Quiz --}}
<div class="modal-overlay" id="quiz-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('quiz-modal')">&#215;</button>
        <h2 id="quiz-modal-title">Add Weekly Quiz</h2>
        <p class="text-muted text-small" id="quiz-modal-subtitle" style="margin-bottom:0.5rem;"></p>
        <p class="text-small" style="background:#eff6ff;color:#1d4ed8;border-radius:6px;padding:0.6rem 0.9rem;margin-bottom:1.25rem;line-height:1.5;">
            Learners must answer <strong>all questions correctly (100%)</strong> to pass and progress.
        </p>
        <form id="quiz-form" onsubmit="saveQuiz(event)">
            <input type="hidden" id="quiz-week-id">
            <input type="hidden" id="quiz-assessment-id">
            <div class="form-group">
                <label class="form-label">Quiz Title</label>
                <input type="text" id="quiz-title" class="form-control" placeholder="e.g. Week 1 Quiz" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Time Limit (minutes)</label>
                    <input type="number" id="quiz-time" class="form-control" min="1" placeholder="Leave blank — no limit">
                </div>
                <div style="display:flex;align-items:flex-end;padding-bottom:1.25rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer;">
                        <input type="checkbox" id="quiz-randomize" style="width:16px;height:16px;">
                        Randomise questions
                    </label>
                </div>
            </div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary" id="quiz-save-btn">Save Quiz</button>
                <a id="quiz-questions-link" href="#" class="btn btn-outline" style="display:none;">Manage Questions</a>
                <button type="button" onclick="closeModal('quiz-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
        <div id="quiz-success" style="display:none;text-align:center;padding:1rem 0 0.5rem;">
            <div style="width:44px;height:44px;border-radius:50%;background:#f0fdf4;border:2px solid #bbf7d0;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;font-size:1.25rem;">✓</div>
            <div style="font-weight:600;margin-bottom:0.35rem;">Quiz saved</div>
            <p class="text-muted text-small" style="margin-bottom:1.25rem;">Now add questions so learners can be assessed.</p>
            <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
                <a id="quiz-go-questions-link" href="#" class="btn btn-primary">Add Questions →</a>
                <button onclick="closeModal('quiz-modal');location.reload();" class="btn btn-ghost">Done</button>
            </div>
        </div>
    </div>
</div>

{{-- Add Content --}}
<div class="modal-overlay" id="content-modal">
    <div class="modal" style="max-width:580px;">
        <button class="modal-close" onclick="closeModal('content-modal')">&#215;</button>
        <h2>Add Content</h2>
        <p class="text-muted text-small" id="content-modal-subtitle" style="margin-bottom:1rem;"></p>
        <form id="content-form" onsubmit="saveContent(event)">
            <input type="hidden" id="content-week-id">
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
                <div class="form-group"><label class="form-label">Video URL</label><input type="url" id="content-video-url" class="form-control" placeholder="https://youtube.com/..."></div>
                <div class="form-group"><label class="form-label">Duration (minutes)</label><input type="number" id="content-video-duration" class="form-control" min="1" placeholder="e.g. 12"></div>
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
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;">
                <input type="checkbox" id="content-required" checked style="width:16px;height:16px;">
                <label for="content-required" class="form-label" style="margin:0;">Required to complete this week</label>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button type="submit" class="btn btn-primary" id="content-save-btn">Add Content</button>
                <button type="button" onclick="closeModal('content-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Final Exam modal — completely isolated, no shared state with quiz modal --}}
<div class="modal-overlay" id="final-exam-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('final-exam-modal')">&#215;</button>
        <h2 id="final-exam-modal-title">Set Up Final Examination</h2>
        <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:0.85rem 1rem;margin-bottom:1.25rem;font-size:0.825rem;color:#5b21b6;line-height:1.6;">
            Pass mark fixed at <strong>75%</strong>. Failed attempts lock for <strong>48 hours</strong>. Score only shown — no question review.
        </div>
        <form id="final-exam-form" onsubmit="saveFinalExam(event)">
            <input type="hidden" id="fe-assessment-id">
            <div class="form-group">
                <label class="form-label">Exam Title</label>
                <input type="text" id="fe-title" class="form-control" placeholder="e.g. Final Examination" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Time Limit (minutes)</label>
                    <input type="number" id="fe-time" class="form-control" min="1" placeholder="e.g. 90">
                </div>
                <div style="display:flex;align-items:flex-end;padding-bottom:1.25rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer;">
                        <input type="checkbox" id="fe-randomize" style="width:16px;height:16px;">
                        Randomise questions
                    </label>
                </div>
            </div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary" id="fe-save-btn">Save Examination</button>
                <a id="fe-questions-link" href="#" class="btn btn-outline" style="display:none;">Manage Questions</a>
                <button type="button" onclick="closeModal('final-exam-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
        <div id="fe-success" style="display:none;text-align:center;padding:1rem 0 0.5rem;">
            <div style="width:44px;height:44px;border-radius:50%;background:#f5f3ff;border:2px solid #ddd6fe;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;font-size:1.25rem;">🎓</div>
            <div style="font-weight:600;margin-bottom:0.35rem;">Final exam saved</div>
            <p class="text-muted text-small" style="margin-bottom:1.25rem;">Add questions to make the exam available to learners.</p>
            <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
                <a id="fe-go-questions-link" href="#" class="btn btn-primary">Add Questions →</a>
                <button onclick="closeModal('final-exam-modal');location.reload();" class="btn btn-ghost">Done</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const PROGRAM_ID = {{ $program->id }};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Ordered week IDs per module for reorder — built from DOM
function getModuleWeekIds(moduleId) {
    const rows = document.querySelectorAll(`[data-module-id="${moduleId}"] [id^="week-"]`);
    return [...rows].map(r => parseInt(r.id.replace('week-','')));
}

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    // Reset success panels
    ['quiz','fe'].forEach(p => {
        const s = document.getElementById(p+'-success');
        const f = document.getElementById(p+(p==='fe'?'-form':'-form'));
        if (s) s.style.display = 'none';
        if (f) f.style.display = 'block';
    });
}
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) closeModal(el.id); }));

// ── Modules ───────────────────────────────────────────────────────────────────
function openAddModule()      { document.getElementById('module-modal-title').textContent='Add Module'; document.getElementById('module-id').value=''; document.getElementById('module-title-input').value=''; openModal('module-modal'); }
function openEditModule(id,t) { document.getElementById('module-modal-title').textContent='Edit Module'; document.getElementById('module-id').value=id; document.getElementById('module-title-input').value=t; openModal('module-modal'); }

async function saveModule(e) {
    e.preventDefault();
    const id=document.getElementById('module-id').value, title=document.getElementById('module-title-input').value.trim();
    if(id) await api('PUT',`/mentor/programs/${PROGRAM_ID}/modules/${id}`,{title});
    else   await api('POST',`/mentor/programs/${PROGRAM_ID}/modules`,{title});
    closeModal('module-modal'); location.reload();
}
async function deleteModule(id,title) {
    if(!confirm(`Remove module "${title}" and all its weeks?`)) return;
    await api('DELETE',`/mentor/programs/${PROGRAM_ID}/modules/${id}`); location.reload();
}

// ── Weeks — no quiz on creation ───────────────────────────────────────────────
function openAddWeek(moduleId, moduleTitle) {
    document.getElementById('week-module-id').value = moduleId;
    document.getElementById('week-modal-subtitle').textContent = `Adding to: ${moduleTitle}`;
    document.getElementById('week-title-input').value = '';
    openModal('week-modal');
}

async function saveWeek(e) {
    e.preventDefault();
    const moduleId = document.getElementById('week-module-id').value;
    await api('POST',`/mentor/programs/${PROGRAM_ID}/modules/${moduleId}/weeks`, {
        title: document.getElementById('week-title-input').value,
    });
    closeModal('week-modal'); location.reload();
}

async function deleteWeek(id, title) {
    if(!confirm(`Remove week "${title}"?`)) return;
    await api('DELETE',`/mentor/programs/${PROGRAM_ID}/weeks/${id}`); location.reload();
}

// ── Week reorder (up/down) ────────────────────────────────────────────────────
async function moveWeek(weekId, direction) {
    // Collect all non-final week IDs in DOM order across all modules
    const allRows = [...document.querySelectorAll('[id^="week-"]')];
    const allIds  = allRows.map(r => parseInt(r.id.replace('week-','')));
    const idx     = allIds.indexOf(weekId);
    if (direction === 'up'   && idx === 0)               return;
    if (direction === 'down' && idx === allIds.length-1) return;

    const swapIdx = direction === 'up' ? idx-1 : idx+1;
    [allIds[idx], allIds[swapIdx]] = [allIds[swapIdx], allIds[idx]];

    await api('POST',`/mentor/programs/${PROGRAM_ID}/weeks/reorder`,{order: allIds});
    location.reload();
}

// ── Weekly quiz ───────────────────────────────────────────────────────────────
function openAddQuiz(weekId, weekTitle) {
    document.getElementById('quiz-modal-title').textContent = 'Add Weekly Quiz';
    document.getElementById('quiz-modal-subtitle').textContent = weekTitle;
    document.getElementById('quiz-week-id').value = weekId;
    document.getElementById('quiz-assessment-id').value = '';
    document.getElementById('quiz-title').value = '';
    document.getElementById('quiz-time').value = '';
    document.getElementById('quiz-randomize').checked = false;
    document.getElementById('quiz-questions-link').style.display = 'none';
    document.getElementById('quiz-form').style.display = 'block';
    document.getElementById('quiz-success').style.display = 'none';
    openModal('quiz-modal');
}

function openEditQuiz(weekId, assessmentId, weekTitle, data) {
    document.getElementById('quiz-modal-title').textContent = 'Edit Weekly Quiz';
    document.getElementById('quiz-modal-subtitle').textContent = weekTitle;
    document.getElementById('quiz-week-id').value = weekId;
    document.getElementById('quiz-assessment-id').value = assessmentId;
    document.getElementById('quiz-title').value = data.title || '';
    document.getElementById('quiz-time').value  = data.time_limit_minutes || '';
    document.getElementById('quiz-randomize').checked = !!data.randomize_questions;
    document.getElementById('quiz-form').style.display = 'block';
    document.getElementById('quiz-success').style.display = 'none';
    const qLink = document.getElementById('quiz-questions-link');
    qLink.href = `/mentor/programs/${PROGRAM_ID}/assessments/${assessmentId}/questions`;
    qLink.textContent = data.questions_count > 0 ? `Manage Questions (${data.questions_count})` : 'Add Questions';
    qLink.style.display = 'inline-flex';
    openModal('quiz-modal');
}

async function saveQuiz(e) {
    e.preventDefault();
    const weekId = document.getElementById('quiz-week-id').value;
    const assId  = document.getElementById('quiz-assessment-id').value;
    const body   = {
        title:               document.getElementById('quiz-title').value,
        time_limit_minutes:  document.getElementById('quiz-time').value || null,
        randomize_questions: document.getElementById('quiz-randomize').checked ? 1 : 0,
    };
    let data;
    if (assId) {
        await api('PUT',`/mentor/programs/${PROGRAM_ID}/assessments/${assId}`,body);
        data = { assessment: { id: assId } };
    } else {
        data = await api('POST',`/mentor/programs/${PROGRAM_ID}/weeks/${weekId}/assessment`,body);
    }
    const resolvedId = data?.assessment?.id || assId;
    const qUrl = `/mentor/programs/${PROGRAM_ID}/assessments/${resolvedId}/questions`;
    document.getElementById('quiz-go-questions-link').href = qUrl;
    document.getElementById('quiz-questions-link').href    = qUrl;
    document.getElementById('quiz-questions-link').style.display = 'inline-flex';
    document.getElementById('quiz-form').style.display    = 'none';
    document.getElementById('quiz-success').style.display = 'block';
}

// ── Content ───────────────────────────────────────────────────────────────────
function openAddContent(weekId, weekTitle) {
    document.getElementById('content-week-id').value = weekId;
    document.getElementById('content-modal-subtitle').textContent = weekTitle;
    document.getElementById('content-form').reset();
    document.getElementById('content-save-btn').disabled = false;
    document.getElementById('content-save-btn').textContent = 'Add Content';
    onContentTypeChange('');
    openModal('content-modal');
}
function onContentTypeChange(type) {
    ['video','pdf','link','article'].forEach(t => { document.getElementById(`field-${t}`).style.display = t===type?'block':'none'; });
}
async function saveContent(e) {
    e.preventDefault();
    const btn = document.getElementById('content-save-btn');
    btn.disabled = true; btn.textContent = 'Saving…';
    const weekId = document.getElementById('content-week-id').value;
    const type   = document.getElementById('content-type').value;
    const form   = new FormData();
    form.append('title', document.getElementById('content-title').value);
    form.append('content_type', type);
    form.append('is_required', document.getElementById('content-required').checked ? 1 : 0);
    if (type==='video')   { form.append('video_url', document.getElementById('content-video-url').value); form.append('video_duration_minutes', document.getElementById('content-video-duration').value); }
    else if (type==='pdf'){ const f=document.getElementById('content-file').files[0]; if(f) form.append('file',f); }
    else if (type==='link')    form.append('external_url', document.getElementById('content-ext-url').value);
    else if (type==='article') form.append('text_content',  document.getElementById('content-text').value);
    const res  = await fetch(`/mentor/programs/${PROGRAM_ID}/weeks/${weekId}/contents`,{method:'POST',body:form,headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}});
    const data = await res.json();
    if (data.success) { closeModal('content-modal'); location.reload(); }
    else { alert('Error saving content.'); btn.disabled=false; btn.textContent='Add Content'; }
}
async function deleteContent(id, title) {
    if(!confirm(`Remove "${title}"?`)) return;
    await api('DELETE',`/mentor/programs/${PROGRAM_ID}/contents/${id}`); location.reload();
}

// ── Final exam — completely isolated fields (fe- prefix) ──────────────────────
function openAddFinalExam() {
    document.getElementById('final-exam-modal-title').textContent = 'Set Up Final Examination';
    document.getElementById('fe-assessment-id').value = '';
    document.getElementById('fe-title').value = '';
    document.getElementById('fe-time').value  = '';
    document.getElementById('fe-randomize').checked = false;
    document.getElementById('fe-questions-link').style.display = 'none';
    document.getElementById('final-exam-form').style.display = 'block';
    document.getElementById('fe-success').style.display = 'none';
    openModal('final-exam-modal');
}

function openEditFinalExam() {
    @if($finalExam)
    document.getElementById('final-exam-modal-title').textContent = 'Edit Final Examination';
    document.getElementById('fe-assessment-id').value = {{ $finalExam->id }};
    document.getElementById('fe-title').value = @json($finalExam->title);
    document.getElementById('fe-time').value  = '{{ $finalExam->time_limit_minutes ?? '' }}';
    document.getElementById('fe-randomize').checked = {{ $finalExam->randomize_questions ? 'true' : 'false' }};
    document.getElementById('final-exam-form').style.display = 'block';
    document.getElementById('fe-success').style.display = 'none';
    const qLink = document.getElementById('fe-questions-link');
    qLink.href = `/mentor/programs/${PROGRAM_ID}/assessments/{{ $finalExam->id }}/questions`;
    qLink.textContent = '{{ $finalExam->questions->count() > 0 ? "Manage Questions (".$finalExam->questions->count().")" : "Add Questions" }}';
    qLink.style.display = 'inline-flex';
    openModal('final-exam-modal');
    @endif
}

async function saveFinalExam(e) {
    e.preventDefault();
    const examId = document.getElementById('fe-assessment-id').value;
    const body = {
        title:               document.getElementById('fe-title').value,
        time_limit_minutes:  document.getElementById('fe-time').value || null,
        randomize_questions: document.getElementById('fe-randomize').checked ? 1 : 0,
        pass_percentage:     75,
        assessment_id:       examId || null,
    };
    const data = await api('POST',`/mentor/programs/${PROGRAM_ID}/final-exam`, body);
    const resolvedId = data?.assessment?.id || examId;
    const qUrl = `/mentor/programs/${PROGRAM_ID}/assessments/${resolvedId}/questions`;
    document.getElementById('fe-go-questions-link').href = qUrl;
    document.getElementById('fe-questions-link').href    = qUrl;
    document.getElementById('fe-questions-link').style.display = 'inline-flex';
    document.getElementById('final-exam-form').style.display = 'none';
    document.getElementById('fe-success').style.display = 'block';
}

async function deleteFinalExam(id) {
    if(!confirm('Remove the final examination and all its questions?')) return;
    await api('DELETE',`/mentor/programs/${PROGRAM_ID}/assessments/${id}`); location.reload();
}

// ── Submit for review ─────────────────────────────────────────────────────────
async function submitForReview() {
    if(!confirm("Submit for admin review? You won't be able to edit until they respond.")) return;
    const res  = await fetch(`/mentor/programs/${PROGRAM_ID}/submit`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}});
    const data = await res.json().catch(()=>({}));
    if(res.ok) location.reload(); else alert(data.message||'Could not submit.');
}

// ── API helper — _method via query string ─────────────────────────────────────
async function api(method, url, body = null) {
    const needsSpoof = method === 'DELETE' || method === 'PUT';
    const fetchUrl   = needsSpoof ? `${url}?_method=${method}` : url;
    const opts = {
        method: needsSpoof ? 'POST' : method,
        headers: {'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    };
    if (body || needsSpoof) opts.body = JSON.stringify(body || {});
    const res  = await fetch(fetchUrl, opts);
    const data = await res.json().catch(()=>({}));
    if (!res.ok) { alert(data.message || 'An error occurred.'); throw new Error(); }
    return data;
}
</script>
@endpush