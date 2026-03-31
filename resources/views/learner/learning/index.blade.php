@extends('layouts.learner')

@section('title', $enrollment->program->name . ' — ' . $currentWeek->title)

@push('styles')
<style>
    /* ── Layout ─────────────────────────────────────────────────────────────── */
    .player-layout {
        display: flex;
        height: calc(100vh - 60px); /* subtract nav height */
        overflow: hidden;
    }

    /* ── Sidebar ─────────────────────────────────────────────────────────────── */
    .sidebar {
        width: 300px;
        min-width: 300px;
        background: #fff;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: width 0.25s ease, min-width 0.25s ease;
    }
    .sidebar.collapsed {
        width: 0;
        min-width: 0;
    }
    .sidebar-inner {
        overflow-y: auto;
        flex: 1;
        scrollbar-width: thin;
        scrollbar-color: #e2e8f0 transparent;
    }
    .sidebar-inner::-webkit-scrollbar { width: 4px; }
    .sidebar-inner::-webkit-scrollbar-track { background: transparent; }
    .sidebar-inner::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

    /* ── Content panel ───────────────────────────────────────────────────────── */
    .content-panel {
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        background: #f7f9fc;
    }

    /* ── Week row in sidebar ─────────────────────────────────────────────────── */
    .week-row {
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        user-select: none;
    }
    .week-row:hover { background: #f8fafc; }
    .week-row.open   { background: #f1f5f9; }

    /* ── Content item in sidebar ─────────────────────────────────────────────── */
    .content-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 9px 16px 9px 36px;
        cursor: pointer;
        transition: background 0.12s;
        border-left: 3px solid transparent;
    }
    .content-item:hover  { background: #f8fafc; }
    .content-item.active {
        background: #eff6ff;
        border-left-color: #2563eb;
    }
    .content-item.done .item-title {
        text-decoration: line-through;
        color: #94a3b8;
    }

    /* ── Video wrapper ───────────────────────────────────────────────────────── */
    .video-wrapper {
        position: relative;
        background: #000;
        width: 100%;
        aspect-ratio: 16/9;
        max-height: 60vh;
    }
    .video-wrapper video,
    .video-wrapper iframe {
        width: 100%;
        height: 100%;
        display: block;
    }

    /* ── Tabs ────────────────────────────────────────────────────────────────── */
    .tab-bar { border-bottom: 1px solid #e2e8f0; background: #fff; }
    .tab-btn {
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        border-bottom: 2px solid transparent;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        cursor: pointer;
        transition: color 0.12s, border-color 0.12s;
    }
    .tab-btn:hover { color: #1d4ed8; }
    .tab-btn.active { color: #1d4ed8; border-bottom-color: #1d4ed8; }

    /* ── Article content ─────────────────────────────────────────────────────── */
    .article-body {
        font-size: 15px;
        line-height: 1.8;
        color: #334155;
        max-width: 740px;
    }
    .article-body h2 { font-size: 20px; font-weight: 800; margin: 24px 0 10px; color: #0f172a; }
    .article-body h3 { font-size: 17px; font-weight: 700; margin: 20px 0 8px; color: #1e293b; }
    .article-body p  { margin-bottom: 14px; }
    .article-body ul, .article-body ol { padding-left: 20px; margin-bottom: 14px; }
    .article-body li { margin-bottom: 6px; }
    .article-body pre {
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        overflow-x: auto;
        font-size: 13px;
        margin-bottom: 16px;
    }
    .article-body blockquote {
        border-left: 3px solid #2563eb;
        padding-left: 16px;
        color: #64748b;
        margin: 16px 0;
    }

    /* ── Complete button ─────────────────────────────────────────────────────── */
    .complete-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #16a34a;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        padding: 12px 24px;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
    }
    .complete-btn:hover   { background: #15803d; }
    .complete-btn:active  { transform: scale(0.98); }
    .complete-btn.done    { background: #64748b; cursor: default; }
    .complete-btn.loading { opacity: 0.7; pointer-events: none; }

    /* ── Progress pill ───────────────────────────────────────────────────────── */
    .progress-pill {
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
    }

    /* ── Transcript placeholder ─────────────────────────────────────────────── */
    .transcript-line {
        padding: 6px 0;
        cursor: pointer;
        transition: color 0.1s;
        border-radius: 4px;
    }
    .transcript-line:hover { color: #1d4ed8; }
    .transcript-line.highlight { color: #1d4ed8; font-weight: 600; }

    /* ── Mobile toggle ───────────────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .sidebar { position: fixed; left: 0; top: 60px; bottom: 0; z-index: 40; box-shadow: 4px 0 20px rgba(0,0,0,0.1); }
        .sidebar.collapsed { transform: translateX(-100%); width: 300px; min-width: 300px; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 39; }
        .sidebar-overlay.show { display: block; }
    }

    /* ── Assessment panel ─────────────────────────────────────────────────────── */
    .quiz-option {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: border-color 0.12s, background 0.12s;
        user-select: none;
    }
    .quiz-option:hover:not(.locked)       { border-color: #818cf8; background: #f5f3ff; }
    .quiz-option.selected:not(.locked)    { border-color: #4f46e5; background: #eef2ff; }
    .quiz-option.correct                  { border-color: #16a34a; background: #f0fdf4; }
    .quiz-option.incorrect                { border-color: #dc2626; background: #fef2f2; }
    .quiz-option.locked                   { cursor: default; }

    .quiz-radio {
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1px;
        transition: border-color 0.12s;
    }
    .quiz-option.selected .quiz-radio     { border-color: #4f46e5; }
    .quiz-option.selected .quiz-radio::after {
        content: '';
        width: 8px;
        height: 8px;
        background: #4f46e5;
        border-radius: 50%;
        display: block;
    }
    .quiz-option.correct .quiz-radio  { border-color: #16a34a; background: #16a34a; }
    .quiz-option.correct .quiz-radio::after  { background: #fff; }
    .quiz-option.incorrect .quiz-radio { border-color: #dc2626; background: #dc2626; }
    .quiz-option.incorrect .quiz-radio::after { background: #fff; }

    .quiz-progress-bar {
        height: 4px;
        background: #e2e8f0;
        border-radius: 9999px;
        overflow: hidden;
    }
    .quiz-progress-fill {
        height: 100%;
        background: linear-gradient(to right, #4f46e5, #7c3aed);
        border-radius: 9999px;
        transition: width 0.3s ease;
    }

    /* Timer warning */
    .timer-warning { color: #dc2626 !important; animation: pulse 1s infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }

    /* Results score ring */
    .score-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; }
    .score-ring svg { transform: rotate(-90deg); }
    .score-ring .score-text {
        position: absolute;
        font-size: 28px;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
    }

    /* Skeleton loader for week content swap */
    .skeleton {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: skeleton-shine 1.4s infinite;
        border-radius: 8px;
    }
    @keyframes skeleton-shine {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('content')

{{-- Hidden data for JS --}}
<script id="contents-data" type="application/json">@json($contentsJson)</script>
<script id="enrollment-id" type="application/json">{{ $enrollment->id }}</script>
<script id="current-week-id" type="application/json">{{ $currentWeek->id }}</script>

{{-- Mobile sidebar overlay --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="player-layout">

    {{-- ════════════════════════════════════════════════════════════════════
         SIDEBAR — Course navigation
    ════════════════════════════════════════════════════════════════════════ --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-inner">

            {{-- Program name --}}
            <div class="px-4 pt-5 pb-3 border-b border-slate-100">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">
                    {{ $enrollment->program->name }}
                </p>
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full prog-fill"
                             style="width: {{ $stats['overall_progress'] }}%"></div>
                    </div>
                    <span class="progress-pill">{{ $stats['overall_progress'] }}%</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    {{ $stats['completed_weeks'] }} / {{ $stats['total_weeks'] }} weeks complete
                </p>
            </div>

            {{-- Week + content tree --}}
            @foreach($allWeekProgress as $wp)
            @php
                $week       = $wp->moduleWeek;
                $isLocked   = !$wp->is_unlocked;
                $isDone     = $wp->is_completed;
                $isCurrent  = $week->id === $currentWeek->id;
                $weekContents = $week->contents()
                    ->with(['contentProgress' => fn($q) => $q->where('user_id', auth()->id())->where('enrollment_id', $enrollment->id)])
                    ->orderBy('order')
                    ->get();
            @endphp

            <div class="week-row {{ $isCurrent ? 'open' : '' }}"
                 id="week-row-{{ $week->id }}"
                 onclick="toggleWeek({{ $week->id }}, {{ $isLocked ? 'true' : 'false' }})">

                <div class="flex items-center gap-3 px-4 py-3.5">
                    {{-- Status icon --}}
                    @if($isLocked)
                        <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    @elseif($isDone)
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <div class="w-4 h-4 rounded-full border-2 {{ $isCurrent ? 'border-blue-600' : 'border-slate-300' }} flex-shrink-0"></div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 leading-snug truncate">
                            {{ $week->programModule->title ?? '' }} · Week {{ $week->week_number }}
                        </p>
                        <p class="text-[11px] text-slate-400 truncate">{{ $week->title }}</p>
                    </div>

                    {{-- Chevron --}}
                    @if(!$isLocked)
                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 chevron-{{ $week->id }} {{ $isCurrent ? 'rotate-180' : '' }} transition-transform"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                    @endif
                </div>
            </div>

            {{-- Content list --}}
            <div id="week-contents-{{ $week->id }}" class="{{ $isCurrent ? '' : 'hidden' }}">
                @foreach($weekContents as $content)
                @php
                    $cp       = $content->contentProgress->first();
                    $done     = $cp && $cp->is_completed;
                    $typeIcon = match($content->content_type) {
                        'video'    => '▶',
                        'article'  => '📄',
                        'pdf'      => '📎',
                        'quiz'     => '✏️',
                        default    => '•',
                    };
                @endphp
                <div class="content-item {{ $done ? 'done' : '' }}"
                     id="sidebar-item-{{ $content->id }}"
                     onclick="loadContent({{ $content->id }})">

                    {{-- Completion circle --}}
                    <div class="w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center
                                {{ $done ? 'bg-green-500' : 'border border-slate-300' }}">
                        @if($done)
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="item-title text-[12px] text-slate-700 leading-snug line-clamp-2">
                            {{ $content->title }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                            <span>{{ $typeIcon }}</span>
                            <span>{{ ucfirst($content->content_type) }}</span>
                            @if($content->video_duration_minutes)
                            <span>· {{ $content->video_duration_minutes }} min</span>
                            @endif
                        </p>
                    </div>
                </div>
                @endforeach

                {{-- Assessment row (if exists) --}}
                @if($week->assessment)
                @php $submitted = $week->assessment->attempts->isNotEmpty(); @endphp
                <div class="content-item {{ $submitted ? 'done' : '' }}"
                     onclick="loadAssessment({{ $week->assessment->id }})">
                    <div class="w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center
                                {{ $submitted ? 'bg-green-500' : 'border border-slate-300' }}">
                        @if($submitted)
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="item-title text-[12px] text-slate-700 leading-snug">Week Assessment</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">✏️ Quiz · {{ $week->assessment->questions->count() }} questions</p>
                    </div>
                </div>
                @endif
            </div>

            @endforeach

        </div>{{-- end sidebar-inner --}}
    </aside>

    {{-- ════════════════════════════════════════════════════════════════════
         MAIN CONTENT PANEL
    ════════════════════════════════════════════════════════════════════════ --}}
    <main class="content-panel" id="content-panel">

        {{-- Top bar --}}
        <div class="sticky top-0 z-20 bg-white border-b border-slate-200 px-5 py-3 flex items-center gap-4 flex-shrink-0">

            {{-- Sidebar toggle --}}
            <button onclick="toggleSidebar()"
                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors flex-shrink-0"
                title="Toggle course navigation">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 min-w-0 flex-1 text-sm">
                <a href="{{ route('learner.my-learning') }}"
                   class="text-slate-400 hover:text-blue-600 transition-colors hidden sm:block flex-shrink-0">
                    My Learning
                </a>
                <span class="text-slate-300 hidden sm:block">›</span>
                <span class="text-slate-500 truncate hidden sm:block">{{ $enrollment->program->name }}</span>
                <span class="text-slate-300 hidden sm:block">›</span>
                <span class="text-slate-700 font-semibold truncate" id="topbar-title">
                    {{ $contents->first()?->title ?? $currentWeek->title }}
                </span>
            </div>

            {{-- Prev / Next --}}
            <div class="flex items-center gap-1 flex-shrink-0">
                <button id="btn-prev"
                    onclick="navigateContent(-1)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors disabled:opacity-30"
                    title="Previous">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button id="btn-next"
                    onclick="navigateContent(1)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors disabled:opacity-30"
                    title="Next">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Content body ─────────────────────────────────────────────── --}}
        <div class="flex-1 overflow-y-auto" id="content-body">

            {{-- Placeholder shown on load --}}
            <div id="loading-state" class="hidden flex-1 flex items-center justify-center py-32">
                <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            </div>

            {{-- ─── VIDEO PLAYER ──────────────────────────────────────── --}}
            <div id="view-video" class="hidden flex-col">
                <div class="video-wrapper" id="video-wrapper">
                    <video id="video-player" controls preload="metadata"
                           class="w-full h-full"
                           onended="onVideoEnded()"
                           ontimeupdate="onVideoTimeUpdate()">
                        <source id="video-source" src="" type="video/mp4">
                        Your browser does not support video.
                    </video>
                </div>

                {{-- Tab bar --}}
                <div class="tab-bar bg-white px-5 flex gap-0 flex-shrink-0">
                    <button class="tab-btn active" onclick="switchTab('overview')" id="tab-overview">Overview</button>
                    <button class="tab-btn" onclick="switchTab('transcript')" id="tab-transcript">Transcript</button>
                    <button class="tab-btn" onclick="switchTab('notes')" id="tab-notes">Notes</button>
                </div>

                {{-- Tab: Overview --}}
                <div id="pane-overview" class="px-6 py-6 max-w-3xl">
                    <h2 class="text-xl font-black text-slate-900 mb-2" id="video-title"></h2>
                    <p class="text-slate-500 text-sm mb-6" id="video-description"></p>

                    {{-- Video completion CTA --}}
                    <div id="video-complete-wrap" class="mt-4">
                        <button id="video-complete-btn"
                            class="complete-btn"
                            onclick="markComplete()">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Mark as Complete
                        </button>
                        <p class="text-xs text-slate-400 mt-2" id="video-complete-hint">
                            Or keep watching — it'll auto-complete at 90%
                        </p>
                    </div>
                </div>

                {{-- Tab: Transcript (future-ready placeholder) --}}
                <div id="pane-transcript" class="hidden px-6 py-6 max-w-3xl">
                    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-amber-900">Transcripts coming soon</p>
                            <p class="text-xs text-amber-700 mt-1">
                                AI-generated transcripts will appear here with click-to-seek support.
                                Mentors can also manually upload SRT/VTT files.
                            </p>
                        </div>
                    </div>

                    {{-- Placeholder lines — replace with real transcript data when available --}}
                    <div class="mt-6 space-y-1 text-sm text-slate-500" id="transcript-body">
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-3">Transcript Preview</p>
                        {{-- JS will populate this --}}
                    </div>
                </div>

                {{-- Tab: Notes --}}
                <div id="pane-notes" class="hidden px-6 py-6 max-w-3xl">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-3">My Notes</p>
                    <textarea
                        id="notes-textarea"
                        rows="8"
                        placeholder="Write notes while watching…"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-700 placeholder-slate-400 outline-none resize-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all"
                        onchange="saveNote()"></textarea>
                    <p class="text-[11px] text-slate-400 mt-2">Notes are saved per content item in your browser.</p>
                </div>
            </div>

            {{-- ─── ARTICLE / TEXT READER ─────────────────────────────── --}}
            <div id="view-article" class="hidden">
                <div class="max-w-3xl mx-auto px-6 py-8">
                    <h1 class="text-2xl font-black text-slate-900 mb-2" id="article-title"></h1>
                    <p class="text-sm text-slate-400 mb-8" id="article-meta"></p>

                    <div class="article-body prose" id="article-body"></div>

                    {{-- Mark complete strip at bottom --}}
                    <div class="mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center gap-4">
                        <button id="article-complete-btn"
                            class="complete-btn"
                            onclick="markComplete()">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Mark as Complete
                        </button>
                        <button onclick="navigateContent(1)"
                            class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                            Next lesson →
                        </button>
                    </div>
                </div>
            </div>

            {{-- ─── PDF / FILE VIEWER ─────────────────────────────────── --}}
            <div id="view-pdf" class="hidden flex-col h-full">
                <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-slate-100 flex-shrink-0">
                    <h2 class="text-sm font-bold text-slate-800" id="pdf-title"></h2>
                    <a id="pdf-download-link" href="#" target="_blank"
                       class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </a>
                </div>
                <iframe id="pdf-frame" src="" class="flex-1 w-full border-0" style="min-height: calc(100vh - 200px)"></iframe>
                <div class="px-6 py-4 bg-white border-t border-slate-100">
                    <button id="pdf-complete-btn" class="complete-btn" onclick="markComplete()">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Mark as Complete
                    </button>
                </div>
            </div>

            {{-- ─── EXTERNAL LINK ─────────────────────────────────────── --}}
            <div id="view-external" class="hidden">
                <div class="max-w-xl mx-auto px-6 py-16 text-center">
                    <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2" id="external-title"></h3>
                    <p class="text-slate-500 text-sm mb-6">This lesson links to an external resource. It will open in a new tab.</p>
                    <a id="external-link" href="#" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors mb-4">
                        Open Resource
                    </a>
                    <div class="mt-6">
                        <button class="complete-btn" onclick="markComplete()">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Mark as Complete
                        </button>
                    </div>
                </div>
            </div>

            {{-- ─── DEFAULT / SELECT STATE ────────────────────────────── --}}
            <div id="view-default" class="flex flex-col items-center justify-center py-24 text-center px-6">
                <div class="text-5xl mb-4">📖</div>
                <h3 class="text-lg font-bold text-slate-700 mb-2">Select a lesson to begin</h3>
                <p class="text-slate-400 text-sm">Choose any item from the course navigation on the left.</p>
            </div>

            {{-- ─── ASSESSMENT PANEL ───────────────────────────────────────────── --}}
            <div id="view-assessment" class="hidden">
                <div class="max-w-3xl mx-auto px-5 py-8">
            
                    {{-- Loading skeleton --}}
                    <div id="assessment-loading" class="hidden space-y-4">
                        <div class="skeleton h-8 w-1/2 mb-2"></div>
                        <div class="skeleton h-4 w-3/4 mb-6"></div>
                        <div class="skeleton h-20 w-full rounded-2xl"></div>
                        <div class="skeleton h-20 w-full rounded-2xl"></div>
                        <div class="skeleton h-20 w-full rounded-2xl"></div>
                    </div>
            
                    {{-- ── State: Intro ──────────────────────────────────────────────── --}}
                    <div id="assessment-intro" class="hidden">
                        <div class="mb-6">
                            <p class="text-xs font-black uppercase tracking-widest text-indigo-500 mb-2">Week Assessment</p>
                            <h2 class="text-2xl font-black text-slate-900 mb-2" id="a-title"></h2>
                        </div>
            
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Questions</p>
                                <p class="text-2xl font-black text-slate-900" id="a-q-count">—</p>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Pass Mark</p>
                                <p class="text-2xl font-black text-slate-900" id="a-pass-mark">—</p>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100" id="a-time-box">
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Time Limit</p>
                                <p class="text-2xl font-black text-slate-900" id="a-time-limit">None</p>
                            </div>
                        </div>
            
                        {{-- Previous attempt --}}
                        <div id="a-prev-attempt" class="hidden bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-bold text-amber-900">Previous attempt: <span id="a-prev-score"></span></p>
                                <p class="text-xs text-amber-700 mt-0.5" id="a-prev-date"></p>
                                <p class="text-xs text-amber-700 mt-1">You can retake this assessment to improve your score.</p>
                            </div>
                        </div>
            
                        <button id="btn-start-assessment" onclick="startAssessment()"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-indigo-600 text-white font-bold px-8 py-4 rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-300/20 text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <span id="btn-start-label">Start Assessment</span>
                        </button>
                    </div>
            
                    {{-- ── State: Quiz (all questions visible) ──────────────────────── --}}
                    <div id="assessment-quiz" class="hidden">
            
                        {{-- Sticky timer bar (only shown when time limit set) --}}
                        <div id="timer-bar" class="hidden sticky top-0 z-10 bg-white border-b border-slate-100 -mx-5 px-5 py-3 mb-6 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-600" id="quiz-title-bar"></p>
                            <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span id="timer-value" class="tabular-nums">00:00</span>
                            </div>
                        </div>
            
                        {{-- All questions rendered here by JS --}}
                        <div id="all-questions-container" class="space-y-5"></div>
            
                        {{-- Unanswered warning --}}
                        <div id="unanswered-warning" class="hidden mt-5 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-sm font-medium text-amber-800">Please answer all questions before submitting.</p>
                        </div>
            
                        {{-- Submit --}}
                        <div class="mt-8 pt-6 border-t border-slate-200 flex justify-end">
                            <button onclick="submitAssessment()" id="btn-quiz-submit"
                                class="inline-flex items-center gap-2 bg-indigo-600 text-white font-bold px-8 py-4 rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-300/20 text-base">
                                Submit Assessment
                            </button>
                        </div>
                    </div>
            
                    {{-- ── State: Submitting ─────────────────────────────────────────── --}}
                    <div id="assessment-submitting" class="hidden text-center py-16">
                        <div class="w-12 h-12 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                        <p class="text-slate-500 font-medium">Submitting your answers…</p>
                    </div>
            
                    {{-- ── State: Results — PASSED ──────────────────────────────────── --}}
                    <div id="assessment-results-pass" class="hidden">
                        <div class="text-center mb-8">
                            <div class="score-ring mx-auto mb-4">
                                <svg width="120" height="120" viewBox="0 0 120 120">
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                                    <circle cx="60" cy="60" r="52" fill="none" id="score-circle"
                                            stroke="#16a34a" stroke-width="10"
                                            stroke-dasharray="326.7" stroke-dashoffset="326.7"
                                            stroke-linecap="round"
                                            style="transition: stroke-dashoffset 1s ease-in-out"/>
                                </svg>
                                <span class="score-text" id="result-pct">0%</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 mb-1" id="result-headline"></h2>
                            <p class="text-slate-500 text-sm" id="result-subline"></p>
                        </div>
            
                        <div class="bg-white rounded-2xl border border-slate-100 divide-y divide-slate-50 mb-8" id="result-breakdown"></div>
            
                        <div class="flex flex-wrap gap-3 justify-center">
                            <button onclick="retakeAssessment()"
                                class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 px-5 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                                Retake Assessment
                            </button>
                            <button onclick="navigateContent(1)"
                                class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-5 py-3 rounded-xl hover:bg-indigo-100 transition-colors">
                                Continue →
                            </button>
                        </div>
                    </div>
            
                    {{-- ── State: Results — FAILED ──────────────────────────────────── --}}
                    <div id="assessment-results-fail" class="hidden">
                        <div class="text-center py-10">
                            <div class="w-24 h-24 rounded-full bg-red-50 border-4 border-red-200 flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 mb-2">Below the pass mark</h2>
                            <p class="text-slate-500 text-sm mb-2" id="fail-score-line"></p>
                            <p class="text-slate-400 text-sm mb-8">Review the material and try again — you've got this.</p>
                            <button onclick="retakeAssessment()"
                                class="inline-flex items-center justify-center gap-2 bg-indigo-600 text-white font-bold px-8 py-4 rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-300/20 text-base mx-auto">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Retake Assessment
                            </button>
                        </div>
                    </div>
            
                </div>
            </div>

        </div>{{-- end content-body --}}

        {{-- Week completion toast (hidden) --}}
        <div id="week-complete-toast"
             class="fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3.5 rounded-2xl shadow-2xl shadow-green-500/30 flex items-center gap-3 translate-y-20 opacity-0 transition-all duration-300 pointer-events-none z-50">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-bold text-sm">Week complete!</p>
                <p class="text-green-100 text-xs" id="week-complete-msg">Great work. Next week is now unlocked.</p>
            </div>
        </div>

    </main>
</div>
@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════════════════════
// BASE DATA (from Blade)
// ════════════════════════════════════════════════════════════════════════════
var CONTENTS        = JSON.parse(document.getElementById('contents-data').textContent);
var ENROLLMENT_ID   = JSON.parse(document.getElementById('enrollment-id').textContent);
var CURRENT_WEEK_ID = JSON.parse(document.getElementById('current-week-id').textContent);
var CSRF            = document.querySelector('meta[name="csrf-token"]').content;

// ── localStorage keys ──────────────────────────────────────────────────────
var POSITION_KEY = 'lms_pos_' + ENROLLMENT_ID;   // 'content:ID' or 'assessment:ID'
// Quiz state key is dynamic: 'lms_quiz_' + ENROLLMENT_ID + '_' + assessmentId

// ── Runtime state ──────────────────────────────────────────────────────────
var currentIndex       = 0;
var currentData        = null;
var activeWeekId       = CURRENT_WEEK_ID;
var videoProgressTimer = null;
var videoGateUnlocked  = false;

// Assessment state
var ASSESSMENT_DATA = null;
var attemptId       = null;
var answers         = {};
var timerInterval   = null;
var timerSecondsLeft = 0;

// ════════════════════════════════════════════════════════════════════════════
// INIT — restore last position from localStorage
// ════════════════════════════════════════════════════════════════════════════
(function init() {
    if (!CONTENTS.length) return;

    var saved = null;
    try { saved = localStorage.getItem(POSITION_KEY); } catch(e) {}

    if (saved) {
        var parts = saved.split(':');
        var type  = parts[0];
        var id    = parseInt(parts[1]);

        if (type === 'assessment') {
            var aItem = CONTENTS.find(function(c) { return c.type === 'assessment' && c._assessment_id === id; });
            if (aItem) { loadAssessment(id); return; }
        } else if (type === 'content') {
            var idx = CONTENTS.findIndex(function(c) { return c.id === id; });
            if (idx !== -1) { loadContent(id); return; }
        }
    }

    // Default: first incomplete content
    var first = CONTENTS.findIndex(function(c) { return !c.is_completed; });
    loadContent(CONTENTS[first >= 0 ? first : 0].id);
})();

// ════════════════════════════════════════════════════════════════════════════
// SIDEBAR
// ════════════════════════════════════════════════════════════════════════════
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var collapsed = sidebar.classList.toggle('collapsed');
    overlay.classList.toggle('show', !collapsed && window.innerWidth <= 768);
}

// ════════════════════════════════════════════════════════════════════════════
// WEEK ACCORDION — AJAX content swap
// ════════════════════════════════════════════════════════════════════════════
function toggleWeek(weekId, isLocked) {
    if (isLocked) return;

    var el   = document.getElementById('week-contents-' + weekId);
    var chev = document.querySelector('.chevron-' + weekId);
    var row  = document.getElementById('week-row-' + weekId);

    if (!el.classList.contains('hidden')) {
        el.classList.add('hidden');
        if (chev) chev.classList.remove('rotate-180');
        row.classList.remove('open');
        return;
    }

    // Close all others
    document.querySelectorAll('[id^="week-contents-"]').forEach(function(e) { e.classList.add('hidden'); });
    document.querySelectorAll('[class*="chevron-"]').forEach(function(e) { e.classList.remove('rotate-180'); });
    document.querySelectorAll('.week-row').forEach(function(e) { e.classList.remove('open'); });

    if (weekId === CURRENT_WEEK_ID) {
        el.classList.remove('hidden');
        if (chev) chev.classList.add('rotate-180');
        row.classList.add('open');
        return;
    }

    el.innerHTML = '<div class="px-4 py-3 space-y-2"><div class="skeleton h-8 rounded-xl"></div><div class="skeleton h-8 rounded-xl"></div><div class="skeleton h-8 rounded-xl"></div></div>';
    el.classList.remove('hidden');
    if (chev) chev.classList.add('rotate-180');
    row.classList.add('open');

    fetch('/learner/learning/' + ENROLLMENT_ID + '/week/' + weekId + '/contents', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) { el.innerHTML = '<p class="px-5 py-3 text-xs text-red-500">' + data.message + '</p>'; return; }

        CONTENTS = data.contents;
        if (data.assessment) {
            CONTENTS.push({
                id: 'assessment-' + data.assessment.id,
                title: data.assessment.title,
                type: 'assessment',
                is_completed: data.assessment.is_submitted,
                _assessment_id: data.assessment.id,
            });
        }
        activeWeekId = weekId;
        currentIndex = 0;
        el.innerHTML = buildSidebarItems(data.contents, data.assessment);
        if (data.contents.length > 0) loadContent(data.contents[0].id);
    })
    .catch(function() { el.innerHTML = '<p class="px-5 py-3 text-xs text-red-500">Failed to load.</p>'; });
}

function buildSidebarItems(contents, assessment) {
    var html = '';
    contents.forEach(function(c) {
        var icon  = c.type === 'video' ? '▶' : c.type === 'article' ? '📄' : c.type === 'pdf' ? '📎' : '•';
        var done  = c.is_completed;
        var circ  = done ? 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center bg-green-500' : 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center border border-slate-300';
        var check = done ? '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>' : '';
        var dur   = c.video_duration ? ' · ' + c.video_duration + ' min' : '';
        html += '<div class="content-item ' + (done ? 'done' : '') + '" id="sidebar-item-' + c.id + '" onclick="loadContent(' + c.id + ')">'
            + '<div class="' + circ + '">' + check + '</div>'
            + '<div class="min-w-0 flex-1"><p class="item-title text-[12px] text-slate-700 leading-snug line-clamp-2">' + escHtml(c.title) + '</p>'
            + '<p class="text-[11px] text-slate-400 mt-0.5">' + icon + ' ' + cap(c.type) + dur + '</p></div></div>';
    });
    if (assessment) {
        var ad = assessment.is_submitted;
        var ac = ad ? 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center bg-green-500' : 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center border border-slate-300';
        var ak = ad ? '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>' : '';
        html += '<div class="content-item ' + (ad ? 'done' : '') + '" id="sidebar-item-assessment-' + assessment.id + '" onclick="loadAssessment(' + assessment.id + ')">'
            + '<div class="' + ac + '">' + ak + '</div>'
            + '<div class="min-w-0"><p class="item-title text-[12px] text-slate-700">Week Assessment</p>'
            + '<p class="text-[11px] text-slate-400 mt-0.5">✏️ Quiz · ' + assessment.question_count + ' questions</p></div></div>';
    }
    return html;
}

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function cap(s)     { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

// ════════════════════════════════════════════════════════════════════════════
// NAVIGATION
// ════════════════════════════════════════════════════════════════════════════
function navigateContent(dir) {
    var newIdx = currentIndex + dir;
    if (newIdx < 0 || newIdx >= CONTENTS.length) return;
    var item = CONTENTS[newIdx];
    if (item.type === 'assessment') loadAssessment(item._assessment_id);
    else loadContent(item.id);
}

// ════════════════════════════════════════════════════════════════════════════
// LOAD CONTENT — save position to localStorage
// ════════════════════════════════════════════════════════════════════════════
function loadContent(contentId) {
    var idx = CONTENTS.findIndex(function(c) { return c.id === contentId; });
    if (idx === -1) return;
    currentIndex = idx;
    currentData  = CONTENTS[idx];

    // Save position
    try { localStorage.setItem(POSITION_KEY, 'content:' + contentId); } catch(e) {}

    updateSidebarActive('sidebar-item-' + contentId);
    document.getElementById('topbar-title').textContent = currentData.title;
    document.getElementById('btn-prev').disabled = currentIndex === 0;
    document.getElementById('btn-next').disabled = currentIndex === CONTENTS.length - 1;

    if (videoProgressTimer) clearInterval(videoProgressTimer);
    videoGateUnlocked = false;
    restoreNote(contentId);
    hideAllViews();

    switch (currentData.type) {
        case 'video':    renderVideo(currentData);    break;
        case 'article':  renderArticle(currentData);  break;
        case 'pdf':      renderPdf(currentData);      break;
        case 'link':
        case 'external': renderExternal(currentData); break;
        default:         renderArticle(currentData);  break;
    }
}

function updateSidebarActive(activeId) {
    document.querySelectorAll('.content-item').forEach(function(el) { el.classList.remove('active'); });
    var el = document.getElementById(activeId);
    if (el) el.classList.add('active');
}

function hideAllViews() {
    ['view-video','view-article','view-pdf','view-external','view-default','view-assessment','loading-state']
        .forEach(function(id) {
            var el = document.getElementById(id);
            if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
        });
    ['assessment-results-pass','assessment-results-fail'].forEach(function(id) {
        var el = document.getElementById(id); if (el) el.classList.add('hidden');
    });
    stopTimer();
}

// ════════════════════════════════════════════════════════════════════════════
// RENDER: VIDEO — gate complete button at 75% watch time
// ════════════════════════════════════════════════════════════════════════════
function renderVideo(c) {
    var view = document.getElementById('view-video');
    view.classList.remove('hidden'); view.classList.add('flex');
    document.getElementById('video-title').textContent       = c.title;
    document.getElementById('video-description').textContent = '';

    var player = document.getElementById('video-player');
    document.getElementById('video-source').src = c.video_url || '';
    player.load();

    var btn  = document.getElementById('video-complete-btn');
    var hint = document.getElementById('video-complete-hint');

    if (c.is_completed) {
        videoGateUnlocked = true;
        setCompleteBtn(btn, 'done');
        hint.textContent = 'Completed ✓';
    } else if (c.video_duration && c.video_duration > 0) {
        // Gate: disable until 75% watched
        videoGateUnlocked = false;
        setCompleteBtn(btn, 'locked');
        hint.textContent = 'Watch at least 75% to enable completion';
    } else {
        // No duration — available immediately
        videoGateUnlocked = true;
        setCompleteBtn(btn, 'ready');
        hint.textContent = 'Mark as complete when you\'re done';
    }

    videoProgressTimer = setInterval(function() {
        if (!player.paused && player.duration > 0) {
            pingProgress(c.id, Math.round((player.currentTime / player.duration) * 100), 15);
        }
    }, 15000);

    switchTab('overview');
}

function onVideoTimeUpdate() {
    var player = document.getElementById('video-player');
    if (!currentData || currentData.type !== 'video' || !player.duration) return;

    var pct = (player.currentTime / player.duration) * 100;

    // Unlock complete button at 75%
    if (!videoGateUnlocked && pct >= 75) {
        videoGateUnlocked = true;
        var btn = document.getElementById('video-complete-btn');
        setCompleteBtn(btn, 'ready');
        document.getElementById('video-complete-hint').textContent = 'Ready — mark as complete when you\'re done';
    }

    // Auto-silently complete at 90%
    if (pct >= 90 && !currentData.is_completed) markComplete(true);
}

function onVideoEnded() {
    if (currentData && !currentData.is_completed) markComplete(true);
}

// ════════════════════════════════════════════════════════════════════════════
// RENDER: ARTICLE / PDF / EXTERNAL
// ════════════════════════════════════════════════════════════════════════════
function renderArticle(c) {
    var view = document.getElementById('view-article');
    view.classList.remove('hidden');
    document.getElementById('article-title').textContent = c.title;
    document.getElementById('article-meta').textContent  = 'Article';
    document.getElementById('article-body').innerHTML    = c.text_content || '<p class="text-slate-400">No content.</p>';
    var btn = document.getElementById('article-complete-btn');
    c.is_completed ? setCompleteBtn(btn, 'done') : setCompleteBtn(btn, 'ready');
}

function renderPdf(c) {
    var view = document.getElementById('view-pdf');
    view.classList.remove('hidden'); view.classList.add('flex');
    document.getElementById('pdf-title').textContent  = c.title;
    document.getElementById('pdf-frame').src          = c.file_url || '';
    document.getElementById('pdf-download-link').href = c.file_url || '#';
    var btn = document.getElementById('pdf-complete-btn');
    c.is_completed ? setCompleteBtn(btn, 'done') : setCompleteBtn(btn, 'ready');
}

function renderExternal(c) {
    var view = document.getElementById('view-external');
    view.classList.remove('hidden');
    document.getElementById('external-title').textContent = c.title;
    document.getElementById('external-link').href         = c.external_url || '#';
}

// ════════════════════════════════════════════════════════════════════════════
// TABS
// ════════════════════════════════════════════════════════════════════════════
function switchTab(tabName) {
    ['overview','transcript','notes'].forEach(function(t) {
        document.getElementById('pane-' + t).classList.toggle('hidden', t !== tabName);
        document.getElementById('tab-'  + t).classList.toggle('active', t === tabName);
    });
}

// ════════════════════════════════════════════════════════════════════════════
// MARK COMPLETE — transform button to "Next →", auto-advance
// ════════════════════════════════════════════════════════════════════════════
function markComplete(silent) {
    if (!currentData || currentData.is_completed) return;
    if (currentData.type === 'video' && !videoGateUnlocked) return; // gate not cleared

    fetch('/learner/learning/content/' + currentData.id + '/complete', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;

        CONTENTS[currentIndex].is_completed = true;
        currentData.is_completed = true;

        // Sidebar green
        markSidebarItemDone('sidebar-item-' + currentData.id);

        // Transform complete buttons
        var isLast = currentIndex >= CONTENTS.length - 1;
        var nextLabel = isLast ? 'Done ✓' : 'Next Lesson →';
        var nextStyle = isLast ? 'next-done' : 'next';
        ['video-complete-btn','article-complete-btn','pdf-complete-btn'].forEach(function(id) {
            var btn = document.getElementById(id);
            if (btn) setCompleteBtn(btn, nextStyle, nextLabel);
        });

        if (!silent) {
            if (data.week_completed) showWeekCompleteToast();
            if (!isLast) setTimeout(function() { navigateContent(1); }, 600);
        }
    })
    .catch(function() { if (!silent) showMiniToast('Could not save. Please try again.', true); });
}

/**
 * State machine for complete button appearance.
 * state: 'locked' | 'ready' | 'done' | 'next' | 'next-done'
 */
function setCompleteBtn(btn, state, label) {
    if (!btn) return;
    var check = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';

    btn.disabled = false;
    btn.onclick  = function() { markComplete(); };
    btn.className = 'complete-btn';

    switch (state) {
        case 'locked':
            btn.disabled = true;
            btn.style.cssText = 'opacity:.4;cursor:not-allowed;background:#94a3b8';
            btn.innerHTML = check + ' Mark as Complete';
            break;
        case 'ready':
            btn.style.cssText = '';
            btn.innerHTML = check + ' Mark as Complete';
            break;
        case 'done':
            btn.style.cssText = 'background:#64748b;cursor:default';
            btn.disabled = true;
            btn.innerHTML = check + ' Completed';
            break;
        case 'next':
            btn.style.cssText = 'background:#4f46e5';
            btn.innerHTML = check + (label || ' Next Lesson →');
            btn.onclick = function() { navigateContent(1); };
            break;
        case 'next-done':
            btn.style.cssText = 'background:#16a34a';
            btn.disabled = true;
            btn.innerHTML = check + (label || ' Done ✓');
            btn.onclick = null;
            break;
    }
}

function markSidebarItemDone(itemId) {
    var si = document.getElementById(itemId);
    if (!si) return;
    si.classList.add('done');
    var circle = si.querySelector('.rounded-full.border, .rounded-full.border-slate-300');
    if (circle) {
        circle.className = 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center bg-green-500';
        circle.innerHTML = '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PROGRESS PING
// ════════════════════════════════════════════════════════════════════════════
var lastPingedPct = -1;
function pingProgress(contentId, pct, timeSpent) {
    if (pct === lastPingedPct) return;
    lastPingedPct = pct;
    fetch('/learner/learning/content/' + contentId + '/progress', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ progress_percentage: pct, time_spent: timeSpent })
    }).catch(function() {});
}

// ════════════════════════════════════════════════════════════════════════════
// NOTES
// ════════════════════════════════════════════════════════════════════════════
function saveNote() {
    if (!currentData) return;
    try { localStorage.setItem('note_' + ENROLLMENT_ID + '_' + currentData.id, document.getElementById('notes-textarea').value); } catch(e) {}
}
function restoreNote(contentId) {
    try { document.getElementById('notes-textarea').value = localStorage.getItem('note_' + ENROLLMENT_ID + '_' + contentId) || ''; } catch(e) {}
}

// ════════════════════════════════════════════════════════════════════════════
// ASSESSMENT ENGINE — all questions on one scrollable page
// ════════════════════════════════════════════════════════════════════════════

function loadAssessment(assessmentId) {
    hideAllViews();
    updateSidebarActive('sidebar-item-assessment-' + assessmentId);
    document.getElementById('topbar-title').textContent = 'Week Assessment';
    document.getElementById('btn-prev').disabled = currentIndex <= 0;
    document.getElementById('btn-next').disabled = currentIndex >= CONTENTS.length - 1;

    // Save position
    try { localStorage.setItem(POSITION_KEY, 'assessment:' + assessmentId); } catch(e) {}

    var view = document.getElementById('view-assessment');
    view.classList.remove('hidden');
    showAssessmentState('loading');

    fetch('/learner/learning/' + ENROLLMENT_ID + '/assessment/' + assessmentId, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) { showMiniToast(data.message || 'Could not load assessment.', true); return; }
        ASSESSMENT_DATA = data;

        // Check for resumable in-progress attempt
        if (data.in_progress_attempt) {
            var savedState = loadQuizState(assessmentId);
            if (savedState && savedState.attemptId === data.in_progress_attempt.id) {
                // Resume mid-quiz
                attemptId = savedState.attemptId;
                answers   = savedState.answers || {};
                showAssessmentState('quiz');
                renderAllQuestions();
                startTimerIfNeeded();
                return;
            }
        }

        showAssessmentState('intro');
        renderAssessmentIntro(data);
    })
    .catch(function() { showMiniToast('Failed to load assessment.', true); });
}

// ── Show/hide assessment sub-states ──────────────────────────────────────
function showAssessmentState(state) {
    ['loading','intro','quiz','submitting','results-pass','results-fail'].forEach(function(s) {
        var el = document.getElementById('assessment-' + s.replace('-','-'));
        // map to actual IDs
    });
    // Direct ID mapping
    var map = {
        'loading':      'assessment-loading',
        'intro':        'assessment-intro',
        'quiz':         'assessment-quiz',
        'submitting':   'assessment-submitting',
        'pass':         'assessment-results-pass',
        'fail':         'assessment-results-fail',
    };
    Object.keys(map).forEach(function(k) {
        var el = document.getElementById(map[k]);
        if (el) el.classList.add('hidden');
    });
    var target = document.getElementById(map[state]);
    if (target) target.classList.remove('hidden');
}

// ── Intro screen ────────────────────────────────────────────────────────
function renderAssessmentIntro(data) {
    var a = data.assessment;
    document.getElementById('a-title').textContent     = a.title;
    document.getElementById('a-q-count').textContent   = data.questions.length;
    document.getElementById('a-pass-mark').textContent = a.passing_score + '%';
    document.getElementById('a-time-limit').textContent = a.time_limit ? a.time_limit + ' min' : 'None';

    if (data.latest_attempt) {
        var la = data.latest_attempt;
        document.getElementById('a-prev-score').textContent = la.score + '% — ' + (la.passed ? '✓ Passed' : '✗ Not passed');
        document.getElementById('a-prev-date').textContent  = 'Submitted ' + la.submitted_at;
        document.getElementById('a-prev-attempt').classList.remove('hidden');
        document.getElementById('btn-start-label').textContent = 'Retake Assessment';
    } else {
        document.getElementById('a-prev-attempt').classList.add('hidden');
        document.getElementById('btn-start-label').textContent = 'Start Assessment';
    }
}

// ── Start / Retake ───────────────────────────────────────────────────────
function startAssessment() {
    if (!ASSESSMENT_DATA) return;

    fetch('/learner/assessments/' + ASSESSMENT_DATA.assessment.id + '/attempt', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ enrollment_id: ENROLLMENT_ID })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var aid = data.attempt_id || (data.attempt && data.attempt.id);
        if (!aid) { showMiniToast('Could not start attempt.', true); return; }

        attemptId = aid;
        answers   = {};
        clearQuizState(ASSESSMENT_DATA.assessment.id);

        showAssessmentState('quiz');
        renderAllQuestions();
        startTimerIfNeeded();
    })
    .catch(function() { showMiniToast('Server error. Please try again.', true); });
}

function retakeAssessment() {
    if (ASSESSMENT_DATA) clearQuizState(ASSESSMENT_DATA.assessment.id);
    answers = {};
    showAssessmentState('intro');
    renderAssessmentIntro(ASSESSMENT_DATA);
}

// ── Timer ────────────────────────────────────────────────────────────────
function startTimerIfNeeded() {
    if (!ASSESSMENT_DATA || !ASSESSMENT_DATA.assessment.time_limit) return;
    var bar = document.getElementById('timer-bar');
    bar.classList.remove('hidden');
    document.getElementById('quiz-title-bar').textContent = ASSESSMENT_DATA.assessment.title;
    timerSecondsLeft = ASSESSMENT_DATA.assessment.time_limit * 60;
    timerInterval = setInterval(tickTimer, 1000);
    tickTimer(); // render immediately
}

function tickTimer() {
    timerSecondsLeft--;
    var m = Math.floor(Math.max(0, timerSecondsLeft) / 60);
    var s = Math.max(0, timerSecondsLeft) % 60;
    var el = document.getElementById('timer-value');
    el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    if (timerSecondsLeft <= 60)  el.classList.add('timer-warning');
    if (timerSecondsLeft <= 0)   { stopTimer(); submitAssessment(); }
}
function stopTimer() { if (timerInterval) { clearInterval(timerInterval); timerInterval = null; } }

// ── Render ALL questions on one page ─────────────────────────────────────
function renderAllQuestions() {
    var container = document.getElementById('all-questions-container');
    container.innerHTML = '';
    document.getElementById('unanswered-warning').classList.add('hidden');

    ASSESSMENT_DATA.questions.forEach(function(q, idx) {
        var card = document.createElement('div');
        card.id        = 'q-card-' + q.id;
        card.className = 'bg-white rounded-2xl border-2 border-slate-200 p-6 transition-all';

        var optionsHtml = '';
        (q.options || []).forEach(function(opt, optIdx) {
            var isSelected = answers[q.id] === opt;
            optionsHtml += '<div class="quiz-option' + (isSelected ? ' selected' : '') + '" '
                + 'data-qid="' + q.id + '" data-optidx="' + optIdx + '" onclick="selectAnswer(this)">'
                + '<div class="quiz-radio"></div>'
                + '<span class="text-sm text-slate-800 leading-snug">' + escHtml(opt) + '</span>'
                + '</div>';
        });

        card.innerHTML = '<div class="flex items-start gap-3 mb-5">'
            + '<span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-black flex items-center justify-center flex-shrink-0">' + (idx + 1) + '</span>'
            + '<p class="text-[15px] font-semibold text-slate-900 leading-relaxed">' + escHtml(q.text || q.question_text || '') + '</p>'
            + '</div>'
            + '<div class="space-y-3 ml-10" id="options-' + q.id + '">' + optionsHtml + '</div>';

        container.appendChild(card);
    });
}

function selectAnswer(clickedEl) {
    var qId    = parseInt(clickedEl.dataset.qid);
    var optIdx = parseInt(clickedEl.dataset.optidx);
    var q      = ASSESSMENT_DATA.questions.find(function(x) { return x.id == qId; });
    if (!q) return;
    var value = q.options[optIdx];

    // Deselect all options in this question
    var container = document.getElementById('options-' + qId);
    if (container) container.querySelectorAll('.quiz-option').forEach(function(el) { el.classList.remove('selected'); });
    clickedEl.classList.add('selected');
    answers[qId] = value;

    // Remove error highlight
    var card = document.getElementById('q-card-' + qId);
    if (card) { card.classList.remove('border-red-300'); card.style.background = ''; }

    saveQuizState();
}

// ── Submit ───────────────────────────────────────────────────────────────
function submitAssessment() {
    if (!attemptId) return;
    stopTimer();

    // Validate all answered
    var unanswered = ASSESSMENT_DATA.questions.filter(function(q) {
        return answers[q.id] === undefined || answers[q.id] === null;
    });

    if (unanswered.length > 0) {
        unanswered.forEach(function(q) {
            var card = document.getElementById('q-card-' + q.id);
            if (card) { card.classList.add('border-red-300'); card.style.background = '#fef2f2'; }
        });
        document.getElementById('unanswered-warning').classList.remove('hidden');
        var first = document.getElementById('q-card-' + unanswered[0].id);
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    showAssessmentState('submitting');

    var answersArray = ASSESSMENT_DATA.questions.map(function(q) {
        return { question_id: q.id, answer: answers[q.id] !== undefined ? answers[q.id] : null };
    });

    fetch('/learner/attempts/' + attemptId + '/submit', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ answers: answersArray })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success || data.score !== undefined) {
            clearQuizState(ASSESSMENT_DATA.assessment.id);
            renderResults(data);
        } else {
            showMiniToast(data.message || 'Submission failed.', true);
            showAssessmentState('quiz');
        }
    })
    .catch(function() {
        showMiniToast('Submission failed. Please try again.', true);
        showAssessmentState('quiz');
    });
}

// ── Results ──────────────────────────────────────────────────────────────
function renderResults(data) {
    var score   = data.score || 0;
    var passing = ASSESSMENT_DATA ? ASSESSMENT_DATA.assessment.passing_score : 70;
    var passed  = score >= passing;

    if (!passed) {
        document.getElementById('fail-score-line').textContent =
            'You scored ' + score + '%. The pass mark is ' + passing + '%.';
        showAssessmentState('fail');
        return;
    }

    // Animate score ring
    var circumference = 326.7;
    var circle = document.getElementById('score-circle');
    circle.style.strokeDashoffset = circumference;
    setTimeout(function() { circle.style.strokeDashoffset = circumference - (score / 100) * circumference; }, 50);

    document.getElementById('result-pct').textContent      = score + '%';
    document.getElementById('result-headline').textContent = '🎉 You passed!';
    document.getElementById('result-subline').textContent  = 'You scored ' + score + '% — above the ' + passing + '% pass mark.';

    var breakdown = document.getElementById('result-breakdown');
    breakdown.innerHTML = '';
    if (data.question_results && data.question_results.length) {
        data.question_results.forEach(function(qr, i) {
            var row  = document.createElement('div');
            row.className = 'px-5 py-4 flex items-start gap-3';
            var icon = qr.is_correct
                ? '<div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>'
                : '<div class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div>';
            row.innerHTML = icon + '<div class="min-w-0"><p class="text-sm text-slate-800 font-medium">Q' + (i+1) + '. ' + escHtml(qr.question_text || '') + '</p>'
                + (!qr.is_correct && qr.correct_answer ? '<p class="text-xs text-green-600 mt-1 font-medium">Correct: ' + escHtml(String(qr.correct_answer)) + '</p>' : '') + '</div>';
            breakdown.appendChild(row);
        });
    }

    // Mark sidebar assessment item green
    markSidebarItemDone('sidebar-item-assessment-' + (ASSESSMENT_DATA ? ASSESSMENT_DATA.assessment.id : ''));

    showAssessmentState('pass');
}

// ── Quiz localStorage ────────────────────────────────────────────────────
function quizStateKey(assessmentId) { return 'lms_quiz_' + ENROLLMENT_ID + '_' + assessmentId; }

function saveQuizState() {
    if (!attemptId || !ASSESSMENT_DATA) return;
    try {
        localStorage.setItem(quizStateKey(ASSESSMENT_DATA.assessment.id),
            JSON.stringify({ attemptId: attemptId, answers: answers }));
    } catch(e) {}
}

function loadQuizState(assessmentId) {
    try {
        var raw = localStorage.getItem(quizStateKey(assessmentId));
        return raw ? JSON.parse(raw) : null;
    } catch(e) { return null; }
}

function clearQuizState(assessmentId) {
    try { localStorage.removeItem(quizStateKey(assessmentId)); } catch(e) {}
}

// ════════════════════════════════════════════════════════════════════════════
// TOASTS
// ════════════════════════════════════════════════════════════════════════════
function showMiniToast(msg, isError) {
    var el = document.createElement('div');
    el.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 ' + (isError ? 'bg-red-600' : 'bg-slate-900') + ' text-white text-sm font-semibold px-5 py-3 rounded-2xl shadow-xl z-50';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function() { el.remove(); }, 2500);
}

function showWeekCompleteToast() {
    var toast = document.getElementById('week-complete-toast');
    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(function() { toast.classList.add('translate-y-20', 'opacity-0'); }, 4000);
}
</script>
@endpush