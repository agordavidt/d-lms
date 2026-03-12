{{--
    Partial: resources/views/learner/learning/partials/_views.blade.php
    All content view panels — rendered/swapped by learning.js
--}}

{{-- ─── VIDEO ──────────────────────────────────────────────────────────── --}}
<div id="view-video" class="hidden flex-col">
    <div class="video-wrapper">
        <video id="video-player" controls preload="metadata"
               onended="onVideoEnded()" ontimeupdate="onVideoTimeUpdate()">
            <source id="video-source" src="" type="video/mp4">
            Your browser does not support video.
        </video>
    </div>

    <div class="tab-bar px-5 flex gap-0 flex-shrink-0">
        <button class="tab-btn active" onclick="switchTab('overview')"    id="tab-overview">Overview</button>
        <button class="tab-btn"        onclick="switchTab('transcript')"  id="tab-transcript">Transcript</button>
        <button class="tab-btn"        onclick="switchTab('notes')"       id="tab-notes">Notes</button>
    </div>

    {{-- Overview --}}
    <div id="pane-overview" class="px-6 py-6 max-w-3xl">
        <h2 class="text-xl font-black text-slate-900 mb-2" id="video-title"></h2>
        <p class="text-slate-500 text-sm mb-6" id="video-description"></p>
        <button id="video-complete-btn" class="complete-btn" onclick="markComplete()">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            Mark as Complete
        </button>
        <p class="text-xs text-slate-400 mt-2" id="video-complete-hint">
            Or keep watching — it'll auto-complete at 90%
        </p>
    </div>

    {{-- Transcript --}}
    <div id="pane-transcript" class="hidden px-6 py-6 max-w-3xl">
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-bold text-amber-900">Transcripts coming soon</p>
                <p class="text-xs text-amber-700 mt-1">AI-generated transcripts with click-to-seek will appear here.</p>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div id="pane-notes" class="hidden px-6 py-6 max-w-3xl">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-3">My Notes</p>
        <textarea id="notes-textarea" rows="8"
            placeholder="Write notes while watching…"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-700 placeholder-slate-400 outline-none resize-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all"
            onchange="saveNote()"></textarea>
        <p class="text-[11px] text-slate-400 mt-2">Notes are saved per content item in your browser.</p>
    </div>
</div>

{{-- ─── ARTICLE ─────────────────────────────────────────────────────────── --}}
<div id="view-article" class="hidden">
    <div class="max-w-3xl mx-auto px-6 py-8">
        <h1 class="text-2xl font-black text-slate-900 mb-2" id="article-title"></h1>
        <p class="text-sm text-slate-400 mb-8" id="article-meta"></p>
        <div class="article-body prose" id="article-body"></div>
        <div class="mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center gap-4">
            <button id="article-complete-btn" class="complete-btn" onclick="markComplete()">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Mark as Complete
            </button>
            <button onclick="navigateContent(1)" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                Next lesson →
            </button>
        </div>
    </div>
</div>

{{-- ─── PDF ─────────────────────────────────────────────────────────────── --}}
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

{{-- ─── EXTERNAL ────────────────────────────────────────────────────────── --}}
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
           class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors mb-6">
            Open Resource
        </a>
        <div>
            <button class="complete-btn" onclick="markComplete()">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Mark as Complete
            </button>
        </div>
    </div>
</div>

{{-- ─── ASSESSMENT ──────────────────────────────────────────────────────── --}}
<div id="view-assessment" class="hidden">
    <div class="max-w-3xl mx-auto px-5 py-8">

        {{-- Loading skeleton --}}
        <div id="assessment-loading" class="hidden space-y-4">
            <div class="skeleton h-8 w-1/2 mb-2"></div>
            <div class="skeleton h-4 w-3/4 mb-6"></div>
            <div class="skeleton h-16 w-full rounded-2xl"></div>
            <div class="skeleton h-16 w-full rounded-2xl"></div>
        </div>

        {{-- Intro --}}
        <div id="assessment-intro" class="hidden">
            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-widest text-indigo-500 mb-2">Week Assessment</p>
                <h2 class="text-2xl font-black text-slate-900 mb-2" id="a-title"></h2>
                <p class="text-slate-500 text-sm" id="a-description"></p>
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
                    <p class="text-2xl font-black text-slate-900" id="a-time-limit">—</p>
                </div>
            </div>

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
                class="inline-flex items-center gap-2 bg-indigo-600 text-white font-bold px-8 py-4 rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-300/20 text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="btn-start-label">Start Assessment</span>
            </button>
        </div>

        {{-- Quiz --}}
        <div id="assessment-quiz" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-bold text-slate-500">
                    Question <span id="q-current-num" class="text-slate-800">1</span>
                    of <span id="q-total-num" class="text-slate-800">1</span>
                </p>
                <div id="timer-display" class="hidden items-center gap-1.5 text-sm font-bold text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="timer-value">00:00</span>
                </div>
            </div>

            <div class="quiz-progress-bar mb-6">
                <div class="quiz-progress-fill" id="quiz-progress-fill" style="width:0%"></div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
                <p class="text-base font-bold text-slate-900 leading-relaxed mb-6" id="q-text"></p>
                <div id="q-options" class="space-y-3"></div>
                <div id="q-short-answer" class="hidden">
                    <textarea id="q-textarea" rows="4"
                        placeholder="Type your answer here…"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 outline-none resize-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all"
                        oninput="onShortAnswerInput()"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button onclick="quizPrev()" id="btn-quiz-prev"
                    class="text-sm font-bold text-slate-600 hover:text-slate-900 px-4 py-2.5 rounded-xl hover:bg-slate-100 disabled:opacity-30"
                    disabled>← Previous</button>
                <div class="flex items-center gap-3">
                    <button onclick="quizNext()" id="btn-quiz-next"
                        class="bg-slate-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-slate-700 transition-all">
                        Next →
                    </button>
                    <button onclick="submitAssessment()" id="btn-quiz-submit"
                        class="hidden bg-indigo-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-300/20">
                        Submit Assessment
                    </button>
                </div>
            </div>
        </div>

        {{-- Submitting --}}
        <div id="assessment-submitting" class="hidden text-center py-16">
            <div class="w-12 h-12 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-slate-500 font-medium">Submitting your answers…</p>
        </div>

        {{-- Results --}}
        <div id="assessment-results" class="hidden">
            <div class="text-center mb-8">
                <div class="score-ring mx-auto mb-4">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                        <circle cx="60" cy="60" r="52" fill="none" id="score-circle"
                                stroke="#4f46e5" stroke-width="10"
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
                    class="text-sm font-bold text-slate-700 bg-white border border-slate-200 px-5 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                    Retake Assessment
                </button>
                <button onclick="navigateContent(1)"
                    class="text-sm font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-5 py-3 rounded-xl hover:bg-indigo-100 transition-colors">
                    Continue →
                </button>
            </div>
        </div>

    </div>
</div>

{{-- ─── DEFAULT ─────────────────────────────────────────────────────────── --}}
<div id="view-default" class="hidden flex flex-col items-center justify-center py-24 text-center px-6">
    <div class="text-5xl mb-4">📖</div>
    <h3 class="text-lg font-bold text-slate-700 mb-2">Select a lesson to begin</h3>
    <p class="text-slate-400 text-sm">Choose any item from the course navigation on the left.</p>
</div>