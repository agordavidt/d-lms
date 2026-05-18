@extends('layouts.learner')
@section('title', $enrollment->program->name . ' — ' . $week->title)

@section('content')

{{-- Pass server data to JS cleanly --}}
<script id="js-data" type="application/json">
    {!! json_encode([
        'enrollmentId'      => $enrollmentId,
        'weekId'            => $week->id,
        'hasAssessment'     => (bool) $week->assessment,
        'isFinal'           => (bool) ($week->assessment?->is_final),
        'assessmentId'      => $week->assessment?->id,
        'assessmentPassed'  => $assessmentPassed,
        'passMark'          => $week->assessment?->getEffectivePassPercentage() ?? 100,
        'contentIds'        => $contents->pluck('id'),
        'contentCompletion' => $contents->mapWithKeys(fn($c) => [
            $c->id => (bool)($c->contentProgress->first()?->is_completed ?? false)
        ])->all(),
    ]) !!}
</script>

<div class="learn-wrap">

    {{-- ══════ SIDEBAR ══════ --}}
    <aside class="learn-sidebar" id="learn-sidebar">
        <div class="sidebar-scroll">

            {{-- Program header --}}
            <div style="padding:1rem 1rem .75rem;border-bottom:1px solid #f3f4f6;">
                <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:.5rem;">
                    {{ $enrollment->program->name }}
                </p>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <div style="flex:1;height:5px;background:#e5e7eb;border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ $stats['overall_progress'] }}%;background:#2563eb;border-radius:3px;transition:width .5s;"></div>
                    </div>
                    <span class="prog-pill">{{ $stats['overall_progress'] }}%</span>
                </div>
                <p style="font-size:.7rem;color:#9ca3af;margin-top:.3rem;">
                    {{ $stats['completed_weeks'] }} / {{ $stats['total_weeks'] }} weeks
                </p>
            </div>

            {{-- Week list --}}
            @foreach($allWeekProgress as $wp)
            @php
                $wk        = $wp->moduleWeek;
                $isLocked  = !$wp->is_unlocked;
                $isDone    = $wp->is_completed;
                $isCurrent = $wk->id === $week->id;
                $isFinalWk = (bool)($wk->assessment?->is_final);
            @endphp

            @if($isLocked)
            <div class="week-nav-item locked">
            @else
            <a href="{{ route('learner.learning.week', [$enrollmentId, $wk->id]) }}"
               class="week-nav-item {{ $isCurrent ? 'active' : '' }}"
               style="{{ $isFinalWk ? 'border-left-color:#7c3aed;' : '' }}">
            @endif

                {{-- Status dot --}}
                @if($isLocked)
                <div style="width:16px;height:16px;border-radius:50%;border:1.5px solid #d1d5db;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </div>
                @elseif($isDone)
                <div style="width:16px;height:16px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                    <svg width="9" height="9" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                @else
                <div style="width:16px;height:16px;border-radius:50%;border:2px solid {{ $isCurrent ? ($isFinalWk ? '#7c3aed' : '#2563eb') : '#d1d5db' }};flex-shrink:0;margin-top:1px;"></div>
                @endif

                <div style="min-width:0;flex:1;">
                    <p style="font-size:.7rem;color:#9ca3af;margin-bottom:.1rem;">
                        {{ $wk->programModule->title ?? '' }} · Week {{ $wk->week_number }}
                        @if($isFinalWk)<span style="color:#7c3aed;font-weight:700;"> · FINAL</span>@endif
                    </p>
                    <p style="font-size:.82rem;font-weight:600;color:{{ $isCurrent ? ($isFinalWk ? '#5b21b6' : '#1d4ed8') : '#374151' }};line-height:1.35;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $wk->title }}
                    </p>
                </div>

            @if($isLocked)
            </div>
            @else
            </a>
            @endif

            {{-- Per-content dots for current week --}}
            @if($isCurrent)
            @php
                $sidebarContents = $wk->contents()
                    ->with(['contentProgress' => fn($q) =>
                        $q->where('user_id', auth()->id())->where('enrollment_id', $enrollmentId)
                    ])->orderBy('order')->get();
            @endphp

            @foreach($sidebarContents as $wc)
            @php $wcDone = (bool)($wc->contentProgress->first()?->is_completed ?? false); @endphp
            <div class="content-nav-item" id="sidebar-item-{{ $wc->id }}">
                <div class="content-nav-dot {{ $wcDone ? 'done' : '' }}" id="dot-{{ $wc->id }}">
                    @if($wcDone)
                    <svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;font-size:.75rem;">{{ $wc->title }}</span>
                <span style="font-size:.65rem;color:#d1d5db;text-transform:uppercase;flex-shrink:0;">{{ $wc->content_type }}</span>
            </div>
            @endforeach

            {{-- Assessment / Exam dot --}}
            @if($week->assessment)
            <div class="content-nav-item" id="sidebar-item-assessment"
                 style="{{ $isFinalWk ? 'background:#f5f3ff08;' : '' }}">
                <div class="content-nav-dot {{ $assessmentPassed ? 'done' : '' }}" id="dot-assessment"
                     style="{{ ($isFinalWk && !$assessmentPassed) ? 'border-color:#c4b5fd;' : '' }}">
                    @if($assessmentPassed)
                    <svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <span style="font-size:.75rem;{{ $isFinalWk ? 'color:#5b21b6;font-weight:600;' : '' }}">
                    {{ $isFinalWk ? 'Final Examination' : 'Weekly Quiz' }}
                </span>
                <span style="font-size:.65rem;color:#d1d5db;flex-shrink:0;">{{ $isFinalWk ? 'exam' : 'quiz' }}</span>
            </div>
            @endif

            @endif {{-- isCurrent --}}

            @endforeach
        </div>
    </aside>

    {{-- ══════ MAIN ══════ --}}
    <main class="learn-main" id="learn-main">

        {{-- Topbar --}}
        <div class="learn-topbar">
            <button onclick="toggleSidebar()"
                    style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;border:none;background:transparent;cursor:pointer;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            </button>
            <div style="flex:1;min-width:0;font-size:.85rem;color:#6b7280;overflow:hidden;">
                <span style="font-weight:600;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                    Week {{ $week->week_number }}: {{ $week->title }}
                </span>
            </div>
            <div style="flex-shrink:0;font-size:.78rem;color:#9ca3af;">
                <span id="topbar-done-count">0</span>/<span>{{ $contents->count() }}{{ $week->assessment ? ($week->assessment->is_final ? '+exam' : '+quiz') : '' }}</span>
            </div>
        </div>

        {{-- Week heading --}}
        <div style="background:#fff;padding:1.75rem 2.5rem 1.25rem;max-width:820px;border-bottom:1px solid #f3f4f6;">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:.4rem;">
                {{ $week->programModule->title ?? '' }} &nbsp;·&nbsp; Week {{ $week->week_number }}
            </p>
            <h1 style="font-size:1.45rem;font-weight:800;color:#111827;line-height:1.3;">{{ $week->title }}</h1>
        </div>

        {{-- ════ CONTENT SECTIONS ════ --}}
        @foreach($contents as $i => $content)
        @php $isDone = (bool)($content->contentProgress->first()?->is_completed ?? false); @endphp

        <section class="content-section"
                 id="section-{{ $content->id }}"
                 data-content-id="{{ $content->id }}"
                 data-type="{{ $content->content_type }}"
                 data-completed="{{ $isDone ? 'true' : 'false' }}"
                 @if($content->content_type === 'video')
                     data-video-url="{{ $content->video_url }}"
                     data-video-duration="{{ $content->video_duration_minutes }}"
                 @endif>

            <p class="content-type-label">
                {{ match($content->content_type) {
                    'video'   => 'Video lesson',
                    'article' => 'Reading',
                    'pdf'     => 'Document',
                    'link'    => 'Resource',
                    default   => ucfirst($content->content_type),
                } }}
                &nbsp;·&nbsp; {{ $i + 1 }} of {{ $contents->count() }}{{ $week->assessment ? ($week->assessment->is_final ? ' + final exam' : ' + quiz') : '' }}
            </p>
            <h2 class="content-title">{{ $content->title }}</h2>

            @if($content->content_type === 'video')
            <div class="video-container" id="player-wrap-{{ $content->id }}"></div>
            <p class="video-watch-note" id="video-note-{{ $content->id }}">
                {{ $isDone ? 'Watched ✓' : 'Watch to the end to mark this lesson complete.' }}
            </p>

            @elseif($content->content_type === 'article')
            <div class="article-body">{!! $content->text_content !!}</div>
            <div class="scroll-sentinel" data-content-id="{{ $content->id }}"></div>

            @elseif($content->content_type === 'pdf')
            <iframe class="pdf-frame" src="{{ $content->file_url }}" title="{{ $content->title }}"></iframe>
            <div style="margin-top:.75rem;">
                <a href="{{ $content->file_url }}" target="_blank" style="font-size:.8rem;color:#2563eb;text-decoration:none;">Open in new tab ↗</a>
            </div>
            <div class="scroll-sentinel" data-content-id="{{ $content->id }}"></div>

            @elseif($content->content_type === 'link')
            <a href="{{ $content->external_url }}" target="_blank" rel="noopener"
               class="ext-link-card" onclick="markContentDone({{ $content->id }})">
                <div style="width:40px;height:40px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:.9rem;font-weight:600;margin-bottom:.2rem;">{{ $content->title }}</p>
                    <p style="font-size:.75rem;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $content->external_url }}</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
            <div class="scroll-sentinel" data-content-id="{{ $content->id }}" style="margin-top:.5rem;"></div>
            @endif

            <div class="completion-indicator {{ $isDone ? 'done' : '' }}" id="ci-{{ $content->id }}">
                <div class="ci-dot"></div>
                <span id="ci-label-{{ $content->id }}">{{ $isDone ? 'Completed' : 'Not yet completed' }}</span>
            </div>
        </section>
        @endforeach

        {{-- ════ WEEKLY QUIZ ════ --}}
        @if($week->assessment && !$week->assessment->is_final)
        @php $assessment = $week->assessment; @endphp
        <section class="assessment-section" id="assessment-section">

            <div class="assessment-header">
                <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1;margin-bottom:.35rem;">Weekly Quiz</p>
                <h2 style="font-size:1.1rem;font-weight:700;color:#111827;margin-bottom:.6rem;">{{ $assessment->title }}</h2>
                <div style="display:flex;gap:1.5rem;font-size:.8rem;color:#6b7280;flex-wrap:wrap;">
                    <span>{{ $assessment->questions->count() }} question{{ $assessment->questions->count() !== 1 ? 's' : '' }}</span>
                    <span>Must answer all correctly to progress</span>
                    @if($assessment->time_limit_minutes)<span>Time limit: {{ $assessment->time_limit_minutes }} min</span>@endif
                </div>
            </div>

            {{-- Already passed --}}
            @if($assessmentPassed)
            <div class="result-banner pass" id="assessment-result-banner">
                <div style="width:32px;height:32px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <p style="font-weight:700;color:#166534;">Passed — {{ number_format($latestAttempt?->percentage ?? 0, 0) }}%</p>
                    <p style="font-size:.8rem;color:#166534;margin-top:.2rem;">All questions answered correctly. You can retake if you wish.</p>
                </div>
                <button onclick="showQuizRetake()"
                        style="margin-left:auto;font-size:.78rem;color:#166534;background:none;border:1px solid #bbf7d0;border-radius:6px;padding:.35rem .75rem;cursor:pointer;white-space:nowrap;">
                    Retake
                </button>
            </div>
            @else
            <div class="result-banner" id="assessment-result-banner" style="display:none;"></div>
            @endif

            {{-- Quiz form --}}
            <div id="assessment-form-area" style="{{ $assessmentPassed ? 'display:none;' : '' }}">
                @foreach($assessment->questions as $qi => $question)
                @php $opts = is_array($question->options) ? $question->options : (json_decode($question->options, true) ?? []); @endphp
                <div class="question-card" id="qcard-{{ $question->id }}" data-qid="{{ $question->id }}">
                    <p class="question-text">
                        <span style="font-size:.75rem;font-weight:700;color:#9ca3af;margin-right:.5rem;">{{ $qi + 1 }}.</span>
                        {{ $question->question_text }}
                        @if($question->question_type === 'multiple_select')
                        <span style="font-size:.75rem;color:#6b7280;font-weight:400;"> (Select all that apply)</span>
                        @endif
                    </p>
                    <div id="opts-{{ $question->id }}">
                        @foreach($opts as $opt)
                        <div class="option-row"
                             data-qid="{{ $question->id }}"
                             data-value="{{ $opt }}"
                             data-type="{{ $question->question_type }}"
                             onclick="selectOption(this)">
                            <div class="option-radio"></div>
                            <span style="font-size:.875rem;">{{ $opt }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div id="unanswered-warning"
                     style="display:none;margin-bottom:1rem;padding:.85rem 1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:.82rem;color:#92400e;">
                    Please answer all questions before submitting.
                </div>

                <button onclick="submitQuiz()" id="quiz-submit-btn"
                        style="background:#4f46e5;color:#fff;border:none;border-radius:10px;padding:.85rem 2rem;font-size:.9rem;font-weight:700;cursor:pointer;transition:background .15s;">
                    Submit Quiz
                </button>
            </div>
        </section>
        @endif

        {{-- ════ FINAL EXAMINATION ════ --}}
        @if($week->assessment && $week->assessment->is_final)
        @php $assessment = $week->assessment; @endphp
        <section class="final-exam-section" id="final-exam-section">

            <div class="final-exam-header">
                <div class="final-exam-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    Final Examination
                </div>
                <h2 class="final-exam-title">{{ $assessment->title }}</h2>
                <div class="final-exam-meta">
                    <span class="meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ $assessment->questions->count() }} questions
                    </span>
                    @if($assessment->time_limit_minutes)
                    <span class="meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                        {{ $assessment->time_limit_minutes }} min time limit
                    </span>
                    @endif
                    <span class="meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M9 12l2 2 4-4"/><path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                        Pass mark: {{ $assessment->pass_percentage }}%
                    </span>
                    <span class="meta-item" style="color:#b45309;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                        48-hour cooldown on fail
                    </span>
                </div>
            </div>

            {{-- Gate: course not fully complete --}}
            @if(!$allWeeksComplete)
            <div style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:12px;padding:18px 22px;display:flex;align-items:center;gap:12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <p style="font-size:.875rem;color:#64748b;margin:0;">
                    Complete all course modules and weekly quizzes before accessing the final examination.
                </p>
            </div>

            {{-- Already passed --}}
            @elseif($assessmentPassed)
            <div class="final-result-pass">
                <div style="width:40px;height:40px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <p style="font-weight:800;font-size:1rem;color:#14532d;">Examination Passed</p>
                    <p style="font-size:.82rem;color:#166534;margin-top:.25rem;">
                        Score: <strong>{{ number_format($latestAttempt->percentage, 0) }}%</strong> ·
                        Your certificate request has been submitted for approval.
                    </p>
                    <a href="{{ route('learner.graduation.status', $enrollment->id) }}"
                       style="font-size:.82rem;color:#15803d;font-weight:700;text-decoration:none;margin-top:.4rem;display:inline-block;">
                        View graduation status →
                    </a>
                </div>
            </div>

            {{-- On cooldown --}}
            @elseif($onCooldown)
            <div class="exam-cooldown-box">
                <div class="exam-cooldown-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                </div>
                <div>
                    <p class="exam-cooldown-title">Did not pass — next attempt available in:</p>
                    <p class="exam-cooldown-countdown" id="cooldown-timer">--:--:--</p>
                    <p class="exam-cooldown-sub">
                        Score: {{ number_format($latestAttempt->percentage, 0) }}% ·
                        Required: {{ $assessment->pass_percentage }}% ·
                        Use this time to review the material.
                    </p>
                </div>
            </div>

            {{-- Ready to attempt --}}
            @else
            @if($latestAttempt)
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:1.25rem;font-size:.82rem;color:#166534;">
                Previous attempt: <strong>{{ number_format($latestAttempt->percentage, 0) }}%</strong> — Did not pass. You may retry now.
            </div>
            @endif
            <button class="btn-begin-exam" id="begin-exam-btn" onclick="beginFinalExam()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                {{ $latestAttempt ? 'Retry Examination' : 'Begin Examination' }}
            </button>
            @if($assessment->time_limit_minutes)
            <p style="font-size:.75rem;color:#9ca3af;margin-top:.75rem;">
                Once started, the {{ $assessment->time_limit_minutes }}-minute timer begins immediately and cannot be paused.
            </p>
            @endif
            @endif

        </section>
        @endif

        {{-- ════ WEEK FOOTER ════ --}}
        <div class="week-footer">
            <div>
                @if($prevWeekId)
                <a href="{{ route('learner.learning.week', [$enrollmentId, $prevWeekId]) }}" class="nav-btn nav-btn-ghost">← Previous</a>
                @endif
            </div>
            <div id="next-container" style="{{ ($week->assessment && !$assessmentPassed) ? 'display:none;' : '' }}">
                @if($nextWeekId)
                <a href="{{ route('learner.learning.week', [$enrollmentId, $nextWeekId]) }}" class="nav-btn nav-btn-primary" id="next-week-btn">
                    Next Week →
                </a>
                @elseif(!$week->assessment?->is_final)
                {{-- All content weeks done, no final exam week — should not normally happen --}}
                <a href="{{ route('learner.graduation.status', $enrollment->id) }}" class="nav-btn nav-btn-primary" style="background:#16a34a;border-color:#16a34a;">
                    View Graduation Status →
                </a>
                @endif
            </div>
        </div>

    </main>
</div>
@endsection

@push('scripts')
<script>
const D            = JSON.parse(document.getElementById('js-data').textContent);
const ENROLLMENT_ID  = D.enrollmentId;
const WEEK_ID        = D.weekId;
const HAS_ASSESSMENT = D.hasAssessment;
const IS_FINAL       = D.isFinal;
const ASSESSMENT_ID  = D.assessmentId;
const PASS_MARK      = D.passMark;
const CSRF           = document.querySelector('meta[name="csrf-token"]').content;
const CONTENT_IDS    = D.contentIds;
const PAGE_LOAD_TIME = Date.now();

const completed      = Object.assign({}, D.contentCompletion);
let   assessmentPassed = D.assessmentPassed;

// ── Sidebar toggle ────────────────────────────────────────────────────────────
function toggleSidebar() {
    document.getElementById('learn-sidebar').classList.toggle('collapsed');
}

// ── Sidebar dot updates ───────────────────────────────────────────────────────
function markSidebarDot(contentId) {
    const dot = document.getElementById('dot-' + contentId);
    if (!dot) return;
    dot.classList.add('done');
    dot.innerHTML = '<svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
}

function markAssessmentDot() {
    const dot = document.getElementById('dot-assessment');
    if (!dot) return;
    dot.classList.add('done');
    dot.innerHTML = '<svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
}

function markCompletionIndicator(contentId) {
    const ci = document.getElementById('ci-' + contentId);
    if (ci) { ci.classList.add('done'); }
    const label = document.getElementById('ci-label-' + contentId);
    if (label) label.textContent = 'Completed';
}

// ── Topbar count ──────────────────────────────────────────────────────────────
function updateTopbar() {
    const done = CONTENT_IDS.filter(id => completed[id]).length + (assessmentPassed ? 1 : 0);
    const el   = document.getElementById('topbar-done-count');
    if (el) el.textContent = done;
}

// ── Mark content done ─────────────────────────────────────────────────────────
function markContentDone(contentId) {
    if (completed[contentId]) return;
    completed[contentId] = true;
    markSidebarDot(contentId);
    markCompletionIndicator(contentId);
    updateTopbar();
    checkAllContentDone();

    fetch('/learner/learning/content/' + contentId + '/complete', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({}),
    }).catch(() => {});
}

