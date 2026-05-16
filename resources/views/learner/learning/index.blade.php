@extends('layouts.learner')
@section('title', $enrollment->program->name . ' — ' . $week->title)

@php
    // Defaults — overwritten below if the week has a final assessment
    $lastAttempt = null;
    $onCooldown  = false;
    $cooldownEnd = null;
@endphp

@section('content')

{{-- Server data for JS --}}
<script id="js-enrollment-id"     type="application/json">{{ $enrollmentId }}</script>
<script id="js-week-id"           type="application/json">{{ $week->id }}</script>
<script id="js-has-assessment"    type="application/json">{{ $week->assessment ? 'true' : 'false' }}</script>
<script id="js-is-final"          type="application/json">{{ ($week->assessment && $week->assessment->is_final) ? 'true' : 'false' }}</script>
<script id="js-assessment-passed" type="application/json">{{ $assessmentPassed ? 'true' : 'false' }}</script>
<script id="js-content-ids"       type="application/json">@json($contents->pluck('id'))</script>
<script id="js-content-completion" type="application/json">
    @json($contents->mapWithKeys(fn($c) => [
        $c->id => (bool)($c->contentProgress->first()?->is_completed ?? false)
    ]))
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
                $isFinalWk = $wk->assessment?->is_final ?? false;
            @endphp

            @if($isLocked)
            <div class="week-nav-item locked">
            @else
            <a href="{{ route('learner.learning.week', [$enrollmentId, $wk->id]) }}"
               class="week-nav-item {{ $isCurrent ? 'active' : '' }}"
               style="{{ $isFinalWk ? 'border-left:3px solid #7c3aed;' : '' }}">
            @endif
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
                    <p style="font-size:.72rem;color:#9ca3af;margin-bottom:.1rem;">
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

            {{-- Per-item list for current week --}}
            @if($isCurrent)
            @php
                $weekContents = $wk->contents()->with(['contentProgress' => fn($q) =>
                    $q->where('user_id', auth()->id())->where('enrollment_id', $enrollmentId)
                ])->orderBy('order')->get();
            @endphp
            @foreach($weekContents as $wc)
            @php $wcDone = (bool)($wc->contentProgress->first()?->is_completed ?? false); @endphp
            <div class="content-nav-item" id="sidebar-dot-{{ $wc->id }}">
                <div class="content-nav-dot {{ $wcDone ? 'done' : '' }}" id="dot-{{ $wc->id }}">
                    @if($wcDone)<svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>@endif
                </div>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">{{ $wc->title }}</span>
                <span style="font-size:.65rem;color:#d1d5db;text-transform:uppercase;flex-shrink:0;">{{ $wc->content_type }}</span>
            </div>
            @endforeach

            @if($week->assessment)
            <div class="content-nav-item" id="sidebar-dot-assessment"
                 style="{{ $week->assessment->is_final ? 'background:#f5f3ff;' : '' }}">
                <div class="content-nav-dot {{ $assessmentPassed ? 'done' : '' }}" id="dot-assessment"
                     style="{{ ($week->assessment->is_final && !$assessmentPassed) ? 'border-color:#c4b5fd;' : '' }}">
                    @if($assessmentPassed)<svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>@endif
                </div>
                <span style="{{ $week->assessment->is_final ? 'color:#5b21b6;font-weight:600;' : '' }}">
                    {{ $week->assessment->is_final ? 'Final Examination' : 'Assessment' }}
                </span>
                <span style="font-size:.65rem;color:#d1d5db;flex-shrink:0;">{{ $week->assessment->is_final ? 'exam' : 'quiz' }}</span>
            </div>
            @endif
            @endif

            @endforeach
        </div>
    </aside>

    {{-- ══════ MAIN ══════ --}}
    <main class="learn-main" id="learn-main">

        {{-- Top bar --}}
        <div class="learn-topbar">
            <button onclick="toggleSidebar()" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;border:none;background:transparent;cursor:pointer;flex-shrink:0;" title="Toggle navigation">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            </button>
            <div style="flex:1;min-width:0;font-size:.85rem;color:#6b7280;display:flex;align-items:center;gap:.4rem;overflow:hidden;">
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;color:#374151;">
                    Week {{ $week->week_number }}: {{ $week->title }}
                </span>
            </div>
            <div style="flex-shrink:0;display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#9ca3af;">
                <span id="topbar-done-count">0</span>/<span>{{ $contents->count() }}{{ $week->assessment ? '+' . ($week->assessment->is_final ? 'exam' : 'quiz') : '' }}</span>
            </div>
        </div>

        {{-- Week title --}}
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
                &nbsp;·&nbsp; {{ $i + 1 }} of {{ $contents->count() }}{{ $week->assessment ? ' + ' . ($week->assessment->is_final ? 'examination' : 'assessment') : '' }}
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

        {{-- ════ WEEKLY ASSESSMENT ════ --}}
        @if($week->assessment && !$week->assessment->is_final)
        @php $assessment = $week->assessment; @endphp
        <section class="assessment-section" id="assessment-section">

            <div class="assessment-header">
                <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1;margin-bottom:.35rem;">Week Assessment</p>
                <h2 style="font-size:1.15rem;font-weight:700;color:#111827;margin-bottom:.75rem;">{{ $assessment->title }}</h2>
                <div style="display:flex;gap:1.5rem;font-size:.8rem;color:#6b7280;flex-wrap:wrap;">
                    <span>{{ $assessment->questions->count() }} question{{ $assessment->questions->count() !== 1 ? 's' : '' }}</span>
                    <span>Pass mark: {{ $assessment->pass_percentage }}%</span>
                    @if($assessment->time_limit_minutes)<span>Time limit: {{ $assessment->time_limit_minutes }} min</span>@endif
                </div>
            </div>

            @if($assessmentPassed)
            <div class="result-banner pass" id="assessment-result-banner">
                <div style="width:32px;height:32px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <p style="font-weight:700;color:#166534;">Passed · {{ number_format($latestAttempt?->percentage ?? 0, 0) }}%</p>
                    <p style="font-size:.8rem;color:#166534;margin-top:.2rem;">Well done. You can retake to improve your score.</p>
                </div>
                <button onclick="showAssessmentForm()" style="margin-left:auto;font-size:.78rem;color:#166534;background:none;border:1px solid #bbf7d0;border-radius:6px;padding:.35rem .75rem;cursor:pointer;white-space:nowrap;">Retake</button>
            </div>
            @else
            <div class="result-banner" id="assessment-result-banner" style="display:none;"></div>
            @endif

            <div id="assessment-form-area" style="{{ $assessmentPassed ? 'display:none;' : '' }}">
                @foreach($assessment->questions as $qi => $question)
                @php $opts = is_array($question->options) ? $question->options : (json_decode($question->options, true) ?? []); @endphp
                <div class="question-card" id="qcard-{{ $question->id }}" data-qid="{{ $question->id }}">
                    <p class="question-text">
                        <span style="font-size:.75rem;font-weight:700;color:#9ca3af;margin-right:.5rem;">{{ $qi + 1 }}.</span>
                        {{ $question->question_text }}
                    </p>
                    <div id="opts-{{ $question->id }}">
                        @foreach($opts as $opt)
                        <div class="option-row" data-qid="{{ $question->id }}" data-value="{{ $opt }}" onclick="selectOption(this)">
                            <div class="option-radio"></div>
                            <span style="font-size:.875rem;">{{ $opt }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div id="unanswered-warning" style="display:none;margin-bottom:1rem;padding:.85rem 1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:.82rem;color:#92400e;">
                    Please answer all questions before submitting.
                </div>

                <button onclick="submitAssessment()" id="submit-btn"
                    style="background:#4f46e5;color:#fff;border:none;border-radius:10px;padding:.85rem 2rem;font-size:.9rem;font-weight:700;cursor:pointer;transition:background .15s;">
                    Submit Assessment
                </button>
            </div>
        </section>
        @endif

        {{-- ════ FINAL EXAMINATION ════ --}}
        @if($week->assessment && $week->assessment->is_final)
        @php
            $assessment   = $week->assessment;
            $lastAttempt  = $assessment->getLatestAttempt(auth()->user(), $enrollmentId);
            $onCooldown   = $assessment->isOnCooldownFor(auth()->user(), $enrollmentId);
            $cooldownEnd  = $assessment->cooldownEndsAt(auth()->user(), $enrollmentId);
            $allContent   = $contents->every(fn($c) => $c->contentProgress->first()?->is_completed ?? false);
        @endphp
        <section class="final-exam-section" id="final-exam-section">

            <div class="final-exam-header">
                <div class="final-exam-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    Final Examination
                </div>
                <h2 class="final-exam-title">{{ $assessment->title }}</h2>
                <div class="final-exam-meta">
                    <span class="meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ $assessment->questions->count() }} questions
                    </span>
                    <span class="meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                        {{ $assessment->time_limit_minutes }} min time limit
                    </span>
                    <span class="meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M9 12l2 2 4-4"/><path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                        Pass mark: {{ $assessment->pass_percentage }}%
                    </span>
                    <span class="meta-item" style="color:#b45309;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                        48 hr cooldown on fail
                    </span>
                </div>
            </div>

            {{-- Content not complete gate --}}
            @if(!$allContent)
            <div style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:12px;padding:18px 22px;display:flex;align-items:center;gap:12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <p style="font-size:.875rem;color:#64748b;margin:0;">Complete all content above before accessing the final examination.</p>
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
                        Score: <strong>{{ number_format($lastAttempt->percentage, 0) }}%</strong> ·
                        Your certificate request has been submitted for approval.
                    </p>
                </div>
            </div>

            {{-- On cooldown --}}
            @elseif($onCooldown)
            <div class="exam-cooldown-box">
                <div class="exam-cooldown-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                </div>
                <div>
                    <p class="exam-cooldown-title">You did not pass — next attempt available in:</p>
                    <p class="exam-cooldown-countdown" id="cooldown-timer">--:--:--</p>
                    <p class="exam-cooldown-sub">Score: {{ number_format($lastAttempt->percentage, 0) }}% · Required: {{ $assessment->pass_percentage }}% · Use this time to review the course material.</p>
                </div>
            </div>

            {{-- Ready to attempt --}}
            @else
            @if($lastAttempt)
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:1.25rem;font-size:.82rem;color:#166534;">
                Previous attempt: <strong>{{ number_format($lastAttempt->percentage, 0) }}%</strong> · Did not pass. You may now retry.
            </div>
            @endif

            <button class="btn-begin-exam" id="begin-exam-btn" onclick="beginFinalExam()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                {{ $lastAttempt ? 'Retry Examination' : 'Begin Examination' }}
            </button>
            <p style="font-size:.75rem;color:#9ca3af;margin-top:.75rem;">
                Once started, the {{ $assessment->time_limit_minutes }}-minute timer begins immediately.
                Closing the page will not stop the timer.
            </p>
            @endif

        </section>
        @endif

        {{-- ════ WEEK FOOTER ════ --}}
        <div class="week-footer">
            <div>
                @if($prevWeekId)
                <a href="{{ route('learner.learning.week', [$enrollmentId, $prevWeekId]) }}" class="nav-btn nav-btn-ghost">← Previous</a>
                @else
                <span></span>
                @endif
            </div>
            <div id="next-container" style="{{ ($week->assessment && !$assessmentPassed) ? 'display:none;' : '' }}">
                @if($nextWeekId)
                <a href="{{ route('learner.learning.week', [$enrollmentId, $nextWeekId]) }}" class="nav-btn nav-btn-primary" id="next-week-btn">Next Week →</a>
                @else
                <a href="{{ route('learner.graduation.status', $enrollment->id) }}" class="nav-btn nav-btn-primary" style="background:#16a34a;border-color:#16a34a;">View Graduation Status →</a>
                @endif
            </div>
        </div>

    </main>
</div>
@endsection

@push('scripts')
<script>
const ENROLLMENT_ID  = JSON.parse(document.getElementById('js-enrollment-id').textContent);
const WEEK_ID        = JSON.parse(document.getElementById('js-week-id').textContent);
const HAS_ASSESSMENT = JSON.parse(document.getElementById('js-has-assessment').textContent);
const IS_FINAL       = JSON.parse(document.getElementById('js-is-final').textContent);
const CSRF           = document.querySelector('meta[name="csrf-token"]').content;
const CONTENT_IDS    = JSON.parse(document.getElementById('js-content-ids').textContent);
const PAGE_LOAD_TIME = Date.now();

const completed = JSON.parse(document.getElementById('js-content-completion').textContent);
let assessmentPassed = JSON.parse(document.getElementById('js-assessment-passed').textContent);

function toggleSidebar() {
    document.getElementById('learn-sidebar').classList.toggle('collapsed');
}

function markSidebarDone(contentId) {
    const dot = document.getElementById('dot-' + contentId);
    if (!dot) return;
    dot.classList.add('done');
    dot.innerHTML = '<svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
}

function markAssessmentSidebarDone() {
    const dot = document.getElementById('dot-assessment');
    if (!dot) return;
    dot.classList.add('done');
    dot.innerHTML = '<svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
}

function markContentIndicatorDone(contentId) {
    const ci = document.getElementById('ci-' + contentId);
    if (ci) { ci.classList.add('done'); const l = document.getElementById('ci-label-' + contentId); if (l) l.textContent = 'Completed'; }
}

function updateTopbarCount() {
    const doneCount = CONTENT_IDS.filter(id => completed[id]).length + (assessmentPassed ? 1 : 0);
    const el = document.getElementById('topbar-done-count');
    if (el) el.textContent = doneCount;
}

function markContentDone(contentId, silent) {
    if (completed[contentId]) return;
    completed[contentId] = true;
    markSidebarDone(contentId);
    markContentIndicatorDone(contentId);
    updateTopbarCount();
    checkWeekCompletion();
    fetch('/learner/learning/content/' + contentId + '/complete', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({}),
    }).catch(() => {});
}

