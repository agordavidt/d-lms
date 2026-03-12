
// ── Boot ──────────────────────────────────────────────────────────────────
var CONTENTS        = [];
var ENROLLMENT_ID   = null;
var CURRENT_WEEK_ID = null;
var CSRF            = '';

// Runtime state
var currentIndex       = 0;
var currentData        = null;
var activeWeekId       = null;
var videoProgressTimer = null;

// Assessment state
var ASSESSMENT_DATA   = null;
var attemptId         = null;
var answers           = {};
var currentQIndex     = 0;
var timerInterval     = null;
var timerSecondsLeft  = 0;

document.addEventListener('DOMContentLoaded', function () {
    var cd = document.getElementById('contents-data');
    var ei = document.getElementById('enrollment-id');
    var wi = document.getElementById('current-week-id');
    var cs = document.querySelector('meta[name="csrf-token"]');

    if (!cd || !ei || !wi || !cs) return;

    CONTENTS        = JSON.parse(cd.textContent);
    ENROLLMENT_ID   = JSON.parse(ei.textContent);
    CURRENT_WEEK_ID = JSON.parse(wi.textContent);
    CSRF            = cs.content;
    activeWeekId    = CURRENT_WEEK_ID;

    if (!CONTENTS.length) return;
    var first = CONTENTS.findIndex(function (c) { return !c.is_completed; });
    loadContent(CONTENTS[first >= 0 ? first : 0].id);
});

// ── Helpers ───────────────────────────────────────────────────────────────
function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

// ── Sidebar toggle ────────────────────────────────────────────────────────
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var collapsed = sidebar.classList.toggle('collapsed');
    overlay.classList.toggle('show', !collapsed && window.innerWidth <= 768);
}

// ── Week accordion ────────────────────────────────────────────────────────
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

    // Close others
    document.querySelectorAll('[id^="week-contents-"]').forEach(function (e) { e.classList.add('hidden'); });
    document.querySelectorAll('[class*="chevron-"]').forEach(function (e) { e.classList.remove('rotate-180'); });
    document.querySelectorAll('.week-row').forEach(function (e) { e.classList.remove('open'); });

    // Current week already has DOM — just open
    if (weekId === CURRENT_WEEK_ID) {
        el.classList.remove('hidden');
        if (chev) chev.classList.add('rotate-180');
        row.classList.add('open');
        return;
    }

    // Skeleton while loading
    el.innerHTML = '<div class="px-4 py-3 space-y-2">'
        + '<div class="skeleton h-8 rounded-xl"></div>'
        + '<div class="skeleton h-8 rounded-xl"></div>'
        + '<div class="skeleton h-8 rounded-xl"></div>'
        + '</div>';
    el.classList.remove('hidden');
    if (chev) chev.classList.add('rotate-180');
    row.classList.add('open');

    fetch('/learner/learning/' + ENROLLMENT_ID + '/week/' + weekId + '/contents', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success) {
            el.innerHTML = '<p class="px-5 py-3 text-xs text-red-500">' + escHtml(data.message || 'Failed to load.') + '</p>';
            return;
        }
        CONTENTS = data.contents;
        if (data.assessment) {
            CONTENTS.push({
                id: 'assessment-' + data.assessment.id,
                title: data.assessment.title,
                type: 'assessment',
                is_completed: data.assessment.is_submitted,
                _assessment_id: data.assessment.id,
                _assessment: data.assessment
            });
        }
        activeWeekId  = weekId;
        currentIndex  = 0;
        el.innerHTML  = buildSidebarItems(data.contents, data.assessment);
        if (data.contents.length > 0) loadContent(data.contents[0].id);
    })
    .catch(function () {
        el.innerHTML = '<p class="px-5 py-3 text-xs text-red-500">Failed to load. Please try again.</p>';
    });
}

