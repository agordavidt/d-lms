@extends('layouts.learner')
@section('title', $enrollment->program->name . ' — ' . $week->title)

@push('styles')
<style>
/* ── Layout ───────────────────────────────────────────────────────────── */
.learn-wrap {
    display: flex;
    height: calc(100vh - 60px);
    overflow: hidden;
}

/* ── Sidebar ──────────────────────────────────────────────────────────── */
.learn-sidebar {
    width: 280px;
    min-width: 280px;
    background: #fff;
    border-right: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: width .22s ease, min-width .22s ease;
    z-index: 10;
}
.learn-sidebar.collapsed { width: 0; min-width: 0; }
.sidebar-scroll { overflow-y: auto; flex: 1; scrollbar-width: thin; scrollbar-color: #e5e7eb transparent; }
.sidebar-scroll::-webkit-scrollbar { width: 3px; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }

.week-nav-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 16px;
    border-bottom: 1px solid #f3f4f6;
    text-decoration: none;
    transition: background .1s;
}
.week-nav-item:hover { background: #f9fafb; }
.week-nav-item.active { background: #eff6ff; }
.week-nav-item.locked { opacity: .45; cursor: default; pointer-events: none; }

.content-nav-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 16px 7px 40px;
    font-size: 12px;
    color: #6b7280;
    border-bottom: 1px solid #f9fafb;
}
.content-nav-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    border: 1.5px solid #d1d5db;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}
.content-nav-dot.done {
    background: #16a34a;
    border-color: #16a34a;
}

/* ── Main content area ────────────────────────────────────────────────── */
.learn-main {
    flex: 1;
    overflow-y: auto;
    background: #f9fafb;
}

/* ── Top bar ──────────────────────────────────────────────────────────── */
.learn-topbar {
    position: sticky;
    top: 0;
    z-index: 20;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding: 0 1.5rem;
    height: 52px;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

/* ── Content section ──────────────────────────────────────────────────── */
.content-section {
    background: #fff;
    border-bottom: 6px solid #f3f4f6;
    padding: 2rem 2.5rem 2.5rem;
    max-width: 820px;
}
.content-section + .content-section { border-top: none; }

.content-type-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #9ca3af;
    margin-bottom: .6rem;
}
.content-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.25rem;
    line-height: 1.4;
}

