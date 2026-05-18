@extends('layouts.admin')
@section('title', 'Review: ' . $program->name)

@section('content')

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.programs.index') }}">Programs</a></div>
        <h1>{{ $program->name }}</h1>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
        <span class="badge {{ match($program->status) {
            'active'       => 'badge-green',
            'under_review' => 'badge-yellow',
            'inactive'     => 'badge-gray',
            default        => 'badge-gray',
        } }}" style="padding:.35rem .9rem;font-size:.8rem;">
            {{ match($program->status) {
                'active'       => 'Live',
                'under_review' => 'Under Review',
                'inactive'     => 'Offline',
                default        => 'Draft',
            } }}
        </span>
        @if($program->status === 'under_review')
            <button onclick="openModal('publish-modal')" class="btn btn-primary btn-sm">Publish</button>
            <button onclick="openModal('reject-modal')" class="btn btn-sm"
                    style="background:#fef2f2;color:var(--error);border:1px solid #fca5a5;">Return to Mentor</button>
        @elseif($program->status === 'active')
            <button onclick="openModal('offline-modal')" class="btn btn-ghost btn-sm">Take Offline</button>
        @elseif($program->status === 'inactive')
            <form method="POST" action="{{ route('admin.programs.restore', $program) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">Restore to Live</button>
            </form>
        @endif
        @if(!$program->enrollments()->exists())
        <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" style="display:inline;"
              onsubmit="return confirm('Permanently delete this program?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--error);">Delete</button>
        </form>
        @endif
    </div>
</div>

{{-- Stats strip --}}
<div style="background:var(--white);border-bottom:1px solid var(--border);padding:0 2rem;">
    <div style="max-width:1200px;margin:0 auto;display:flex;gap:2.5rem;padding:.85rem 0;flex-wrap:wrap;">
        @foreach([
            'modules'     => 'Modules',
            'weeks'       => 'Weeks',
            'contents'    => 'Content Items',
            'assessments' => 'Weekly Quizzes',
            'questions'   => 'Questions',
            'enrolled'    => 'Learners',
        ] as $key => $label)
        <div style="text-align:center;min-width:56px;">
            <div style="font-weight:600;font-size:1.05rem;color:{{ $stats[$key] == 0 ? 'var(--muted)' : 'var(--text)' }};">
                {{ $stats[$key] }}
            </div>
            <div class="text-muted text-small">{{ $label }}</div>
        </div>
        @endforeach
        {{-- Final exam indicator --}}
        @php $hasFinalExam = $program->modules->flatMap->weeks->contains(fn($w) => $w->assessment?->is_final); @endphp
        <div style="text-align:center;min-width:56px;">
            <div style="font-weight:600;font-size:1.05rem;color:{{ $hasFinalExam ? '#7c3aed' : 'var(--muted)' }};">
                {{ $hasFinalExam ? '✓' : '—' }}
            </div>
            <div class="text-muted text-small">Final Exam</div>
        </div>
    </div>
</div>

@if($program->review_notes && $program->reviewed_at)
<div style="padding:.75rem 2rem;background:#fffbeb;border-bottom:1px solid #fde68a;font-size:.875rem;color:#92400e;">
    <strong>Previous feedback</strong> ({{ $program->reviewed_at->format('M j, Y') }}): {{ $program->review_notes }}
</div>
@endif