function buildSidebarItems(contents, assessment) {
    var html = '';
    contents.forEach(function (c) {
        var icon  = c.type === 'video' ? '▶' : c.type === 'article' ? '📄' : c.type === 'pdf' ? '📎' : '•';
        var done  = c.is_completed;
        var circ  = done
            ? 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center bg-green-500'
            : 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center border border-slate-300';
        var check = done
            ? '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>'
            : '';
        var dur   = c.video_duration ? ' · ' + c.video_duration + ' min' : '';
        html += '<div class="content-item ' + (done ? 'done' : '') + '" id="sidebar-item-' + c.id
            + '" onclick="loadContent(' + c.id + ')">'
            + '<div class="' + circ + '">' + check + '</div>'
            + '<div class="min-w-0 flex-1"><p class="item-title text-[12px] text-slate-700 leading-snug line-clamp-2">'
            + escHtml(c.title) + '</p><p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">'
            + '<span>' + icon + '</span><span>' + cap(c.type) + '</span>' + dur + '</p></div></div>';
    });
    if (assessment) {
        var ad = assessment.is_submitted;
        var ac = ad
            ? 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center bg-green-500'
            : 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center border border-slate-300';
        var ak = ad
            ? '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>'
            : '';
        html += '<div class="content-item ' + (ad ? 'done' : '') + '" id="sidebar-item-assessment-' + assessment.id
            + '" onclick="loadAssessment(' + assessment.id + ')">'
            + '<div class="' + ac + '">' + ak + '</div>'
            + '<div class="min-w-0"><p class="item-title text-[12px] text-slate-700 leading-snug">Week Assessment</p>'
            + '<p class="text-[11px] text-slate-400 mt-0.5">✏️ Quiz · ' + assessment.question_count + ' questions</p>'
            + '</div></div>';
    }
    return html;
}

// ── Navigation ────────────────────────────────────────────────────────────
function navigateContent(dir) {
    var idx = currentIndex + dir;
    if (idx < 0 || idx >= CONTENTS.length) return;
    var item = CONTENTS[idx];
    item.type === 'assessment' ? loadAssessment(item._assessment_id) : loadContent(item.id);
}

// ── Load content ──────────────────────────────────────────────────────────
function loadContent(contentId) {
    var idx = CONTENTS.findIndex(function (c) { return c.id === contentId; });
    if (idx === -1) return;

    currentIndex = idx;
    currentData  = CONTENTS[idx];

    setSidebarActive('sidebar-item-' + contentId);
    document.getElementById('topbar-title').textContent = currentData.title;
    document.getElementById('btn-prev').disabled = currentIndex === 0;
    document.getElementById('btn-next').disabled = currentIndex === CONTENTS.length - 1;

    if (videoProgressTimer) clearInterval(videoProgressTimer);
    restoreNote(contentId);
    hideAllViews();

    switch (currentData.type) {
        case 'video':    renderVideo(currentData);    break;
        case 'article':  renderArticle(currentData);  break;
        case 'pdf':      renderPdf(currentData);      break;
        case 'external': renderExternal(currentData); break;
        default:         renderArticle(currentData);  break;
    }
}

function setSidebarActive(id) {
    document.querySelectorAll('.content-item').forEach(function (el) { el.classList.remove('active'); });
    var el = document.getElementById(id);
    if (el) el.classList.add('active');
}

function hideAllViews() {
    ['view-video','view-article','view-pdf','view-external','view-default','view-assessment','loading-state']
        .forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
        });
    stopTimer();
}

// ── Render: Video ─────────────────────────────────────────────────────────
function renderVideo(c) {
    var view = document.getElementById('view-video');
    view.classList.remove('hidden'); view.classList.add('flex');
    document.getElementById('video-title').textContent       = c.title;
    document.getElementById('video-description').textContent = c.description || '';
    var player = document.getElementById('video-player');
    document.getElementById('video-source').src = c.video_url || '';
    player.load();
    updateCompleteBtn('video-complete-btn', c.is_completed);
    document.getElementById('video-complete-hint').textContent = c.is_completed
        ? 'Completed ✓' : 'Or keep watching — it\'ll auto-complete at 90%';
    videoProgressTimer = setInterval(function () {
        if (!player.paused && player.duration > 0) {
            pingProgress(c.id, Math.round((player.currentTime / player.duration) * 100), 15);
        }
    }, 15000);
    switchTab('overview');
}

function onVideoTimeUpdate() {
    var player = document.getElementById('video-player');
    if (!currentData || currentData.type !== 'video' || !player.duration || currentData.is_completed) return;
    if ((player.currentTime / player.duration) * 100 >= 90) markComplete(true);
}
function onVideoEnded() { if (currentData && !currentData.is_completed) markComplete(true); }

// ── Render: Article ───────────────────────────────────────────────────────
function renderArticle(c) {
    var view = document.getElementById('view-article');
    view.classList.remove('hidden');
    document.getElementById('article-title').textContent = c.title;
    document.getElementById('article-meta').textContent  = 'Article';
    document.getElementById('article-body').innerHTML    = c.text_content || '<p class="text-slate-400">No content available.</p>';
    updateCompleteBtn('article-complete-btn', c.is_completed);
}