function checkWeekCompletion() {
    const allDone = CONTENT_IDS.every(id => completed[id]);
    if (!HAS_ASSESSMENT && allDone) {
        const nc = document.getElementById('next-container');
        if (nc) nc.style.display = '';
    }
}

// ── Scroll sentinel ────────────────────────────────────────────────────────
const sentinelObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        if (Date.now() - PAGE_LOAD_TIME < 1500) return;
        const id = parseInt(entry.target.dataset.contentId);
        if (!completed[id]) { sentinelObserver.unobserve(entry.target); markContentDone(id); }
    });
}, { root: document.getElementById('learn-main'), rootMargin: '0px', threshold: 0.2 });

document.querySelectorAll('.scroll-sentinel').forEach(el => {
    const id = parseInt(el.dataset.contentId);
    if (!completed[id]) sentinelObserver.observe(el);
});

// ── Video ──────────────────────────────────────────────────────────────────
function parseVideoUrl(url) {
    if (!url) return { type: 'none', embedUrl: '' };
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
window.onYouTubeIframeAPIReady = function() { _ytReady = true; _ytQueue.forEach(fn => fn()); _ytQueue.length = 0; };

function initVideoSection(section) {
    const contentId = parseInt(section.dataset.contentId);
    const url = section.dataset.videoUrl;
    const duration = parseFloat(section.dataset.videoDuration) || 0;
    const wrap = document.getElementById('player-wrap-' + contentId);
    const note = document.getElementById('video-note-' + contentId);
    if (!wrap || completed[contentId]) return;
    const parsed = parseVideoUrl(url);
    const onComplete = () => { if (!completed[contentId]) { markContentDone(contentId); if (note) note.textContent = 'Watched ✓'; } };
    if (parsed.type === 'file') {
        const vid = document.createElement('video'); vid.controls = true; vid.preload = 'metadata'; vid.style = 'position:absolute;inset:0;width:100%;height:100%;';
        vid.innerHTML = '<source src="' + parsed.embedUrl + '" type="video/mp4">';
        wrap.appendChild(vid);
        vid.addEventListener('timeupdate', function handler() { if (vid.duration > 0 && (vid.currentTime / vid.duration) >= 0.9) { vid.removeEventListener('timeupdate', handler); onComplete(); } });
    } else if (parsed.type === 'youtube') {
        const div = document.createElement('div'); div.id = 'yt-' + contentId; div.style = 'position:absolute;inset:0;width:100%;height:100%;'; wrap.appendChild(div);
        const init = () => new YT.Player(div.id, { videoId: parsed.videoId, playerVars: { rel: 0, modestbranding: 1 }, events: {
            onReady: () => { const iv = setInterval(() => { try { if (player.getDuration() > 0 && (player.getCurrentTime() / player.getDuration()) >= 0.9) { clearInterval(iv); onComplete(); } } catch(e) {} }, 3000); },
            onStateChange: e => { if (e.data === YT.PlayerState.ENDED) onComplete(); }
        }});
        ensureYT(); _ytReady ? init() : _ytQueue.push(init);
    } else {
        const fr = document.createElement('iframe'); fr.src = parsed.embedUrl; fr.allow = 'autoplay; fullscreen; picture-in-picture'; fr.style = 'position:absolute;inset:0;width:100%;height:100%;border:0;'; wrap.appendChild(fr);
        const gateSecs = duration ? Math.round(duration * 60 * 0.9) : 300; let elapsed = 0, visible = false;
        const visObs = new IntersectionObserver(([e]) => { visible = e.isIntersecting; }); visObs.observe(fr);
        const ticker = setInterval(() => { if (visible) { elapsed++; if (elapsed >= gateSecs) { clearInterval(ticker); visObs.disconnect(); onComplete(); } } }, 1000);
    }
}
document.querySelectorAll('section[data-type="video"]').forEach(initVideoSection);

// ── Weekly assessment ──────────────────────────────────────────────────────
const answers = {};
let currentAttemptId = null;

function selectOption(el) {
    const qid = el.dataset.qid;
    document.querySelectorAll('.option-row[data-qid="' + qid + '"]').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    answers[qid] = el.dataset.value;
    const card = document.getElementById('qcard-' + qid);
    if (card) card.classList.remove('unanswered');
}

function submitAssessment() {
    if (!currentAttemptId) {
        fetch('/learner/assessments/{{ $week->assessment?->id ?? 0 }}/attempt', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ enrollment_id: ENROLLMENT_ID }),
        })
        .then(r => r.json())
        .then(data => {
            currentAttemptId = data.attempt_id;
            if (currentAttemptId) submitAssessment();
            else alert(data.message || 'Could not start attempt.');
        });
        return;
    }

    const questions = document.querySelectorAll('.question-card');
    let allAnswered = true;
    questions.forEach(card => { if (!answers[card.dataset.qid]) { card.classList.add('unanswered'); allAnswered = false; } });
    if (!allAnswered) {
        document.getElementById('unanswered-warning').style.display = '';
        document.querySelector('.question-card.unanswered')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    document.getElementById('unanswered-warning').style.display = 'none';

    const btn = document.getElementById('submit-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Submitting…'; }

    const payload = Array.from(questions).map(card => ({ question_id: card.dataset.qid, answer: answers[card.dataset.qid] }));

    fetch('/learner/attempts/' + currentAttemptId + '/submit', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ answers: payload }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success && data.score === undefined) {
            if (btn) { btn.disabled = false; btn.textContent = 'Submit Assessment'; }
            alert(data.message || 'Submission failed.');
            return;
        }
        showAssessmentResult(data.score, data.passed ?? (data.score >= {{ $week->assessment && !$week->assessment->is_final ? $week->assessment->pass_percentage : 70 }}));
    })
    .catch(() => { if (btn) { btn.disabled = false; btn.textContent = 'Submit Assessment'; } alert('Submission failed.'); });
}