<div class="container section">
<div style="display:grid;grid-template-columns:1fr 300px;gap:2rem;align-items:start;">

    {{-- ══ LEFT: Curriculum ══ --}}
    <div>

        {{-- Course modules and weeks --}}
        <h2 style="font-family:'Source Serif 4',serif;font-size:1.05rem;margin-bottom:1rem;">Curriculum</h2>

        @forelse($program->modules as $moduleIndex => $module)
        <div class="card" style="margin-bottom:1.25rem;">

            <div style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:var(--bg);">
                <div>
                    <span class="text-muted text-small" style="text-transform:uppercase;letter-spacing:.04em;font-size:.7rem;">
                        Module {{ $moduleIndex + 1 }}
                    </span>
                    <div style="font-weight:600;margin-top:.1rem;">{{ $module->title }}</div>
                </div>
                <span class="text-muted text-small">{{ $module->weeks->count() }} week{{ $module->weeks->count() !== 1 ? 's' : '' }}</span>
            </div>

            {{-- Only show non-final-exam weeks here --}}
            @foreach($module->weeks->filter(fn($w) => !($w->assessment?->is_final)) as $week)
            <div style="border-bottom:1px solid var(--border);">

                <div style="padding:.8rem 1.25rem .8rem 2rem;display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;">
                    <div>
                        <span style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Week {{ $week->week_number }}</span>
                        <div style="font-weight:500;font-size:.9rem;margin-top:.1rem;">{{ $week->title }}</div>
                    </div>
                    <div style="display:flex;gap:.5rem;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end;">
                        <span class="text-muted text-small">{{ $week->contents->count() }} item{{ $week->contents->count() !== 1 ? 's' : '' }}</span>
                        @if($week->has_assessment && $week->assessment)
                        @php $qCount = $week->assessment->questions->count(); @endphp
                        <span class="badge badge-blue" style="font-size:.7rem;">
                            ✓ Quiz · {{ $qCount }}Q · 100% required
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Content items --}}
                @foreach($week->contents as $content)
                <div style="padding:.55rem 1.25rem .55rem 3rem;border-top:1px solid var(--border);display:flex;align-items:flex-start;gap:.75rem;">
                    <span style="flex-shrink:0;font-size:.65rem;text-transform:uppercase;font-weight:600;color:#fff;border-radius:3px;padding:.15rem .45rem;margin-top:.1rem;
                                 background:{{ match($content->content_type) {
                                     'video'   => '#6366f1',
                                     'pdf'     => '#ef4444',
                                     'link'    => '#0891b2',
                                     'article' => '#059669',
                                     default   => '#6b7280',
                                 } }};">
                        {{ $content->content_type }}
                    </span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.875rem;font-weight:500;display:flex;align-items:center;gap:.5rem;">
                            {{ $content->title }}
                            @if(!$content->is_required)<span class="text-muted text-small">(optional)</span>@endif
                        </div>
                        @if($content->content_type === 'video' && $content->video_url)
                        <div class="text-muted text-small" style="margin-top:.2rem;display:flex;gap:.75rem;flex-wrap:wrap;">
                            <a href="{{ $content->video_url }}" target="_blank" rel="noopener" style="color:var(--blue);word-break:break-all;">
                                {{ Str::limit($content->video_url, 70) }} ↗
                            </a>
                            @if($content->video_duration_minutes)<span>{{ $content->video_duration_minutes }} min</span>@endif
                        </div>
                        @elseif($content->content_type === 'pdf' && $content->file_path)
                        <div class="text-muted text-small" style="margin-top:.2rem;">
                            <a href="{{ asset('storage/'.$content->file_path) }}" target="_blank" style="color:var(--blue);">View PDF ↗</a>
                        </div>
                        @elseif($content->content_type === 'link' && $content->external_url)
                        <div class="text-muted text-small" style="margin-top:.2rem;">
                            <a href="{{ $content->external_url }}" target="_blank" rel="noopener" style="color:var(--blue);word-break:break-all;">
                                {{ Str::limit($content->external_url, 70) }} ↗
                            </a>
                        </div>
                        @elseif($content->content_type === 'article' && $content->text_content)
                        <details style="margin-top:.35rem;">
                            <summary style="font-size:.78rem;color:var(--blue);cursor:pointer;list-style:none;">Read preview ▾</summary>
                            <div style="margin-top:.5rem;padding:.75rem;background:var(--bg);border-radius:4px;font-size:.82rem;line-height:1.65;color:var(--text);white-space:pre-wrap;max-height:240px;overflow-y:auto;border:1px solid var(--border);">{{ $content->text_content }}</div>
                        </details>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Weekly quiz questions --}}
                @if($week->has_assessment && $week->assessment && $week->assessment->questions->isNotEmpty())
                <div style="border-top:2px solid var(--blue-light);background:#f8faff;">
                    <div style="padding:.75rem 1.25rem .75rem 3rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
                        <div>
                            <span class="badge badge-blue" style="margin-right:.5rem;">Quiz</span>
                            <strong style="font-size:.875rem;">{{ $week->assessment->title }}</strong>
                        </div>
                        <div style="font-size:.78rem;color:var(--muted);">
                            Pass: 100% (all correct)
                            @if($week->assessment->time_limit_minutes) · {{ $week->assessment->time_limit_minutes }} min @endif
                            · {{ $week->assessment->questions->count() }}Q
                            @if($week->assessment->randomize_questions) · Randomised @endif
                        </div>
                    </div>
                    <div style="padding:0 1.25rem .75rem 3rem;">
                        <details>
                            <summary style="font-size:.78rem;color:var(--blue);cursor:pointer;list-style:none;padding:.35rem 0;user-select:none;">
                                Show questions ▾
                            </summary>
                            <div style="display:grid;gap:.6rem;margin-top:.5rem;">
                                @foreach($week->assessment->questions as $qi => $question)
                                @php
                                    $options  = is_array($question->options)        ? $question->options        : json_decode($question->options, true)        ?? [];
                                    $correct  = is_array($question->correct_answer) ? $question->correct_answer : json_decode($question->correct_answer, true) ?? [];
                                @endphp
                                <div style="border:1px solid var(--border);border-radius:6px;overflow:hidden;background:var(--white);">
                                    <div style="padding:.6rem 1rem;display:flex;align-items:flex-start;gap:.6rem;border-bottom:1px solid var(--border);">
                                        <span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;background:var(--blue);color:#fff;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ $qi+1 }}</span>
                                        <div>
                                            <div style="font-size:.875rem;font-weight:500;line-height:1.5;">{{ $question->question_text }}</div>
                                            <div style="display:flex;gap:.4rem;margin-top:.3rem;flex-wrap:wrap;">
                                                <span class="badge badge-gray" style="font-size:.65rem;">{{ str_replace('_',' ',$question->question_type) }}</span>
                                                <span class="text-muted" style="font-size:.72rem;">{{ $question->points }} pt{{ $question->points !== 1 ? 's' : '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="padding:.6rem 1rem;display:grid;gap:.3rem;">
                                        @foreach($options as $opt)
                                        @php $isCorrect = in_array($opt, $correct); @endphp
                                        <div style="display:flex;align-items:center;gap:.5rem;padding:.3rem .6rem;border-radius:4px;font-size:.82rem;
                                                    background:{{ $isCorrect ? '#f0fdf4' : 'transparent' }};
                                                    border:1px solid {{ $isCorrect ? '#bbf7d0' : 'transparent' }};
                                                    color:{{ $isCorrect ? '#166534' : 'var(--text)' }};">
                                            <span style="flex-shrink:0;width:14px;height:14px;border-radius:50%;border:1.5px solid {{ $isCorrect ? '#16a34a' : 'var(--border)' }};background:{{ $isCorrect ? '#16a34a' : 'transparent' }};display:flex;align-items:center;justify-content:center;">
                                                @if($isCorrect)<svg width="8" height="8" viewBox="0 0 9 9" fill="none"><path d="M1.5 4.5l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>@endif
                                            </span>
                                            {{ $opt }}
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </details>
                    </div>
                </div>
                @endif

            </div>
            @endforeach

        </div>
        @empty
        <div class="card card-body" style="color:var(--muted);text-align:center;padding:3rem;">No curriculum content yet.</div>
        @endforelse

        {{-- Final examination (separate from curriculum) --}}
        @if($hasFinalExam)
        @php $finalExam = $program->modules->flatMap->weeks->map->assessment->firstWhere('is_final', true); @endphp
        <div style="margin-top:2rem;">
            <h2 style="font-family:'Source Serif 4',serif;font-size:1.05rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
                <span style="display:inline-flex;align-items:center;gap:4px;background:#7c3aed;color:#fff;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:2px 8px;border-radius:99px;">🎓 Final Exam</span>
                {{ $finalExam->title }}
            </h2>
            <div class="card">
                <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--border);display:flex;gap:2rem;font-size:.82rem;color:var(--muted);flex-wrap:wrap;">
                    <span>Pass mark: <strong style="color:var(--text);">{{ $finalExam->pass_percentage }}%</strong></span>
                    <span>Time limit: <strong style="color:var(--text);">{{ $finalExam->time_limit_minutes ? $finalExam->time_limit_minutes.' min' : 'None' }}</strong></span>
                    <span>Questions: <strong style="color:var(--text);">{{ $finalExam->questions->count() }}</strong></span>
                    <span>Cooldown on fail: <strong style="color:var(--text);">48 hours</strong></span>
                    @if($finalExam->randomize_questions)<span style="color:#7c3aed;font-weight:600;">Randomised</span>@endif
                </div>
                @if($finalExam->questions->isNotEmpty())
                <div style="padding:.75rem 1.25rem;">
                    <details>
                        <summary style="font-size:.78rem;color:var(--blue);cursor:pointer;list-style:none;padding:.35rem 0;">Show questions ▾</summary>
                        <div style="display:grid;gap:.6rem;margin-top:.5rem;">
                            @foreach($finalExam->questions as $qi => $question)
                            @php
                                $options = is_array($question->options)        ? $question->options        : json_decode($question->options, true)        ?? [];
                                $correct = is_array($question->correct_answer) ? $question->correct_answer : json_decode($question->correct_answer, true) ?? [];
                            @endphp
                            <div style="border:1px solid #ddd6fe;border-radius:6px;overflow:hidden;background:#faf5ff;">
                                <div style="padding:.6rem 1rem;border-bottom:1px solid #ddd6fe;display:flex;align-items:flex-start;gap:.6rem;">
                                    <span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;background:#7c3aed;color:#fff;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ $qi+1 }}</span>
                                    <div style="font-size:.875rem;font-weight:500;line-height:1.5;">{{ $question->question_text }}</div>
                                </div>
                                <div style="padding:.6rem 1rem;display:grid;gap:.3rem;">
                                    @foreach($options as $opt)
                                    @php $isCorrect = in_array($opt, $correct); @endphp
                                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;padding:.3rem .6rem;border-radius:4px;
                                                background:{{ $isCorrect ? '#f5f3ff' : 'transparent' }};
                                                color:{{ $isCorrect ? '#5b21b6' : 'var(--text)' }};font-weight:{{ $isCorrect ? '600' : '400' }};">
                                        <span>{{ $isCorrect ? '✓' : '·' }}</span> {{ $opt }}
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </details>
                </div>
                @else
                <div style="padding:1rem 1.25rem;font-size:.82rem;color:var(--warning);">⚠ No questions added yet.</div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- ══ RIGHT: Details & actions ══ --}}
    <div style="position:sticky;top:calc(var(--nav-h) + 1.5rem);">

        @if($program->cover_image)
        <div style="border-radius:8px;overflow:hidden;margin-bottom:1rem;border:1px solid var(--border);">
            <img src="{{ asset('storage/'.$program->cover_image) }}" style="width:100%;display:block;" alt="">
        </div>
        @endif

        <div class="card card-body" style="margin-bottom:1rem;">
            <div style="font-weight:600;margin-bottom:.75rem;font-family:'Source Serif 4',serif;">Program Details</div>
            <div style="display:grid;gap:.55rem;font-size:.875rem;">
                @foreach([
                    'Mentor'   => ($program->mentor?->first_name.' '.$program->mentor?->last_name) ?? '—',
                    'Duration' => $program->duration,
                    'Price'    => '₦'.number_format($program->price, 2),
                    'Discount' => $program->discount_percentage > 0 ? $program->discount_percentage.'%' : null,
                    'Submitted'=> $program->submitted_at?->format('M j, Y') ?? '—',
                    'Learners' => $stats['enrolled'],
                ] as $label => $value)
                @if($value !== null)
                <div style="display:flex;justify-content:space-between;gap:.5rem;">
                    <span class="text-muted">{{ $label }}</span>
                    <span>{{ $value }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>

        <div class="card card-body" style="margin-bottom:1rem;">
            <div style="font-weight:600;margin-bottom:.5rem;font-family:'Source Serif 4',serif;">Description</div>
            <p style="font-size:.875rem;color:var(--muted);line-height:1.65;white-space:pre-wrap;">{{ $program->description }}</p>
        </div>

        @if($program->review_notes)
        <div class="card card-body" style="margin-bottom:1rem;background:#fffbeb;border-color:#fde68a;">
            <div style="font-size:.72rem;font-weight:700;color:#92400e;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.03em;">Previous Feedback</div>
            <p style="font-size:.875rem;color:#92400e;line-height:1.55;">{{ $program->review_notes }}</p>
            @if($program->reviewed_at)
            <p style="font-size:.75rem;color:#b45309;margin-top:.4rem;">{{ $program->reviewed_at->format('M j, Y') }}</p>
            @endif
        </div>
        @endif

        @if($program->status === 'under_review')
        <div class="card card-body" style="border-color:var(--blue);background:var(--blue-light);">
            <div style="font-weight:600;font-size:.875rem;margin-bottom:.75rem;color:var(--blue);">Review Decision</div>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                <button onclick="openModal('publish-modal')" class="btn btn-primary" style="justify-content:center;">Publish Program</button>
                <button onclick="openModal('reject-modal')" class="btn" style="justify-content:center;background:#fef2f2;color:var(--error);border-color:#fca5a5;">Return to Mentor</button>
            </div>
        </div>
        @elseif($program->status === 'active')
        <div class="card card-body">
            <div style="font-weight:600;font-size:.875rem;margin-bottom:.75rem;">Actions</div>
            <button onclick="openModal('offline-modal')" class="btn btn-ghost" style="width:100%;justify-content:center;">Take Offline</button>
        </div>
        @elseif($program->status === 'inactive')
        <div class="card card-body">
            <div style="font-weight:600;font-size:.875rem;margin-bottom:.75rem;">Actions</div>
            <form method="POST" action="{{ route('admin.programs.restore', $program) }}">
                @csrf
                <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center;">Restore to Live</button>
            </form>
        </div>
        @endif

    </div>

</div>
</div>

{{-- Publish modal --}}
<div class="modal-overlay" id="publish-modal">
    <div class="modal" style="max-width:460px;">
        <button class="modal-close" onclick="closeModal('publish-modal')">&#215;</button>
        <h2>Publish Program</h2>
        <p style="color:var(--muted);font-size:.875rem;margin-bottom:1.25rem;">
            The program will be listed on the Explore page and open for enrolments.
        </p>
        <form method="POST" action="{{ route('admin.programs.publish', $program) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Note to Mentor <span class="text-muted text-small">(optional)</span></label>
                <textarea name="review_notes" class="form-control" rows="3" maxlength="500" placeholder="Any final feedback…"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn btn-primary">Publish</button>
                <button type="button" onclick="closeModal('publish-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Return to mentor modal --}}
<div class="modal-overlay" id="reject-modal">
    <div class="modal" style="max-width:460px;">
        <button class="modal-close" onclick="closeModal('reject-modal')">&#215;</button>
        <h2>Return to Mentor</h2>
        <p style="color:var(--muted);font-size:.875rem;margin-bottom:1.25rem;">
            The program returns to <strong>Draft</strong> so the mentor can revise and resubmit.
        </p>
        <form method="POST" action="{{ route('admin.programs.reject', $program) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Feedback for Mentor <span style="color:var(--error)">*</span></label>
                <textarea name="review_notes" class="form-control" rows="4" required maxlength="500"
                          placeholder="Describe what changes are needed…"></textarea>
                <div class="form-hint">Max 500 characters.</div>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn" style="background:var(--error);color:#fff;border-color:var(--error);">Return to Mentor</button>
                <button type="button" onclick="closeModal('reject-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Take offline modal --}}
<div class="modal-overlay" id="offline-modal">
    <div class="modal" style="max-width:460px;">
        <button class="modal-close" onclick="closeModal('offline-modal')">&#215;</button>
        <h2>Take Program Offline</h2>
        <p style="color:var(--muted);font-size:.875rem;margin-bottom:1.25rem;">
            Existing learners keep access. No new enrolments until restored.
        </p>
        <form method="POST" action="{{ route('admin.programs.take-offline', $program) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Reason <span class="text-muted text-small">(optional)</span></label>
                <textarea name="review_notes" class="form-control" rows="3" maxlength="500" placeholder="Internal note…"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn btn-primary">Take Offline</button>
                <button type="button" onclick="closeModal('offline-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); }));
</script>
@endpush