// ── Render: PDF ───────────────────────────────────────────────────────────
function renderPdf(c) {
    var view = document.getElementById('view-pdf');
    view.classList.remove('hidden'); view.classList.add('flex');
    document.getElementById('pdf-title').textContent        = c.title;
    document.getElementById('pdf-frame').src                = c.file_url || '';
    document.getElementById('pdf-download-link').href       = c.file_url || '#';
    updateCompleteBtn('pdf-complete-btn', c.is_completed);
}

// ── Render: External ──────────────────────────────────────────────────────
function renderExternal(c) {
    var view = document.getElementById('view-external');
    view.classList.remove('hidden');
    document.getElementById('external-title').textContent = c.title;
    document.getElementById('external-link').href         = c.external_url || '#';
}

// ── Tabs ──────────────────────────────────────────────────────────────────
function switchTab(t) {
    ['overview','transcript','notes'].forEach(function (n) {
        document.getElementById('pane-' + n).classList.toggle('hidden', n !== t);
        document.getElementById('tab-'  + n).classList.toggle('active', n === t);
    });
}

// ── Mark complete ─────────────────────────────────────────────────────────
function markComplete(silent) {
    if (!currentData || currentData.is_completed) return;
    fetch('/learner/learning/content/' + currentData.id + '/complete', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success) return;
        CONTENTS[currentIndex].is_completed = true;
        currentData.is_completed = true;

        var si = document.getElementById('sidebar-item-' + currentData.id);
        if (si) {
            si.classList.add('done');
            var circle = si.querySelector('.rounded-full.border, .rounded-full.border-slate-300');
            if (circle) {
                circle.className = 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center bg-green-500';
                circle.innerHTML = '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
            }
        }
        ['video-complete-btn','article-complete-btn','pdf-complete-btn'].forEach(function (id) { updateCompleteBtn(id, true); });
        if (!silent) showMiniToast('Marked as complete!');
        if (data.week_completed) showWeekCompleteToast();
        if (!silent && currentIndex < CONTENTS.length - 1) setTimeout(function () { navigateContent(1); }, 1500);
    })
    .catch(function () { if (!silent) showMiniToast('Could not save. Please try again.', true); });
}

function updateCompleteBtn(btnId, isDone) {
    var btn = document.getElementById(btnId);
    if (!btn) return;
    var svg = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
    if (isDone) {
        btn.classList.add('done'); btn.innerHTML = svg + ' Completed'; btn.onclick = null;
    } else {
        btn.classList.remove('done'); btn.innerHTML = svg + ' Mark as Complete';
        btn.onclick = function () { markComplete(); };
    }
}

// ── Progress ping ─────────────────────────────────────────────────────────
var lastPingedPct = -1;
function pingProgress(contentId, pct, timeSpent) {
    if (pct === lastPingedPct) return;
    lastPingedPct = pct;
    fetch('/learner/learning/content/' + contentId + '/progress', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ progress_percentage: pct, time_spent: timeSpent })
    }).catch(function () {});
}

// ── Notes ─────────────────────────────────────────────────────────────────
function saveNote() {
    if (!currentData) return;
    try { localStorage.setItem('note_' + ENROLLMENT_ID + '_' + currentData.id, document.getElementById('notes-textarea').value); } catch (e) {}
}
function restoreNote(contentId) {
    try { document.getElementById('notes-textarea').value = localStorage.getItem('note_' + ENROLLMENT_ID + '_' + contentId) || ''; } catch (e) {}
}

// ═══════════════════════════════════════════════════════
// ASSESSMENT ENGINE
// ═══════════════════════════════════════════════════════
function loadAssessment(assessmentId) {
    hideAllViews();
    setSidebarActive('sidebar-item-assessment-' + assessmentId);
    document.getElementById('topbar-title').textContent = 'Week Assessment';
    document.getElementById('btn-prev').disabled = currentIndex <= 0;
    document.getElementById('btn-next').disabled = currentIndex >= CONTENTS.length - 1;

    var view = document.getElementById('view-assessment');
    view.classList.remove('hidden');
    showAssessmentState('loading');

    fetch('/learner/learning/' + ENROLLMENT_ID + '/assessment/' + assessmentId, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success) { showMiniToast(data.message || 'Could not load.', true); showAssessmentState(''); return; }
        ASSESSMENT_DATA = data;
        renderAssessmentIntro(data);
        showAssessmentState('intro');
    })
    .catch(function () { showMiniToast('Failed to load assessment.', true); });
}

