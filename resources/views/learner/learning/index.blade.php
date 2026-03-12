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
                $weekContents = $week->publishedContents()
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
// DATA
// ════════════════════════════════════════════════════════════════════════════
var CONTENTS      = JSON.parse(document.getElementById('contents-data').textContent);
var ENROLLMENT_ID = JSON.parse(document.getElementById('enrollment-id').textContent);
var CSRF          = document.querySelector('meta[name="csrf-token"]').content;

var currentIndex  = 0;   // index within CONTENTS array
var currentData   = null; // the active content object
var videoProgressTimer = null;

// ════════════════════════════════════════════════════════════════════════════
// INIT — load the first incomplete content automatically
// ════════════════════════════════════════════════════════════════════════════
(function init() {
    if (!CONTENTS.length) return;

    // Find first incomplete, else start from 0
    var firstIncomplete = CONTENTS.findIndex(function(c) { return !c.is_completed; });
    loadContent(CONTENTS[firstIncomplete >= 0 ? firstIncomplete : 0].id);
})();

// ════════════════════════════════════════════════════════════════════════════
// SIDEBAR TOGGLE
// ════════════════════════════════════════════════════════════════════════════
function toggleSidebar() {
    var sidebar  = document.getElementById('sidebar');
    var overlay  = document.getElementById('sidebar-overlay');
    var collapsed = sidebar.classList.toggle('collapsed');
    overlay.classList.toggle('show', !collapsed && window.innerWidth <= 768);
}

// ════════════════════════════════════════════════════════════════════════════
// WEEK ACCORDION
// ════════════════════════════════════════════════════════════════════════════
function toggleWeek(weekId, isLocked) {
    if (isLocked) return;

    var el     = document.getElementById('week-contents-' + weekId);
    var chev   = document.querySelector('.chevron-' + weekId);
    var row    = document.getElementById('week-row-' + weekId);
    var hidden = el.classList.toggle('hidden');

    if (chev) chev.classList.toggle('rotate-180', !hidden);
    row.classList.toggle('open', !hidden);
}