function checkAllContentDone() {
    const allDone = CONTENT_IDS.every(id => completed[id]);
    // If no assessment, show next button when all content done
    if (!HAS_ASSESSMENT && allDone) {
        const nc = document.getElementById('next-container');
        if (nc) nc.style.display = '';
    }
    // If final exam, unlock the begin button visually (server still gates it)
    if (IS_FINAL && allDone) {
        const gate = document.querySelector('#final-exam-section [style*="f1f5f9"]');
        // Gate is server-rendered; page reload needed — no JS gate removal
    }
}

// ── Scroll sentinel ───────────────────────────────────────────────────────────
const sentinelObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        if (Date.now() - PAGE_LOAD_TIME < 2000) return; // ignore immediate load
        const id = parseInt(entry.target.dataset.contentId);
        if (!completed[id]) {
            sentinelObserver.unobserve(entry.target);
            markContentDone(id);
        }
    });
}, { root: document.getElementById('learn-main'), rootMargin: '0px', threshold: 0.5 });

document.querySelectorAll('.scroll-sentinel').forEach(el => {
    const id = parseInt(el.dataset.contentId);
    if (!completed[id]) sentinelObserver.observe(el);
});

// ── Video players ─────────────────────────────────────────────────────────────
function parseVideoUrl(url) {
    if (!url) return { type: 'none' };
    const yt = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (yt) return { type: 'youtube', videoId: yt[1], embedUrl: 'https://www.youtube.com/embed/' + yt[1] + '?enablejsapi=1&rel=0&modestbranding=1' };
    const vm = url.match(/vimeo\.com\/(\d+)/);
    if (vm) return { type: 'iframe', embedUrl: 'https://player.vimeo.com/video/' + vm[1] };
    const gd = url.match(/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/);
    if (gd) return { type: 'iframe', embedUrl: 'https://drive.google.com/file/d/' + gd[1] + '/preview' };
    if (url.match(/\.(mp4|webm|ogg|mov)(\?.*)?$/i)) return { type: 'file', embedUrl: url };
    return { type: 'iframe', embedUrl: url };
}