function showAssessmentState(state) {
    ['loading','intro','quiz','submitting','results'].forEach(function (s) {
        var el = document.getElementById('assessment-' + s);
        if (el) el.classList.toggle('hidden', s !== state);
    });
}

// ── Intro ─────────────────────────────────────────────────────────────────
function renderAssessmentIntro(data) {
    var a = data.assessment;
    document.getElementById('a-title').textContent       = a.title;
    document.getElementById('a-description').textContent = a.description || '';
    document.getElementById('a-q-count').textContent     = data.questions.length;
    document.getElementById('a-pass-mark').textContent   = a.passing_score + '%';

    var timeBox = document.getElementById('a-time-box');
    if (a.time_limit) {
        document.getElementById('a-time-limit').textContent = a.time_limit + ' min';
        timeBox.classList.remove('hidden');
    } else {
        timeBox.classList.add('hidden');
    }

    var prevEl = document.getElementById('a-prev-attempt');
    if (data.latest_attempt) {
        var la = data.latest_attempt;
        document.getElementById('a-prev-score').textContent = la.score + '% — ' + (la.passed ? '✓ Passed' : '✗ Not passed');
        document.getElementById('a-prev-date').textContent  = 'Submitted ' + la.submitted_at;
        prevEl.classList.remove('hidden');
        document.getElementById('btn-start-label').textContent = 'Retake Assessment';
    } else {
        prevEl.classList.add('hidden');
        document.getElementById('btn-start-label').textContent = 'Start Assessment';
    }
}

// ── Start ─────────────────────────────────────────────────────────────────
function startAssessment() {
    if (!ASSESSMENT_DATA) return;
    fetch('/learner/assessments/' + ASSESSMENT_DATA.assessment.id + '/attempt', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ enrollment_id: ENROLLMENT_ID })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        attemptId = data.attempt_id || (data.attempt && data.attempt.id);
        if (!attemptId) { showMiniToast('Could not start attempt.', true); return; }
        answers = {}; currentQIndex = 0;
        showAssessmentState('quiz');
        renderQuestion(0);
        if (ASSESSMENT_DATA.assessment.time_limit) {
            timerSecondsLeft = ASSESSMENT_DATA.assessment.time_limit * 60;
            var td = document.getElementById('timer-display');
            td.classList.remove('hidden'); td.classList.add('flex');
            timerInterval = setInterval(tickTimer, 1000);
        }
    })
    .catch(function () { showMiniToast('Server error. Please try again.', true); });
}

function retakeAssessment() {
    showAssessmentState('intro');
    renderAssessmentIntro(ASSESSMENT_DATA);
}

// ── Timer ─────────────────────────────────────────────────────────────────
function tickTimer() {
    timerSecondsLeft--;
    var m = Math.floor(timerSecondsLeft / 60);
    var s = timerSecondsLeft % 60;
    document.getElementById('timer-value').textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    if (timerSecondsLeft <= 60) document.getElementById('timer-value').classList.add('timer-warning');
    if (timerSecondsLeft <= 0) { stopTimer(); submitAssessment(); }
}
function stopTimer() { if (timerInterval) { clearInterval(timerInterval); timerInterval = null; } }

// ── Render question ───────────────────────────────────────────────────────
function renderQuestion(idx) {
    var questions = ASSESSMENT_DATA.questions;
    var q = questions[idx];
    currentQIndex = idx;

    document.getElementById('q-current-num').textContent = idx + 1;
    document.getElementById('q-total-num').textContent   = questions.length;
    document.getElementById('q-text').textContent        = q.text;
    document.getElementById('quiz-progress-fill').style.width = ((idx + 1) / questions.length * 100) + '%';

    var isShort = q.type === 'short_answer';
    document.getElementById('q-options').classList.toggle('hidden', isShort);
    document.getElementById('q-short-answer').classList.toggle('hidden', !isShort);
    if (isShort) { document.getElementById('q-textarea').value = answers[q.id] || ''; }
    else { renderOptions(q); }

    document.getElementById('btn-quiz-prev').disabled = idx === 0;
    var isLast = idx === questions.length - 1;
    document.getElementById('btn-quiz-next').classList.toggle('hidden', isLast);
    document.getElementById('btn-quiz-submit').classList.toggle('hidden', !isLast);
}

function renderOptions(q) {
    var container = document.getElementById('q-options');
    container.innerHTML = '';
    q.options.forEach(function (opt) {
        var selected = answers[q.id] === opt;
        var div = document.createElement('div');
        div.className = 'quiz-option' + (selected ? ' selected' : '');
        div.onclick   = function () { selectOption(q.id, opt, div, container); };
        div.innerHTML = '<div class="quiz-radio"></div><span class="text-sm text-slate-800 leading-snug">' + escHtml(opt) + '</span>';
        container.appendChild(div);
    });
}