// ════════════════════════════════════════════════════════════════════════════
// NAVIGATION
// ════════════════════════════════════════════════════════════════════════════
function navigateContent(dir) {
    var newIndex = currentIndex + dir;
    if (newIndex >= 0 && newIndex < CONTENTS.length) {
        loadContent(CONTENTS[newIndex].id);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// LOAD CONTENT
// ════════════════════════════════════════════════════════════════════════════
function loadContent(contentId) {
    var idx = CONTENTS.findIndex(function(c) { return c.id === contentId; });
    if (idx === -1) return;

    currentIndex = idx;
    currentData  = CONTENTS[idx];

    // Update sidebar active state
    document.querySelectorAll('.content-item').forEach(function(el) {
        el.classList.remove('active');
    });
    var sidebarItem = document.getElementById('sidebar-item-' + contentId);
    if (sidebarItem) sidebarItem.classList.add('active');

    // Update topbar title
    document.getElementById('topbar-title').textContent = currentData.title;

    // Prev / Next buttons
    document.getElementById('btn-prev').disabled = currentIndex === 0;
    document.getElementById('btn-next').disabled = currentIndex === CONTENTS.length - 1;

    // Stop any ongoing video timer
    if (videoProgressTimer) clearInterval(videoProgressTimer);

    // Restore notes
    restoreNote(contentId);

    // Render by type
    hideAllViews();
    switch (currentData.type) {
        case 'video':    renderVideo(currentData);    break;
        case 'article':  renderArticle(currentData);  break;
        case 'pdf':      renderPdf(currentData);      break;
        case 'external': renderExternal(currentData); break;
        default:         renderArticle(currentData);  break;
    }
}

function hideAllViews() {
    ['view-video','view-article','view-pdf','view-external','view-default','loading-state']
        .forEach(function(id) {
            var el = document.getElementById(id);
            el.classList.add('hidden');
            el.classList.remove('flex');
        });
}

// ════════════════════════════════════════════════════════════════════════════
// RENDER: VIDEO
// ════════════════════════════════════════════════════════════════════════════
function renderVideo(c) {
    var view = document.getElementById('view-video');
    view.classList.remove('hidden');
    view.classList.add('flex');

    document.getElementById('video-title').textContent       = c.title;
    document.getElementById('video-description').textContent = c.description || '';

    var player = document.getElementById('video-player');
    var source = document.getElementById('video-source');

    source.src = c.video_url || '';
    player.load();

    updateCompleteBtn('video-complete-btn', c.is_completed);

    if (c.is_completed) {
        document.getElementById('video-complete-hint').textContent = 'Completed ✓';
    }

    // Start periodic progress ping every 15s
    videoProgressTimer = setInterval(function() {
        if (!player.paused && player.duration > 0) {
            var pct = Math.round((player.currentTime / player.duration) * 100);
            pingProgress(c.id, pct, 15);
        }
    }, 15000);

    switchTab('overview');
}

function onVideoTimeUpdate() {
    var player = document.getElementById('video-player');
    if (!currentData || currentData.type !== 'video') return;
    if (!player.duration || currentData.is_completed) return;

    var pct = (player.currentTime / player.duration) * 100;

    // Auto-complete at 90%
    if (pct >= 90) {
        markComplete(true); // silent = true
    }
}

function onVideoEnded() {
    if (currentData && !currentData.is_completed) {
        markComplete(true);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// RENDER: ARTICLE
// ════════════════════════════════════════════════════════════════════════════
function renderArticle(c) {
    var view = document.getElementById('view-article');
    view.classList.remove('hidden');

    document.getElementById('article-title').textContent = c.title;
    document.getElementById('article-meta').textContent  = 'Article';

    // Render HTML or plain text
    var body = document.getElementById('article-body');
    body.innerHTML = c.text_content || '<p class="text-slate-400">No content available.</p>';

    updateCompleteBtn('article-complete-btn', c.is_completed);
}

// ════════════════════════════════════════════════════════════════════════════
// RENDER: PDF
// ════════════════════════════════════════════════════════════════════════════
function renderPdf(c) {
    var view = document.getElementById('view-pdf');
    view.classList.remove('hidden');
    view.classList.add('flex');

    document.getElementById('pdf-title').textContent        = c.title;
    document.getElementById('pdf-frame').src                = c.file_url || '';
    document.getElementById('pdf-download-link').href       = c.file_url || '#';

    updateCompleteBtn('pdf-complete-btn', c.is_completed);
}

// ════════════════════════════════════════════════════════════════════════════
// RENDER: EXTERNAL
// ════════════════════════════════════════════════════════════════════════════
function renderExternal(c) {
    var view = document.getElementById('view-external');
    view.classList.remove('hidden');

    document.getElementById('external-title').textContent = c.title;
    document.getElementById('external-link').href         = c.external_url || '#';
}

// ════════════════════════════════════════════════════════════════════════════
// TABS (video view)
// ════════════════════════════════════════════════════════════════════════════
function switchTab(tabName) {
    ['overview','transcript','notes'].forEach(function(t) {
        document.getElementById('pane-' + t).classList.toggle('hidden', t !== tabName);
        document.getElementById('tab-'  + t).classList.toggle('active', t === tabName);
    });
}

// ════════════════════════════════════════════════════════════════════════════
// MARK COMPLETE
// ════════════════════════════════════════════════════════════════════════════
function markComplete(silent) {
    if (!currentData || currentData.is_completed) return;

    fetch('/learner/learning/content/' + currentData.id + '/complete', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success) return;

        // Mark in local state
        CONTENTS[currentIndex].is_completed = true;
        currentData.is_completed = true;

        // Update sidebar item
        var sidebarItem = document.getElementById('sidebar-item-' + currentData.id);
        if (sidebarItem) {
            sidebarItem.classList.add('done');
            var circle = sidebarItem.querySelector('.rounded-full.border');
            if (circle) {
                circle.className = 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center bg-green-500';
                circle.innerHTML = '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
            }
        }

        // Update complete buttons
        ['video-complete-btn','article-complete-btn','pdf-complete-btn'].forEach(function(id) {
            updateCompleteBtn(id, true);
        });

        if (!silent) {
            showMiniToast('Marked as complete!');
        }

        // Week completion
        if (data.week_completed) {
            showWeekCompleteToast();
        }

        // Auto-advance after 1.5s (non-silent only)
        if (!silent && currentIndex < CONTENTS.length - 1) {
            setTimeout(function() { navigateContent(1); }, 1500);
        }
    })
    .catch(function() {
        if (!silent) showMiniToast('Could not save. Please try again.', true);
    });
}

function updateCompleteBtn(btnId, isDone) {
    var btn = document.getElementById(btnId);
    if (!btn) return;
    if (isDone) {
        btn.classList.add('done');
        btn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Completed';
        btn.onclick = null;
    } else {
        btn.classList.remove('done');
        btn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Mark as Complete';
        btn.onclick = function() { markComplete(); };
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PROGRESS PING (video)
// ════════════════════════════════════════════════════════════════════════════
var lastPingedPct = -1;
function pingProgress(contentId, pct, timeSpent) {
    if (pct === lastPingedPct) return;
    lastPingedPct = pct;

    fetch('/learner/learning/content/' + contentId + '/progress', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ progress_percentage: pct, time_spent: timeSpent })
    }).catch(function() {});
}

// ════════════════════════════════════════════════════════════════════════════
// NOTES (localStorage — browser only)
// ════════════════════════════════════════════════════════════════════════════
function saveNote() {
    if (!currentData) return;
    var key = 'note_' + ENROLLMENT_ID + '_' + currentData.id;
    try {
        localStorage.setItem(key, document.getElementById('notes-textarea').value);
    } catch(e) {}
}

function restoreNote(contentId) {
    var key = 'note_' + ENROLLMENT_ID + '_' + contentId;
    try {
        var val = localStorage.getItem(key) || '';
        document.getElementById('notes-textarea').value = val;
    } catch(e) {}
}

// ════════════════════════════════════════════════════════════════════════════
// TOASTS
// ════════════════════════════════════════════════════════════════════════════
function showMiniToast(msg, isError) {
    var color = isError ? 'bg-red-600' : 'bg-slate-900';
    var el = document.createElement('div');
    el.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 ' + color + ' text-white text-sm font-semibold px-5 py-3 rounded-2xl shadow-xl z-50 transition-all';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function() { el.remove(); }, 2500);
}

function showWeekCompleteToast() {
    var toast = document.getElementById('week-complete-toast');
    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(function() {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 4000);
}

// ════════════════════════════════════════════════════════════════════════════
// ASSESSMENT placeholder — Batch 3
// ════════════════════════════════════════════════════════════════════════════
function loadAssessment(assessmentId) {
    // Assessment rendering will be implemented in Batch 3
    showMiniToast('Assessment view coming in the next update.');
}
</script>
@endpush