let _ytReady = false, _ytQueue = [];
function ensureYT() {
    if (_ytReady || document.querySelector('script[src*="youtube.com/iframe_api"]')) return;
    const s = document.createElement('script'); s.src = 'https://www.youtube.com/iframe_api'; document.head.appendChild(s);
}
window.onYouTubeIframeAPIReady = function () { _ytReady = true; _ytQueue.forEach(fn => fn()); _ytQueue = []; };

function initVideoSection(section) {
    const contentId = parseInt(section.dataset.contentId);
    if (completed[contentId]) return;
    const url      = section.dataset.videoUrl;
    const duration = parseFloat(section.dataset.videoDuration) || 0;
    const wrap     = document.getElementById('player-wrap-' + contentId);
    const note     = document.getElementById('video-note-' + contentId);
    if (!wrap) return;
    const parsed   = parseVideoUrl(url);
    const onDone   = () => {
        if (!completed[contentId]) {
            markContentDone(contentId);
            if (note) note.textContent = 'Watched ✓';
        }
    };

    if (parsed.type === 'file') {
        const vid = document.createElement('video');
        vid.controls = true; vid.preload = 'metadata';
        vid.style = 'position:absolute;inset:0;width:100%;height:100%;';
        vid.innerHTML = '<source src="' + parsed.embedUrl + '" type="video/mp4">';
        wrap.appendChild(vid);
        vid.addEventListener('timeupdate', function handler() {
            if (vid.duration > 0 && vid.currentTime / vid.duration >= 0.9) {
                vid.removeEventListener('timeupdate', handler); onDone();
            }
        });
    } else if (parsed.type === 'youtube') {
        const div = document.createElement('div');
        div.id = 'yt-' + contentId;
        div.style = 'position:absolute;inset:0;width:100%;height:100%;';
        wrap.appendChild(div);
        const init = () => {
            const player = new YT.Player(div.id, {
                videoId: parsed.videoId,
                playerVars: { rel: 0, modestbranding: 1 },
                events: {
                    onReady: () => {
                        setInterval(() => {
                            try {
                                if (player.getDuration() > 0 && player.getCurrentTime() / player.getDuration() >= 0.9) onDone();
                            } catch(e) {}
                        }, 3000);
                    },
                    onStateChange: e => { if (e.data === YT.PlayerState.ENDED) onDone(); }
                }
            });
        };
        ensureYT(); _ytReady ? init() : _ytQueue.push(init);
    } else {
        const fr = document.createElement('iframe');
        fr.src = parsed.embedUrl;
        fr.allow = 'autoplay; fullscreen; picture-in-picture';
        fr.style = 'position:absolute;inset:0;width:100%;height:100%;border:0;';
        wrap.appendChild(fr);
        const gateSecs = duration ? Math.round(duration * 60 * 0.9) : 300;
        let elapsed = 0, visible = false;
        const vis = new IntersectionObserver(([e]) => { visible = e.isIntersecting; }); vis.observe(fr);
        setInterval(() => { if (visible) { elapsed++; if (elapsed >= gateSecs) { vis.disconnect(); onDone(); } } }, 1000);
    }
}
document.querySelectorAll('section[data-type="video"]').forEach(initVideoSection);