function showAssessmentResult(score, passed) {
    const banner = document.getElementById('assessment-result-banner');
    const form   = document.getElementById('assessment-form-area');
    if (passed) {
        assessmentPassed = true;
        markAssessmentSidebarDone();
        updateTopbarCount();
        const nc = document.getElementById('next-container');
        if (nc) nc.style.display = '';
        banner.className = 'result-banner pass';
        banner.style.display = '';
        banner.innerHTML = `<div style="width:32px;height:32px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="16" height="16" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><div><p style="font-weight:700;color:#166534;">Passed · ${score}%</p><p style="font-size:.8rem;color:#166534;margin-top:.2rem;">Scroll down to continue.</p></div>`;
        if (form) form.style.display = 'none';
        setTimeout(() => { document.querySelector('.week-footer')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 400);
    } else {
        const passMark = {{ $week->assessment && !$week->assessment->is_final ? $week->assessment->pass_percentage : 70 }};
        banner.className = 'result-banner fail';
        banner.style.display = '';
        banner.innerHTML = `<div style="width:32px;height:32px;border-radius:50%;background:#dc2626;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="16" height="16" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><div style="flex:1;"><p style="font-weight:700;color:#991b1b;">Score: ${score}% — below the ${passMark}% pass mark</p><p style="font-size:.8rem;color:#991b1b;margin-top:.2rem;">Review the material and try again.</p></div><button onclick="resetAssessmentForm()" style="margin-left:auto;font-size:.78rem;color:#991b1b;background:none;border:1px solid #fca5a5;border-radius:6px;padding:.35rem .75rem;cursor:pointer;white-space:nowrap;flex-shrink:0;">Try Again</button>`;
        currentAttemptId = null;
        if (btn) { btn.disabled = false; btn.textContent = 'Submit Assessment'; }
    }
}

function resetAssessmentForm() {
    document.querySelectorAll('.option-row').forEach(o => o.classList.remove('selected'));
    document.querySelectorAll('.question-card').forEach(c => c.classList.remove('unanswered'));
    Object.keys(answers).forEach(k => delete answers[k]);
    currentAttemptId = null;
    document.getElementById('unanswered-warning').style.display = 'none';
    const btn = document.getElementById('submit-btn');
    if (btn) { btn.disabled = false; btn.textContent = 'Submit Assessment'; }
    const banner = document.getElementById('assessment-result-banner');
    if (banner) banner.style.display = 'none';
    document.getElementById('assessment-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showAssessmentForm() {
    const form = document.getElementById('assessment-form-area');
    if (form) form.style.display = '';
    const banner = document.getElementById('assessment-result-banner');
    if (banner) banner.style.display = 'none';
    resetAssessmentForm();
}

// ── Final exam ─────────────────────────────────────────────────────────────
const FINAL_EXAM_ASSESSMENT_ID = {{ $week->assessment?->id ?? 0 }};
const FINAL_EXAM_BTN_LABEL     = @json($lastAttempt ? 'Retry Examination' : 'Begin Examination');

function beginFinalExam() {
    const btn = document.getElementById('begin-exam-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Starting…'; }

    fetch('/learner/assessments/' + FINAL_EXAM_ASSESSMENT_ID + '/attempt', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ enrollment_id: ENROLLMENT_ID }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.attempt_id) {
            window.location.href = '/learner/attempts/' + data.attempt_id;
        } else {
            if (btn) { btn.disabled = false; btn.textContent = FINAL_EXAM_BTN_LABEL; }
            toastr.error(data.message || 'Could not start examination.');
        }
    })
    .catch(() => {
        if (btn) { btn.disabled = false; btn.textContent = FINAL_EXAM_BTN_LABEL; }
        toastr.error('Network error. Please try again.');
    });
}

// ── Cooldown countdown ─────────────────────────────────────────────────────
@if(isset($onCooldown) && $onCooldown && isset($cooldownEnd))
(function() {
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

updateTopbarCount();
checkWeekCompletion();
</script>
@endpush