/* ── Article body ─────────────────────────────────────────────────────── */
.article-body {
    font-size: 15px;
    line-height: 1.85;
    color: #374151;
}
.article-body p  { margin-bottom: 1rem; }
.article-body h2 { font-size: 1.15rem; font-weight: 700; margin: 1.5rem 0 .6rem; color: #111827; }
.article-body h3 { font-size: 1rem; font-weight: 700; margin: 1.25rem 0 .5rem; }
.article-body ul, .article-body ol { padding-left: 1.4rem; margin-bottom: 1rem; }
.article-body li { margin-bottom: .4rem; }
.article-body pre {
    background: #1e293b; color: #e2e8f0;
    border-radius: 8px; padding: 1rem;
    font-size: 13px; overflow-x: auto; margin-bottom: 1rem;
}
.article-body blockquote {
    border-left: 3px solid #6366f1; padding-left: 1rem;
    color: #6b7280; margin: 1rem 0;
}
.article-body a { color: #2563eb; }

/* ── Video ────────────────────────────────────────────────────────────── */
.video-container {
    position: relative;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1rem;
}
.video-container iframe,
.video-container video {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    border: 0;
}
.video-watch-note {
    font-size: 0.78rem;
    color: #9ca3af;
    margin-top: .5rem;
}

/* ── PDF ──────────────────────────────────────────────────────────────── */
.pdf-frame {
    width: 100%;
    height: 65vh;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

/* ── External link card ───────────────────────────────────────────────── */
.ext-link-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f9fafb;
    text-decoration: none;
    color: #111827;
    transition: border-color .15s, background .15s;
}
.ext-link-card:hover { border-color: #6366f1; background: #fafafe; }

/* ── Completion indicator ─────────────────────────────────────────────── */
.completion-indicator {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-top: 1.25rem;
    font-size: .8rem;
    color: #9ca3af;
    height: 20px;
    transition: all .3s;
}
.completion-indicator.done { color: #16a34a; }
.completion-indicator .ci-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #d1d5db;
    transition: background .3s;
}
.completion-indicator.done .ci-dot { background: #16a34a; }

/* ── Assessment section ───────────────────────────────────────────────── */
.assessment-section {
    background: #fff;
    padding: 2rem 2.5rem 3rem;
    max-width: 820px;
}
.assessment-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f3f4f6;
}
.question-card {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    transition: border-color .15s;
}
.question-card.unanswered { border-color: #fca5a5; background: #fef2f2; }
.question-text { font-weight: 600; margin-bottom: 1rem; line-height: 1.5; color: #111827; }
.option-row {
    display: flex; align-items: center; gap: .75rem;
    padding: .65rem .85rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: .45rem;
    transition: border-color .12s, background .12s;
    user-select: none;
}
.option-row:hover { border-color: #a5b4fc; background: #f5f3ff; }
.option-row.selected { border-color: #4f46e5; background: #eef2ff; }
.option-row.correct  { border-color: #16a34a; background: #f0fdf4; cursor: default; }
.option-row.wrong    { border-color: #dc2626; background: #fef2f2; cursor: default; }
.option-radio {
    width: 16px; height: 16px; border-radius: 50%;
    border: 2px solid #d1d5db; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: border-color .12s;
}
.option-row.selected .option-radio { border-color: #4f46e5; }
.option-row.selected .option-radio::after {
    content: ''; width: 7px; height: 7px;
    background: #4f46e5; border-radius: 50%; display: block;
}
.option-row.correct .option-radio { border-color: #16a34a; background: #16a34a; }
.option-row.correct .option-radio::after { background: #fff; }
.option-row.wrong .option-radio   { border-color: #dc2626; background: #dc2626; }
.option-row.wrong .option-radio::after { background: #fff; }

/* ── Assessment result banners ────────────────────────────────────────── */
.result-banner {
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    display: flex; align-items: flex-start; gap: .85rem;
    margin-bottom: 1.5rem;
}
.result-banner.pass { background: #f0fdf4; border: 1px solid #bbf7d0; }
.result-banner.fail { background: #fef2f2; border: 1px solid #fecaca; }

/* ── Week navigation footer ───────────────────────────────────────────── */
.week-footer {
    background: #fff;
    border-top: 1px solid #e5e7eb;
    padding: 1.25rem 2.5rem;
    max-width: 820px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.nav-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .65rem 1.25rem;
    border-radius: 8px;
    font-size: .875rem; font-weight: 600;
    text-decoration: none;
    transition: background .15s;
}
.nav-btn-ghost { color: #6b7280; border: 1px solid #e5e7eb; background: #fff; }
.nav-btn-ghost:hover { background: #f9fafb; }
.nav-btn-primary { background: #2563eb; color: #fff; border: 1px solid #2563eb; }
.nav-btn-primary:hover { background: #1d4ed8; }
.nav-btn:disabled,
.nav-btn[aria-disabled="true"] {
    opacity: .35; pointer-events: none;
}

/* ── Progress pill ────────────────────────────────────────────────────── */
.prog-pill {
    font-size: 11px; font-weight: 700;
    background: #eff6ff; color: #2563eb;
    padding: 2px 9px; border-radius: 20px;
}

@media (max-width: 768px) {
    .learn-sidebar {
        position: fixed; left: 0; top: 60px; bottom: 0;
        box-shadow: 4px 0 20px rgba(0,0,0,.08);
    }
    .learn-sidebar.collapsed { width: 0; min-width: 0; }
    .content-section,
    .assessment-section,
    .week-footer { padding-left: 1.25rem; padding-right: 1.25rem; }
}
</style>
@endpush

@section('content')

{{-- Server data for JS -------------------------------------------------- --}}
<script id="js-enrollment-id"  type="application/json">{{ $enrollmentId }}</script>
<script id="js-week-id"        type="application/json">{{ $week->id }}</script>
<script id="js-has-assessment" type="application/json">{{ $week->assessment ? 'true' : 'false' }}</script>
<script id="js-assessment-passed" type="application/json">{{ $assessmentPassed ? 'true' : 'false' }}</script>
<script id="js-content-ids" type="application/json">
    @json($contents->pluck('id'))
</script>
<script id="js-content-completion" type="application/json">
    @json($contents->mapWithKeys(fn ($c) => [
        $c->id => (bool) ($c->contentProgress->first()?->is_completed ?? false)
    ]))
</script>

<div class="learn-wrap">

    {{-- ══════════ SIDEBAR ══════════ --}}
    <aside class="learn-sidebar" id="learn-sidebar">
        <div class="sidebar-scroll">

            {{-- Program header --}}
            <div style="padding: 1rem 1rem .75rem; border-bottom: 1px solid #f3f4f6;">
                <p style="font-size: .7rem; font-weight: 700; text-transform: uppercase;
                           letter-spacing: .06em; color: #9ca3af; margin-bottom: .5rem;">
                    {{ $enrollment->program->name }}
                </p>
                <div style="display: flex; align-items: center; gap: .5rem;">
                    <div style="flex: 1; height: 5px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $stats['overall_progress'] }}%;
                                    background: #2563eb; border-radius: 3px; transition: width .5s;"></div>
                    </div>
                    <span class="prog-pill">{{ $stats['overall_progress'] }}%</span>
                </div>
                <p style="font-size: .7rem; color: #9ca3af; margin-top: .3rem;">
                    {{ $stats['completed_weeks'] }} / {{ $stats['total_weeks'] }} weeks
                </p>
            </div>

            {{-- Week + content list --}}
            @foreach($allWeekProgress as $wp)
            @php
                $wk        = $wp->moduleWeek;
                $isLocked  = !$wp->is_unlocked;
                $isDone    = $wp->is_completed;
                $isCurrent = $wk->id === $week->id;
                $weekUrl   = route('learner.learning.week', [$enrollmentId, $wk->id]);
            @endphp

            {{-- Week row --}}
            @if($isLocked)
            <div class="week-nav-item locked">
            @else
            <a href="{{ $weekUrl }}" class="week-nav-item {{ $isCurrent ? 'active' : '' }}">
            @endif
                {{-- Status dot --}}
                @if($isLocked)
                <div style="width:16px;height:16px;border-radius:50%;border:1.5px solid #d1d5db;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </div>
                @elseif($isDone)
                <div style="width:16px;height:16px;border-radius:50%;background:#16a34a;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                    <svg width="9" height="9" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                @else
                <div style="width:16px;height:16px;border-radius:50%;
                            border:2px solid {{ $isCurrent ? '#2563eb' : '#d1d5db' }};
                            flex-shrink:0;margin-top:1px;"></div>
                @endif

                <div style="min-width:0;flex:1;">
                    <p style="font-size:.72rem;color:#9ca3af;margin-bottom:.1rem;">
                        {{ $wk->programModule->title ?? '' }} · Week {{ $wk->week_number }}
                    </p>
                    <p style="font-size:.82rem;font-weight:600;color:{{ $isCurrent ? '#1d4ed8' : '#374151' }};
                               line-height:1.35;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $wk->title }}
                    </p>
                </div>
            @if($isLocked)
            </div>
            @else
            </a>
            @endif

            {{-- Per-item progress (current week only) --}}
            @if($isCurrent)
            @php
                $weekContents = $wk->contents()->with(['contentProgress' => fn($q) =>
                    $q->where('user_id', auth()->id())->where('enrollment_id', $enrollmentId)
                ])->orderBy('order')->get();
            @endphp
            @foreach($weekContents as $wc)
            @php $wcDone = (bool) ($wc->contentProgress->first()?->is_completed ?? false); @endphp
            <div class="content-nav-item" id="sidebar-dot-{{ $wc->id }}">
                <div class="content-nav-dot {{ $wcDone ? 'done' : '' }}" id="dot-{{ $wc->id }}">
                    @if($wcDone)
                    <svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">{{ $wc->title }}</span>
                <span style="font-size:.65rem;color:#d1d5db;text-transform:uppercase;flex-shrink:0;">{{ $wc->content_type }}</span>
            </div>
            @endforeach

            {{-- Assessment dot in sidebar --}}
            @if($week->assessment)
            <div class="content-nav-item" id="sidebar-dot-assessment">
                <div class="content-nav-dot {{ $assessmentPassed ? 'done' : '' }}" id="dot-assessment">
                    @if($assessmentPassed)
                    <svg width="8" height="8" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <span>Assessment</span>
                <span style="font-size:.65rem;color:#d1d5db;flex-shrink:0;">quiz</span>
            </div>
            @endif
            @endif
            {{-- end isCurrent --}}

            @endforeach

        </div>
    </aside>

    {{-- ══════════ MAIN ══════════ --}}
    <main class="learn-main" id="learn-main">

        {{-- Top bar --}}
        <div class="learn-topbar">
            <button onclick="toggleSidebar()"
                style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;
                       border-radius:6px;border:none;background:transparent;cursor:pointer;flex-shrink:0;"
                title="Toggle navigation">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            </button>

            <div style="flex:1;min-width:0;font-size:.85rem;color:#6b7280;display:flex;align-items:center;gap:.4rem;overflow:hidden;">
                <a href="{{ route('learner.my-learning') }}"
                   style="color:#9ca3af;text-decoration:none;white-space:nowrap;display:none;" class="sm-show">
                    My Learning
                </a>
                <span style="color:#d1d5db;display:none;" class="sm-show">›</span>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;color:#374151;">
                    Week {{ $week->week_number }}: {{ $week->title }}
                </span>
            </div>

            {{-- Week progress indicator --}}
            <div style="flex-shrink:0;display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#9ca3af;">
                <span id="topbar-done-count">{{ $contents->where('contentProgress.0.is_completed', true)->count() }}</span>/<span>{{ $contents->count() }}{{ $week->assessment ? '+quiz' : '' }}</span>
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
        @php $isDone = (bool) ($content->contentProgress->first()?->is_completed ?? false); @endphp

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
                &nbsp;·&nbsp; {{ $i + 1 }} of {{ $contents->count() }}{{ $week->assessment ? ' + assessment' : '' }}
            </p>
            <h2 class="content-title">{{ $content->title }}</h2>

            {{-- ── Video ── --}}
            @if($content->content_type === 'video')
            <div class="video-container" id="player-wrap-{{ $content->id }}">
                {{-- Populated by JS after DOM ready --}}
            </div>
            <p class="video-watch-note" id="video-note-{{ $content->id }}">
                @if($isDone)
                    Watched ✓
                @else
                    Watch to the end to mark this lesson complete.
                @endif
            </p>

            {{-- ── Article ── --}}
            @elseif($content->content_type === 'article')
            <div class="article-body">
                {!! $content->text_content !!}
            </div>
            {{-- Sentinel: entering viewport at bottom = read to end --}}
            <div class="scroll-sentinel" data-content-id="{{ $content->id }}"></div>

            {{-- ── PDF ── --}}
            @elseif($content->content_type === 'pdf')
            <iframe class="pdf-frame"
                    src="{{ $content->file_url }}"
                    title="{{ $content->title }}"></iframe>
            <div style="margin-top:.75rem;display:flex;gap:1rem;align-items:center;">
                <a href="{{ $content->file_url }}" target="_blank"
                   style="font-size:.8rem;color:#2563eb;text-decoration:none;">
                    Open in new tab ↗
                </a>
            </div>
            <div class="scroll-sentinel" data-content-id="{{ $content->id }}"></div>

            {{-- ── External link ── --}}
            @elseif($content->content_type === 'link')
            <a href="{{ $content->external_url }}" target="_blank" rel="noopener"
               class="ext-link-card" onclick="markContentDone({{ $content->id }})">
                <div style="width:40px;height:40px;background:#eff6ff;border-radius:8px;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                        <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:.9rem;font-weight:600;margin-bottom:.2rem;">{{ $content->title }}</p>
                    <p style="font-size:.75rem;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $content->external_url }}
                    </p>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
            <div class="scroll-sentinel" data-content-id="{{ $content->id }}" style="margin-top:.5rem;"></div>
            @endif

            {{-- Per-item completion indicator --}}
            <div class="completion-indicator {{ $isDone ? 'done' : '' }}" id="ci-{{ $content->id }}">
                <div class="ci-dot"></div>
                <span id="ci-label-{{ $content->id }}">{{ $isDone ? 'Completed' : 'Not yet completed' }}</span>
            </div>

        </section>
        @endforeach

        {{-- ════ ASSESSMENT SECTION ════ --}}
        @if($week->assessment)
        @php $assessment = $week->assessment; @endphp
        <section class="assessment-section" id="assessment-section">

            <div class="assessment-header">
                <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                           color:#6366f1;margin-bottom:.35rem;">Week Assessment</p>
                <h2 style="font-size:1.15rem;font-weight:700;color:#111827;margin-bottom:.75rem;">
                    {{ $assessment->title }}
                </h2>
                <div style="display:flex;gap:1.5rem;font-size:.8rem;color:#6b7280;flex-wrap:wrap;">
                    <span>{{ $assessment->questions->count() }} question{{ $assessment->questions->count() !== 1 ? 's' : '' }}</span>
                    <span>Pass mark: {{ $assessment->pass_percentage }}%</span>
                    @if($assessment->time_limit_minutes)
                    <span>Time limit: {{ $assessment->time_limit_minutes }} min</span>
                    @endif
                </div>
            </div>

            {{-- Already passed: show result and skip form --}}
            @if($assessmentPassed)
            <div class="result-banner pass" id="assessment-result-banner">
                <div style="width:32px;height:32px;border-radius:50%;background:#16a34a;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <p style="font-weight:700;color:#166534;">
                        Passed · {{ number_format($latestAttempt->percentage, 0) }}%
                    </p>
                    <p style="font-size:.8rem;color:#166534;margin-top:.2rem;">
                        Well done. You can retake this assessment to improve your score.
                    </p>
                </div>
                <button onclick="showAssessmentForm()" style="margin-left:auto;font-size:.78rem;
                    color:#166534;background:none;border:1px solid #bbf7d0;border-radius:6px;
                    padding:.35rem .75rem;cursor:pointer;white-space:nowrap;">
                    Retake
                </button>
            </div>
            @else
            <div class="result-banner" id="assessment-result-banner" style="display:none;"></div>
            @endif

            {{-- Question form (hidden if already passed; shown on retake) --}}
            <div id="assessment-form-area" style="{{ $assessmentPassed ? 'display:none;' : '' }}">

                @foreach($assessment->questions as $qi => $question)
                <div class="question-card" id="qcard-{{ $question->id }}" data-qid="{{ $question->id }}">
                    <p class="question-text">
                        <span style="font-size:.75rem;font-weight:700;color:#9ca3af;margin-right:.5rem;">
                            {{ $qi + 1 }}.
                        </span>
                        {{ $question->question_text }}
                    </p>

                    @php
                        $opts = is_array($question->options)
                            ? $question->options
                            : (json_decode($question->options, true) ?? []);
                    @endphp

                    <div id="opts-{{ $question->id }}">
                        @foreach($opts as $opt)
                        <div class="option-row"
                             data-qid="{{ $question->id }}"
                             data-value="{{ $opt }}"
                             onclick="selectOption(this)">
                            <div class="option-radio"></div>
                            <span style="font-size:.875rem;">{{ $opt }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div id="unanswered-warning" style="display:none;margin-bottom:1rem;padding:.85rem 1rem;
                     background:#fffbeb;border:1px solid #fde68a;border-radius:8px;
                     font-size:.82rem;color:#92400e;">
                    Please answer all questions before submitting.
                </div>

                <button onclick="submitAssessment()" id="submit-btn"
                    style="background:#4f46e5;color:#fff;border:none;border-radius:10px;
                           padding:.85rem 2rem;font-size:.9rem;font-weight:700;cursor:pointer;
                           transition:background .15s;">
                    Submit Assessment
                </button>
            </div>

        </section>
        @endif

        {{-- ════ WEEK FOOTER (navigation) ════ --}}
        <div class="week-footer">
            <div>
                @if($prevWeekId)
                <a href="{{ route('learner.learning.week', [$enrollmentId, $prevWeekId]) }}"
                   class="nav-btn nav-btn-ghost">
                    ← Previous
                </a>
                @else
                <span></span>
                @endif
            </div>

            {{-- Next: always rendered; JS controls visibility --}}
            <div id="next-container"
                 style="{{ ($week->assessment && !$assessmentPassed) ? 'display:none;' : '' }}">
                @if($nextWeekId)
                <a href="{{ route('learner.learning.week', [$enrollmentId, $nextWeekId]) }}"
                   class="nav-btn nav-btn-primary" id="next-week-btn">
                    Next Week →
                </a>
                @else
                {{-- Last week complete --}}
                <a href="{{ route('learner.graduation.status', $enrollment->id) }}"
                   class="nav-btn nav-btn-primary" style="background:#16a34a;border-color:#16a34a;">
                    Complete Course →
                </a>
                @endif
            </div>
        </div>

    </main>
</div>
@endsection

@push('scripts')
<script>
// ── Server data ────────────────────────────────────────────────────────────
const ENROLLMENT_ID    = JSON.parse(document.getElementById('js-enrollment-id').textContent);
const WEEK_ID          = JSON.parse(document.getElementById('js-week-id').textContent);
const HAS_ASSESSMENT   = JSON.parse(document.getElementById('js-has-assessment').textContent);
const CSRF             = document.querySelector('meta[name="csrf-token"]').content;
const CONTENT_IDS      = JSON.parse(document.getElementById('js-content-ids').textContent);

// Completion map — seeded from server (already-completed items pre-filled)
const completed = JSON.parse(document.getElementById('js-content-completion').textContent);
let assessmentPassed   = JSON.parse(document.getElementById('js-assessment-passed').textContent);

// Prevent sentinel fires for the first 1.5s after page load
const PAGE_LOAD_TIME = Date.now();

// ── Sidebar toggle ─────────────────────────────────────────────────────────
function toggleSidebar() {
    document.getElementById('learn-sidebar').classList.toggle('collapsed');
}

// ── Sidebar dot update ─────────────────────────────────────────────────────
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

// ── Per-item completion indicator ──────────────────────────────────────────
function markContentIndicatorDone(contentId) {
    const ci = document.getElementById('ci-' + contentId);
    if (ci) {
        ci.classList.add('done');
        const label = document.getElementById('ci-label-' + contentId);
        if (label) label.textContent = 'Completed';
    }
}

// ── Top bar count ──────────────────────────────────────────────────────────
function updateTopbarCount() {
    const doneCount = CONTENT_IDS.filter(id => completed[id]).length
        + (assessmentPassed ? 1 : 0);
    const el = document.getElementById('topbar-done-count');
    if (el) el.textContent = doneCount;
}

// ── Mark content complete (AJAX) ───────────────────────────────────────────
function markContentDone(contentId, silent) {
    if (completed[contentId]) return; // already done — no duplicate calls
    completed[contentId] = true;

    // Optimistic UI
    markSidebarDone(contentId);
    markContentIndicatorDone(contentId);
    updateTopbarCount();
    checkWeekCompletion();

    // Fire AJAX
    fetch('/learner/learning/content/' + contentId + '/complete', {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body:    JSON.stringify({}),
    }).catch(() => {}); // silent fail — optimistic update already applied
}

// ── Check if all content done → show Next (no-assessment weeks) ────────────
function checkWeekCompletion() {
    const allContentDone = CONTENT_IDS.every(id => completed[id]);
    if (!HAS_ASSESSMENT && allContentDone) {
        const nc = document.getElementById('next-container');
        if (nc) nc.style.display = '';
    }
}

// ── IntersectionObserver for scroll-to-end completion ─────────────────────
// Fires for articles, PDFs, and links when their sentinel div enters the viewport
const sentinelObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        if (Date.now() - PAGE_LOAD_TIME < 1500) return; // ignore on fresh load

        const contentId = parseInt(entry.target.dataset.contentId);
        if (!completed[contentId]) {
            sentinelObserver.unobserve(entry.target); // fire once only
            markContentDone(contentId);
        }
    });
}, {
    root:       document.getElementById('learn-main'),
    rootMargin: '0px',
    threshold:  0.2,
});

// Observe sentinels for non-video items that aren't already complete
document.querySelectorAll('.scroll-sentinel').forEach(el => {
    const id = parseInt(el.dataset.contentId);
    if (!completed[id]) {
        sentinelObserver.observe(el);
    }
});

// ── Video setup ────────────────────────────────────────────────────────────
// Parse a URL into { type: 'youtube'|'file'|'iframe', embedUrl, videoId? }
function parseVideoUrl(url) {
    if (!url) return { type: 'none', embedUrl: '' };
    const yt = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (yt) return {
        type:     'youtube',
        videoId:  yt[1],
        embedUrl: 'https://www.youtube.com/embed/' + yt[1] + '?enablejsapi=1&rel=0&modestbranding=1',
    };
    const vm = url.match(/vimeo\.com\/(\d+)/);
    if (vm) return { type: 'iframe', embedUrl: 'https://player.vimeo.com/video/' + vm[1] };
    const gd = url.match(/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/);
    if (gd) return { type: 'iframe', embedUrl: 'https://drive.google.com/file/d/' + gd[1] + '/preview' };
    if (url.match(/\.(mp4|webm|ogg|mov)(\?.*)?$/i)) return { type: 'file', embedUrl: url };
    return { type: 'iframe', embedUrl: url };
}

// YT API bootstrap
let _ytReady = false;
const _ytQueue = [];
function ensureYT() {
    if (_ytReady || document.querySelector('script[src*="youtube.com/iframe_api"]')) return;
    const s = document.createElement('script');
    s.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(s);
}
window.onYouTubeIframeAPIReady = function () {
    _ytReady = true;
    _ytQueue.forEach(fn => fn());
    _ytQueue.length = 0;
};

function initVideoSection(section) {
    const contentId = parseInt(section.dataset.contentId);
    const url       = section.dataset.videoUrl;
    const duration  = parseFloat(section.dataset.videoDuration) || 0;
    const wrap      = document.getElementById('player-wrap-' + contentId);
    const note      = document.getElementById('video-note-' + contentId);
    if (!wrap || completed[contentId]) return;

    const parsed = parseVideoUrl(url);

    const onComplete = () => {
        if (!completed[contentId]) {
            markContentDone(contentId);
            if (note) note.textContent = 'Watched ✓';
        }
    };

    if (parsed.type === 'file') {
        const vid = document.createElement('video');
        vid.controls = true;
        vid.preload  = 'metadata';
        vid.style    = 'position:absolute;inset:0;width:100%;height:100%;';
        vid.innerHTML = '<source src="' + parsed.embedUrl + '" type="video/mp4">';
        wrap.appendChild(vid);

        vid.addEventListener('timeupdate', function handler() {
            if (vid.duration > 0 && (vid.currentTime / vid.duration) >= 0.9) {
                vid.removeEventListener('timeupdate', handler);
                onComplete();
            }
        });

    } else if (parsed.type === 'youtube') {
        const div  = document.createElement('div');
        div.id     = 'yt-' + contentId;
        div.style  = 'position:absolute;inset:0;width:100%;height:100%;';
        wrap.appendChild(div);

        const init = () => {
            const player = new YT.Player(div.id, {
                videoId:    parsed.videoId,
                playerVars: { rel: 0, modestbranding: 1 },
                events: {
                    onReady: () => {
                        const interval = setInterval(() => {
                            try {
                                const dur = player.getDuration();
                                const cur = player.getCurrentTime();
                                if (dur > 0 && (cur / dur) >= 0.9) {
                                    clearInterval(interval);
                                    onComplete();
                                }
                            } catch(e) {}
                        }, 3000);
                    },
                    onStateChange: e => {
                        if (e.data === YT.PlayerState.ENDED) onComplete();
                    },
                },
            });
        };

        ensureYT();
        _ytReady ? init() : _ytQueue.push(init);

    } else {
        // Generic iframe (Vimeo, GDrive, Loom, etc.) — time-based gate
        const fr   = document.createElement('iframe');
        fr.src     = parsed.embedUrl;
        fr.allow   = 'autoplay; fullscreen; picture-in-picture';
        fr.style   = 'position:absolute;inset:0;width:100%;height:100%;border:0;';
        wrap.appendChild(fr);

        // Approximate: gate at 90% of stated duration, or 5 min if unknown
        const gateSecs = duration ? Math.round(duration * 60 * 0.9) : 300;
        let elapsed    = 0;
        let visible    = false;

        const visObs = new IntersectionObserver(([e]) => { visible = e.isIntersecting; });
        visObs.observe(fr);

        const ticker = setInterval(() => {
            if (visible) {
                elapsed++;
                if (elapsed >= gateSecs) {
                    clearInterval(ticker);
                    visObs.disconnect();
                    onComplete();
                }
            }
        }, 1000);
    }
}

// Initialise all video sections on this page
document.querySelectorAll('section[data-type="video"]').forEach(initVideoSection);

// Link items: mark complete when user clicks through
// (sentinel also fires on scroll, handled above)

// ── Assessment ─────────────────────────────────────────────────────────────
const answers = {};
let currentAttemptId = null;

function selectOption(el) {
    const qid = el.dataset.qid;
    // Deselect siblings
    document.querySelectorAll(`.option-row[data-qid="${qid}"]`)
        .forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    answers[qid] = el.dataset.value;
    // Clear unanswered highlight on this card
    const card = document.getElementById('qcard-' + qid);
    if (card) card.classList.remove('unanswered');
}

function submitAssessment() {
    if (!currentAttemptId) {
        // Create attempt first, then re-call
        fetch('/learner/assessments/{{ $week->assessment?->id ?? 0 }}/attempt', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body:    JSON.stringify({ enrollment_id: ENROLLMENT_ID }),
        })
        .then(r => r.json())
        .then(data => {
            currentAttemptId = data.attempt_id || data.attempt?.id;
            if (currentAttemptId) submitAssessment();
            else alert('Could not start attempt. Please try again.');
        });
        return;
    }

    // Validate all questions answered
    const questions = document.querySelectorAll('.question-card');
    let allAnswered = true;
    questions.forEach(card => {
        const qid = card.dataset.qid;
        if (!answers[qid]) {
            card.classList.add('unanswered');
            allAnswered = false;
        }
    });

    if (!allAnswered) {
        document.getElementById('unanswered-warning').style.display = '';
        const firstUnanswered = document.querySelector('.question-card.unanswered');
        if (firstUnanswered) firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    document.getElementById('unanswered-warning').style.display = 'none';
    const btn = document.getElementById('submit-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Submitting…'; }

    const payload = Array.from(questions).map(card => ({
        question_id: card.dataset.qid,
        answer:      answers[card.dataset.qid],
    }));

    fetch('/learner/attempts/' + currentAttemptId + '/submit', {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body:    JSON.stringify({ answers: payload }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success && data.score === undefined) {
            if (btn) { btn.disabled = false; btn.textContent = 'Submit Assessment'; }
            alert(data.message || 'Submission failed. Please try again.');
            return;
        }
        showAssessmentResult(data.score, data.passed ?? (data.score >= {{ $week->assessment?->pass_percentage ?? 70 }}));
    })
    .catch(() => {
        if (btn) { btn.disabled = false; btn.textContent = 'Submit Assessment'; }
        alert('Submission failed. Please try again.');
    });
}

function showAssessmentResult(score, passed) {
    const banner = document.getElementById('assessment-result-banner');
    const form   = document.getElementById('assessment-form-area');

    if (passed) {
        assessmentPassed = true;
        markAssessmentSidebarDone();
        updateTopbarCount();

        // Show Next button
        const nc = document.getElementById('next-container');
        if (nc) nc.style.display = '';

        banner.className = 'result-banner pass';
        banner.style.display = '';
        banner.innerHTML = `
            <div style="width:32px;height:32px;border-radius:50%;background:#16a34a;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="white">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <p style="font-weight:700;color:#166534;">Passed · ${score}%</p>
                <p style="font-size:.8rem;color:#166534;margin-top:.2rem;">
                    Great work. Scroll down to continue to the next week.
                </p>
            </div>`;

        if (form) form.style.display = 'none';

        // Smooth scroll to the footer
        setTimeout(() => {
            document.querySelector('.week-footer')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 400);

    } else {
        // Failed — show result, allow retry
        const passMark = {{ $week->assessment?->pass_percentage ?? 70 }};
        banner.className = 'result-banner fail';
        banner.style.display = '';
        banner.innerHTML = `
            <div style="width:32px;height:32px;border-radius:50%;background:#dc2626;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="white">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div style="flex:1;">
                <p style="font-weight:700;color:#991b1b;">Score: ${score}% — below the ${passMark}% pass mark</p>
                <p style="font-size:.8rem;color:#991b1b;margin-top:.2rem;">Review the material above and try again.</p>
            </div>
            <button onclick="resetAssessmentForm()"
                style="margin-left:auto;font-size:.78rem;color:#991b1b;background:none;
                       border:1px solid #fca5a5;border-radius:6px;padding:.35rem .75rem;
                       cursor:pointer;white-space:nowrap;flex-shrink:0;">
                Try Again
            </button>`;

        currentAttemptId = null;
        const btn = document.getElementById('submit-btn');
        if (btn) { btn.disabled = false; btn.textContent = 'Submit Assessment'; }
    }
}

function resetAssessmentForm() {
    // Clear all selections and reset state
    document.querySelectorAll('.option-row').forEach(o => o.classList.remove('selected', 'correct', 'wrong'));
    document.querySelectorAll('.question-card').forEach(c => c.classList.remove('unanswered'));
    Object.keys(answers).forEach(k => delete answers[k]);
    currentAttemptId = null;
    document.getElementById('unanswered-warning').style.display = 'none';
    const btn = document.getElementById('submit-btn');
    if (btn) { btn.disabled = false; btn.textContent = 'Submit Assessment'; }
    const banner = document.getElementById('assessment-result-banner');
    if (banner) banner.style.display = 'none';
    // Scroll to top of assessment
    document.getElementById('assessment-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showAssessmentForm() {
    const form = document.getElementById('assessment-form-area');
    if (form) form.style.display = '';
    const banner = document.getElementById('assessment-result-banner');
    if (banner) banner.style.display = 'none';
    resetAssessmentForm();
}

// ── Init ───────────────────────────────────────────────────────────────────
updateTopbarCount();
checkWeekCompletion();
</script>
@endpush