// ── Weekly quiz ───────────────────────────────────────────────────────────────
const quizAnswers = {};
let   currentAttemptId = null;

function selectOption(el) {
    const qid  = el.dataset.qid;
    const type = el.dataset.type;

    if (type === 'multiple_select') {
        el.classList.toggle('selected');
        el.querySelector('.option-radio').style.background = el.classList.contains('selected') ? '#4f46e5' : '';
        el.querySelector('.option-radio').style.borderColor = el.classList.contains('selected') ? '#4f46e5' : '#d1d5db';
        const selected = [...document.querySelectorAll(`.option-row[data-qid="${qid}"].selected`)].map(o => o.dataset.value);
        quizAnswers[qid] = selected.length ? selected : undefined;
    } else {
        document.querySelectorAll(`.option-row[data-qid="${qid}"]`).forEach(o => o.classList.remove('selected'));
        el.classList.add('selected');
        quizAnswers[qid] = el.dataset.value;
    }
    document.getElementById('qcard-' + qid)?.classList.remove('unanswered');
}

async function submitQuiz() {
    // Create attempt first if not started
    if (!currentAttemptId) {
        try {
            const res  = await fetch('/learner/assessments/' + ASSESSMENT_ID + '/attempt', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ enrollment_id: ENROLLMENT_ID }),
            });
            const data = await res.json();
            if (!data.success) { alert(data.message || 'Could not start quiz.'); return; }
            currentAttemptId = data.attempt_id;
        } catch { alert('Network error. Please try again.'); return; }
    }

    // Validate all answered
    const questions = document.querySelectorAll('.question-card');
    let allAnswered = true;
    questions.forEach(card => {
        const qid = card.dataset.qid;
        if (!quizAnswers[qid] || (Array.isArray(quizAnswers[qid]) && quizAnswers[qid].length === 0)) {
            card.classList.add('unanswered');
            allAnswered = false;
        }
    });

    if (!allAnswered) {
        document.getElementById('unanswered-warning').style.display = '';
        document.querySelector('.question-card.unanswered')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    document.getElementById('unanswered-warning').style.display = 'none';

    const btn = document.getElementById('quiz-submit-btn');
    btn.disabled = true; btn.textContent = 'Submitting…';

    const payload = Array.from(questions).map(card => ({
        question_id: card.dataset.qid,
        answer: quizAnswers[card.dataset.qid],
    }));

    try {
        const res  = await fetch('/learner/attempts/' + currentAttemptId + '/submit', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ answers: payload }),
        });
        const data = await res.json();

        if (data.success) {
            showQuizResult(data.score, data.passed);
        } else {
            alert(data.message || 'Submission failed.');
            btn.disabled = false; btn.textContent = 'Submit Quiz';
        }
    } catch {
        alert('Network error. Please try again.');
        btn.disabled = false; btn.textContent = 'Submit Quiz';
    }
}