function selectOption(questionId, value, el, container) {
    container.querySelectorAll('.quiz-option').forEach(function (e) { e.classList.remove('selected'); });
    el.classList.add('selected');
    answers[questionId] = value;
}

function onShortAnswerInput() {
    if (!ASSESSMENT_DATA) return;
    answers[ASSESSMENT_DATA.questions[currentQIndex].id] = document.getElementById('q-textarea').value;
}

function quizPrev() { if (currentQIndex > 0) renderQuestion(currentQIndex - 1); }
function quizNext() { if (currentQIndex < ASSESSMENT_DATA.questions.length - 1) renderQuestion(currentQIndex + 1); }

// ── Submit ────────────────────────────────────────────────────────────────
function submitAssessment() {
    if (!attemptId) return;
    stopTimer();
    showAssessmentState('submitting');

    var answersArray = (ASSESSMENT_DATA ? ASSESSMENT_DATA.questions : []).map(function (q) {
        return { question_id: q.id, answer: answers[q.id] !== undefined ? answers[q.id] : null };
    });

    fetch('/learner/attempts/' + attemptId + '/submit', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ answers: answersArray })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success || data.score !== undefined) { renderResults(data); showAssessmentState('results'); }
        else { showMiniToast(data.message || 'Submission failed.', true); showAssessmentState('quiz'); }
    })
    .catch(function () { showMiniToast('Submission failed. Please try again.', true); showAssessmentState('quiz'); });
}

// ── Results ───────────────────────────────────────────────────────────────
function renderResults(data) {
    var score   = data.score || 0;
    var passing = ASSESSMENT_DATA ? ASSESSMENT_DATA.assessment.passing_score : 70;
    var passed  = score >= passing;
    var circum  = 326.7;
    var offset  = circum - (score / 100) * circum;

    var circle = document.getElementById('score-circle');
    circle.style.stroke           = passed ? '#16a34a' : '#dc2626';
    circle.style.strokeDashoffset = circum;
    setTimeout(function () { circle.style.strokeDashoffset = offset; }, 50);

    document.getElementById('result-pct').textContent      = score + '%';
    document.getElementById('result-headline').textContent = passed ? '🎉 You passed!' : 'Keep going — you can do this';
    document.getElementById('result-subline').textContent  = passed
        ? 'You scored ' + score + '% — above the ' + passing + '% pass mark.'
        : 'You scored ' + score + '%. The pass mark is ' + passing + '%. Review and try again.';

    var breakdown = document.getElementById('result-breakdown');
    breakdown.innerHTML = '';
    (data.question_results || []).forEach(function (qr, i) {
        var row  = document.createElement('div');
        row.className = 'px-5 py-4 flex items-start gap-3';
        var icon = qr.is_correct
            ? '<div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>'
            : '<div class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div>';
        row.innerHTML = icon + '<div class="min-w-0"><p class="text-sm text-slate-800 font-medium">'
            + escHtml('Q' + (i + 1) + '. ' + (qr.question_text || '')) + '</p>'
            + (!qr.is_correct && qr.correct_answer ? '<p class="text-xs text-green-600 mt-1 font-medium">Correct: ' + escHtml(String(qr.correct_answer)) + '</p>' : '')
            + '</div>';
        breakdown.appendChild(row);
    });

    // Mark sidebar assessment as done if passed
    var sai = document.getElementById('sidebar-item-assessment-' + (ASSESSMENT_DATA ? ASSESSMENT_DATA.assessment.id : ''));
    if (sai && passed) {
        sai.classList.add('done');
        var ac = sai.querySelector('.rounded-full');
        if (ac) {
            ac.className = 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center bg-green-500';
            ac.innerHTML = '<svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
        }
    }
}

// ── Toasts ────────────────────────────────────────────────────────────────
function showMiniToast(msg, isError) {
    var el = document.createElement('div');
    el.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 ' + (isError ? 'bg-red-600' : 'bg-slate-900')
        + ' text-white text-sm font-semibold px-5 py-3 rounded-2xl shadow-xl z-50';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function () { el.remove(); }, 2500);
}

function showWeekCompleteToast() {
    var toast = document.getElementById('week-complete-toast');
    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(function () { toast.classList.add('translate-y-20', 'opacity-0'); }, 4000);
}