function showQuizResult(score, passed) {
    const banner = document.getElementById('assessment-result-banner');
    const form   = document.getElementById('assessment-form-area');
    const btn    = document.getElementById('quiz-submit-btn');

    if (passed) {
        assessmentPassed = true;
        markAssessmentDot();
        updateTopbar();
        const nc = document.getElementById('next-container');
        if (nc) nc.style.display = '';

        banner.className = 'result-banner pass';
        banner.style.display = '';
        banner.innerHTML = `
            <div style="width:32px;height:32px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </div>
            <div>
                <p style="font-weight:700;color:#166534;">Passed — ${score}%</p>
                <p style="font-size:.8rem;color:#166534;margin-top:.2rem;">All correct. Scroll down to continue to the next week.</p>
            </div>`;
        if (form) form.style.display = 'none';
        setTimeout(() => document.querySelector('.week-footer')?.scrollIntoView({ behavior: 'smooth', block: 'center' }), 400);
    } else {
        banner.className = 'result-banner fail';
        banner.style.display = '';
        banner.innerHTML = `
            <div style="width:32px;height:32px;border-radius:50%;background:#dc2626;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </div>
            <div style="flex:1;">
                <p style="font-weight:700;color:#991b1b;">Score: ${score}% — you need 100% to pass</p>
                <p style="font-size:.8rem;color:#991b1b;margin-top:.2rem;">Review the material and try again. You can retry immediately.</p>
            </div>
            <button onclick="resetQuiz()" style="margin-left:auto;font-size:.78rem;color:#991b1b;background:none;border:1px solid #fca5a5;border-radius:6px;padding:.35rem .75rem;cursor:pointer;white-space:nowrap;flex-shrink:0;">
                Try Again
            </button>`;
        currentAttemptId = null;
        if (btn) { btn.disabled = false; btn.textContent = 'Submit Quiz'; }
    }
}

function resetQuiz() {
    document.querySelectorAll('.option-row').forEach(o => {
        o.classList.remove('selected');
        const radio = o.querySelector('.option-radio');
        if (radio) { radio.style.background = ''; radio.style.borderColor = ''; }
    });
    document.querySelectorAll('.question-card').forEach(c => c.classList.remove('unanswered'));
    Object.keys(quizAnswers).forEach(k => delete quizAnswers[k]);
    currentAttemptId = null;
    document.getElementById('unanswered-warning').style.display = 'none';
    const btn = document.getElementById('quiz-submit-btn');
    if (btn) { btn.disabled = false; btn.textContent = 'Submit Quiz'; }
    const banner = document.getElementById('assessment-result-banner');
    if (banner) banner.style.display = 'none';
    document.getElementById('assessment-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showQuizRetake() {
    const form = document.getElementById('assessment-form-area');
    if (form) form.style.display = '';
    resetQuiz();
}

// ── Final exam ────────────────────────────────────────────────────────────────
function beginFinalExam() {
    const btn = document.getElementById('begin-exam-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Starting…'; }

    fetch('/learner/assessments/' + ASSESSMENT_ID + '/attempt', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ enrollment_id: ENROLLMENT_ID }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.attempt_id) {
            window.location.href = '/learner/attempts/' + data.attempt_id;
        } else {
            if (btn) { btn.disabled = false; btn.textContent = '{{ $latestAttempt ? "Retry Examination" : "Begin Examination" }}'; }
            toastr.error(data.message || 'Could not start examination.');
        }
    })
    .catch(() => {
        if (btn) { btn.disabled = false; btn.textContent = '{{ $latestAttempt ? "Retry Examination" : "Begin Examination" }}'; }
        toastr.error('Network error. Please try again.');
    });
}

// ── Cooldown countdown ────────────────────────────────────────────────────────
@if($onCooldown && $cooldownEnd)
(function () {
    const end = new Date('{{ $cooldownEnd->toIso8601String() }}').getTime();
    function tick() {
        const diff = Math.max(0, end - Date.now());
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        const el = document.getElementById('cooldown-timer');
        if (el) el.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        if (diff > 0) setTimeout(tick, 1000);
        else location.reload();
    }
    tick();
})();
@endif

updateTopbar();
checkAllContentDone();
</script>
@endpush