@extends('layouts.learner')

@section('title', 'My Learning')

@push('styles')
<style>
/* ── Base ─────────────────────────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600;700&display=swap');

:root {
    --blue:       #0056d2;
    --blue-light: #e8f0fd;
    --blue-mid:   #ccdcf8;
    --text-primary:   #1c1d1f;
    --text-secondary: #5c5c5c;
    --text-muted:     #9ca3af;
    --border:         #e5e7eb;
    --border-light:   #f3f4f6;
    --surface:        #ffffff;
    --bg:             #f8f9fa;
    --green:      #1a9048;
    --green-light:#e6f4ec;
    --radius-sm:  8px;
    --radius-md:  12px;
    --radius-lg:  16px;
}

body { background: var(--bg); font-family: 'DM Sans', sans-serif; }

/* ── Layout ──────────────────────────────────────────────────────────────── */
.ml-layout {
    max-width: 1280px;
    margin: 0 auto;
    padding: 32px 24px;
}
.ml-body {
    display: flex;
    gap: 32px;
    align-items: flex-start;
}
.ml-sidebar {
    width: 300px;
    min-width: 300px;
    flex-shrink: 0;
}
.ml-main { flex: 1; min-width: 0; }

/* ── Greeting ─────────────────────────────────────────────────────────────── */
.greeting-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
}
.greeting-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--blue);
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-family: 'Source Serif 4', serif;
}
.greeting-text h1 {
    font-size: 26px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.1;
    font-family: 'Source Serif 4', serif;
}
.greeting-text p {
    font-size: 13px;
    color: var(--text-secondary);
    margin-top: 2px;
}

/* ── Pending banner ──────────────────────────────────────────────────────── */
.pending-banner {
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: var(--radius-md);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
    font-size: 13px;
}
.pending-banner .pb-text { flex: 1; }
.pending-banner .pb-title { font-weight: 700; color: #92400e; }
.pending-banner .pb-sub   { color: #b45309; margin-top: 2px; }
.btn-pending {
    background: #f59e0b;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 16px;
    border-radius: var(--radius-sm);
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s;
    text-decoration: none;
}
.btn-pending:hover { background: #d97706; }

/* ── Section cards (sidebar) ─────────────────────────────────────────────── */
.sidebar-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 16px;
}
.sidebar-card-header {
    padding: 16px 20px 12px;
    border-bottom: 1px solid var(--border-light);
}
.sidebar-card-header h3 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
}
.sidebar-card-header p {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* ── Calendar ────────────────────────────────────────────────────────────── */
.cal-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px 8px;
}
.cal-nav-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
}
.cal-nav-btn {
    background: none;
    border: 1px solid var(--border);
    border-radius: 6px;
    width: 28px;
    height: 28px;
    cursor: pointer;
    color: var(--text-secondary);
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.12s;
}
.cal-nav-btn:hover { background: var(--border-light); }
.cal-grid {
    padding: 0 16px 16px;
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}
.cal-day-name {
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    padding: 4px 0;
    letter-spacing: 0.04em;
}
.cal-day {
    text-align: center;
    font-size: 12px;
    color: var(--text-secondary);
    padding: 5px 2px;
    border-radius: 6px;
    cursor: default;
    transition: background 0.1s;
    position: relative;
}
.cal-day.today {
    background: var(--blue);
    color: #fff;
    font-weight: 700;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    line-height: 28px;
    padding: 0;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cal-day.has-session::after {
    content: '';
    display: block;
    width: 4px;
    height: 4px;
    background: var(--blue);
    border-radius: 50%;
    position: absolute;
    bottom: 1px;
    left: 50%;
    transform: translateX(-50%);
}
.cal-day.today.has-session::after { background: rgba(255,255,255,0.7); }
.cal-day.other-month { color: var(--border); }

/* ── Session list in sidebar ─────────────────────────────────────────────── */
.session-list { padding: 0; }
.session-item {
    padding: 12px 20px;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.session-item:last-child { border-bottom: none; }
.session-dot-wrap {
    padding-top: 4px;
    flex-shrink: 0;
}
.session-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--blue);
}
.session-dot.green  { background: #10b981; }
.session-dot.orange { background: #f59e0b; }
.session-dot.purple { background: #8b5cf6; }
.session-info { min-width: 0; }
.session-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.session-meta {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}
.session-join {
    font-size: 11px;
    font-weight: 700;
    color: var(--blue);
    text-decoration: none;
    display: inline-block;
    margin-top: 3px;
}
.session-join:hover { text-decoration: underline; }
.session-date-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted);
    padding: 8px 20px 4px;
    background: var(--border-light);
}

/* ── Tabs ────────────────────────────────────────────────────────────────── */
.tab-pills {
    display: flex;
    gap: 4px;
    margin-bottom: 20px;
}
.tab-pill {
    padding: 8px 18px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.tab-pill.active {
    background: var(--text-primary);
    color: #fff;
}
.tab-pill.inactive {
    background: var(--surface);
    color: var(--text-secondary);
    border: 1px solid var(--border);
}
.tab-pill.inactive:hover { background: var(--border-light); }

/* ── Course cards (horizontal list) ─────────────────────────────────────── */
.course-list { display: flex; flex-direction: column; gap: 0; }
.course-card-h {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 10px;
    transition: box-shadow 0.15s, border-color 0.15s;
}
.course-card-h:hover {
    border-color: #c7d7f4;
    box-shadow: 0 2px 12px rgba(0,86,210,0.07);
}
.course-card-h:last-child { margin-bottom: 0; }

/* program icon */
.cc-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-sm);
    background: var(--blue-light);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    overflow: hidden;
}
.cc-icon img { width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm); }

/* main info */
.cc-info { flex: 1; min-width: 0; }
.cc-program {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted);
    margin-bottom: 3px;
}
.cc-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.3;
    margin-bottom: 5px;
    font-family: 'Source Serif 4', serif;
}
.cc-meta {
    font-size: 12px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}
.cc-meta-sep { color: var(--border); }
.cc-progress-bar {
    height: 4px;
    background: var(--border);
    border-radius: 999px;
    overflow: hidden;
    max-width: 380px;
}
.cc-progress-fill {
    height: 100%;
    background: var(--blue);
    border-radius: 999px;
    transition: width 0.6s ease;
}
.cc-progress-fill.green { background: var(--green); }

/* action */
.cc-action {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    margin-left: 16px;
}
.btn-resume {
    background: var(--blue);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 9px 22px;
    border-radius: var(--radius-sm);
    border: none;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s;
    display: inline-block;
}
.btn-resume:hover { background: #0044aa; color: #fff; }
.btn-review {
    background: transparent;
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 700;
    padding: 9px 22px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s, border-color 0.15s;
    display: inline-block;
}
.btn-review:hover { background: var(--border-light); border-color: #c5c8cc; }

/* completion badge */
.cc-complete-badge {
    font-size: 11px;
    font-weight: 700;
    color: var(--green);
    background: var(--green-light);
    padding: 3px 10px;
    border-radius: 999px;
}

/* ── Empty state ─────────────────────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 56px 24px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
}
.empty-state h3 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
    font-family: 'Source Serif 4', serif;
}
.empty-state p { font-size: 14px; color: var(--text-secondary); margin-bottom: 20px; }
.btn-explore {
    background: var(--blue);
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    padding: 11px 28px;
    border-radius: var(--radius-sm);
    text-decoration: none;
    display: inline-block;
    transition: background 0.15s;
}
.btn-explore:hover { background: #0044aa; color: #fff; }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .ml-body { flex-direction: column; }
    .ml-sidebar { width: 100%; min-width: 0; }
    .cc-progress-bar { max-width: 100%; }
}
@media (max-width: 600px) {
    .course-card-h { flex-wrap: wrap; }
    .cc-action { width: 100%; flex-direction: row; align-items: center; margin-left: 0; }
    .greeting-text h1 { font-size: 20px; }
}
</style>
@endpush

@section('content')
@php
    $user      = auth()->user();
    $firstName = $user->first_name ?? explode(' ', $user->name)[0];
    $hour      = now()->hour;
    $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $initial   = strtoupper(substr($firstName, 0, 1));

    // Session days for calendar dots
    $sessionDays = $upcomingSessions->map(fn($s) => $s->start_time->format('Y-m-d'))->unique()->values();
@endphp

<div class="ml-layout">

    {{-- ── Greeting ──────────────────────────────────────────────────────── --}}
    <div class="greeting-row">
        <div class="greeting-avatar">{{ $initial }}</div>
        <div class="greeting-text">
            <h1>{{ $greeting }}, {{ $firstName }}</h1>
            <p>
                @if($enrollments->where('status','active')->count())
                    You have {{ $enrollments->where('status','active')->count() }} program{{ $enrollments->where('status','active')->count() !== 1 ? 's' : '' }} in progress.
                @else
                    Welcome — start your learning journey today.
                @endif
            </p>
        </div>
    </div>

    {{-- ── Pending Payment Banner ────────────────────────────────────────── --}}
    @if($pendingEnrollment)
    <div class="pending-banner">
        <div class="pb-text">
            <div class="pb-title">Payment pending for {{ $pendingEnrollment->program->name }}</div>
            <div class="pb-sub">Complete your payment to activate your enrollment and start learning.</div>
        </div>
        <form action="{{ route('payment.pay-installment') }}" method="POST" style="margin:0">
            @csrf
            <input type="hidden" name="enrollment_id" value="{{ $pendingEnrollment->id }}">
            <button type="submit" class="btn-pending">Complete Payment</button>
        </form>
    </div>
    @endif

    <div class="ml-body">

        {{-- ══════════════════════════════════════════════════════════════
             SIDEBAR
        ═══════════════════════════════════════════════════════════════ --}}
        <aside class="ml-sidebar">

            {{-- ── Calendar ─────────────────────────────────────────────── --}}
            <div class="sidebar-card">
                <div class="cal-nav">
                    <button class="cal-nav-btn" onclick="calPrev()" title="Previous month">&#8249;</button>
                    <span class="cal-nav-title" id="cal-title"></span>
                    <button class="cal-nav-btn" onclick="calNext()" title="Next month">&#8250;</button>
                </div>
                <div class="cal-grid" id="cal-grid">
                    <div class="cal-day-name">Mo</div>
                    <div class="cal-day-name">Tu</div>
                    <div class="cal-day-name">We</div>
                    <div class="cal-day-name">Th</div>
                    <div class="cal-day-name">Fr</div>
                    <div class="cal-day-name">Sa</div>
                    <div class="cal-day-name">Su</div>
                </div>
            </div>

            {{-- ── Upcoming Sessions ────────────────────────────────────── --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h3>Upcoming Sessions</h3>
                    <p>Live sessions across your programs</p>
                </div>

                @if($upcomingSessions->isEmpty())
                <div style="padding:28px 20px; text-align:center; color:var(--text-muted); font-size:13px;">
                    No upcoming sessions scheduled.
                </div>
                @else
                @php
                    $grouped = $upcomingSessions->take(10)->groupBy(fn($s) => $s->start_time->format('Y-m-d'));
                @endphp
                <div class="session-list">
                    @foreach($grouped as $date => $sessions)
                    @php
                        $dt    = \Carbon\Carbon::parse($date);
                        $label = $dt->isToday() ? 'Today' : ($dt->isTomorrow() ? 'Tomorrow' : $dt->format('D, M j'));
                    @endphp
                    <div class="session-date-label">{{ $label }}</div>
                    @foreach($sessions as $session)
                    @php
                        $dotClass = match($session->session_type ?? 'live_class') {
                            'workshop'   => 'green',
                            'assessment' => 'orange',
                            'q&a'        => 'purple',
                            default      => '',
                        };
                    @endphp
                    <div class="session-item">
                        <div class="session-dot-wrap">
                            <div class="session-dot {{ $dotClass }}"></div>
                        </div>
                        <div class="session-info">
                            <div class="session-title">{{ $session->title }}</div>
                            <div class="session-meta">
                                {{ $session->start_time->format('g:i A') }}
                                @if($session->duration_minutes)
                                 · {{ $session->duration_minutes }} min
                                @endif
                            </div>
                            @if($session->meet_link)
                            <a href="{{ $session->meet_link }}" target="_blank" rel="noopener" class="session-join">Join session</a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                </div>
                @endif
            </div>

        </aside>

        {{-- ══════════════════════════════════════════════════════════════
             MAIN
        ═══════════════════════════════════════════════════════════════ --}}
        <main class="ml-main">

            @if($enrollments->isEmpty())
            <div class="empty-state">
                <h3>Start your learning journey</h3>
                <p>Explore our programs and enroll in the one that fits your goals.</p>
                <a href="{{ route('explore') }}" class="btn-explore">Browse Programs</a>
            </div>

            @else

            {{-- Tabs --}}
            <div class="tab-pills">
                <button data-tab="active"
                    class="tab-pill active"
                    onclick="switchTab('active')">
                    In Progress
                    <span style="margin-left:6px;font-size:11px;opacity:0.7;">
                        {{ $enrollments->where('status','active')->count() }}
                    </span>
                </button>
                <button data-tab="completed"
                    class="tab-pill inactive"
                    onclick="switchTab('completed')">
                    Completed
                    <span style="margin-left:6px;font-size:11px;opacity:0.7;">
                        {{ $enrollments->where('status','completed')->count() }}
                    </span>
                </button>
            </div>

            {{-- ── In Progress ─────────────────────────────────────────── --}}
            <div id="tab-active" class="course-list">
                @php $active = $enrollments->where('status', 'active'); @endphp

                @if($active->isEmpty())
                <div class="empty-state">
                    <h3>No active courses</h3>
                    <p>Enroll in a program to get started.</p>
                    <a href="{{ route('explore') }}" class="btn-explore">Browse Programs</a>
                </div>
                @else
                @foreach($active as $enrollment)
                @php
                    $p        = $enrollment->progress_data;
                    $pct      = $p['percentage'];
                    $cohort   = $enrollment->cohort->name ?? 'Cohort';
                    $program  = $enrollment->program->name;
                    $hasImage = !empty($enrollment->program->image);
                @endphp
                <div class="course-card-h">

                    {{-- Icon --}}
                    <div class="cc-icon">
                        @if($hasImage)
                            <img src="{{ $enrollment->program->image_url }}" alt="">
                        @else
                            📚
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="cc-info">
                        <div class="cc-program">{{ $cohort }}</div>
                        <div class="cc-title">{{ $program }}</div>
                        <div class="cc-meta">
                            <span>{{ $p['completed_weeks'] }} / {{ $p['total_weeks'] }} weeks</span>
                            <span class="cc-meta-sep">·</span>
                            <span>{{ $pct }}% complete</span>
                            @if($p['last_accessed'])
                            <span class="cc-meta-sep">·</span>
                            <span>Last accessed {{ \Carbon\Carbon::parse($p['last_accessed'])->diffForHumans() }}</span>
                            @endif
                        </div>
                        <div class="cc-progress-bar">
                            <div class="cc-progress-fill" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>

                    {{-- Action --}}
                    <div class="cc-action">
                        <a href="{{ route('learner.learning.index', $enrollment->id) }}"
                           class="btn-resume">
                            {{ $p['has_started'] ? 'Resume' : 'Start' }}
                        </a>
                    </div>

                </div>
                @endforeach
                @endif
            </div>

            {{-- ── Completed ───────────────────────────────────────────── --}}
            <div id="tab-completed" class="course-list hidden">
                @php $completed = $enrollments->where('status', 'completed'); @endphp

                @if($completed->isEmpty())
                <div class="empty-state">
                    <h3>No completed courses yet</h3>
                    <p>Keep going — you're making great progress!</p>
                </div>
                @else
                @foreach($completed as $enrollment)
                @php
                    $cohort  = $enrollment->cohort->name ?? 'Cohort';
                    $program = $enrollment->program->name;
                    $hasImage = !empty($enrollment->program->image);
                @endphp
                <div class="course-card-h">

                    {{-- Icon --}}
                    <div class="cc-icon" style="background:var(--green-light)">
                        @if($hasImage)
                            <img src="{{ $enrollment->program->image_url }}" alt="">
                        @else
                            ✅
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="cc-info">
                        <div class="cc-program">{{ $cohort }}</div>
                        <div class="cc-title">{{ $program }}</div>
                        <div class="cc-meta">
                            <span class="cc-complete-badge">Completed</span>
                            @if($enrollment->completed_at ?? null)
                            <span class="cc-meta-sep">·</span>
                            <span>{{ \Carbon\Carbon::parse($enrollment->completed_at)->format('M Y') }}</span>
                            @endif
                        </div>
                        <div class="cc-progress-bar">
                            <div class="cc-progress-fill green" style="width:100%"></div>
                        </div>
                    </div>

                    {{-- Action --}}
                    <div class="cc-action">
                        <a href="{{ route('learner.learning.index', $enrollment->id) }}"
                           class="btn-review">Review</a>
                    </div>

                </div>
                @endforeach
                @endif
            </div>

            @endif {{-- end enrollments check --}}
        </main>

    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Tab switching ────────────────────────────────────────────────────────
function switchTab(tab) {
    document.getElementById('tab-active').classList.toggle('hidden', tab !== 'active');
    document.getElementById('tab-completed').classList.toggle('hidden', tab !== 'completed');
    document.querySelectorAll('[data-tab]').forEach(function(btn) {
        var isActive = btn.dataset.tab === tab;
        btn.classList.toggle('active', isActive);
        btn.classList.toggle('inactive', !isActive);
    });
}

// ── Mini calendar ────────────────────────────────────────────────────────
var SESSION_DAYS = @json($sessionDays);  // e.g. ["2026-03-14","2026-03-21"]
var calDate = new Date();
calDate.setDate(1);

function renderCalendar() {
    var year  = calDate.getFullYear();
    var month = calDate.getMonth(); // 0-indexed

    var months = ['January','February','March','April','May','June',
                  'July','August','September','October','November','December'];
    document.getElementById('cal-title').textContent = months[month] + ' ' + year;

    var grid = document.getElementById('cal-grid');
    // Remove all day cells (keep the 7 day-name headers)
    var headers = grid.querySelectorAll('.cal-day-name');
    grid.innerHTML = '';
    headers.forEach(function(h) { grid.appendChild(h.cloneNode(true)); });

    var today    = new Date();
    var firstDay = new Date(year, month, 1);
    // Monday-based: getDay() returns 0=Sun, adjust to Mon=0
    var startDow = (firstDay.getDay() + 6) % 7; // 0=Mon
    var daysInMonth = new Date(year, month + 1, 0).getDate();
    var daysInPrev  = new Date(year, month, 0).getDate();

    // Prev month fillers
    for (var i = startDow - 1; i >= 0; i--) {
        var d = document.createElement('div');
        d.className = 'cal-day other-month';
        d.textContent = daysInPrev - i;
        grid.appendChild(d);
    }

    // Current month days
    for (var day = 1; day <= daysInMonth; day++) {
        var d = document.createElement('div');
        var isToday = (day === today.getDate() && month === today.getMonth() && year === today.getFullYear());
        var dateStr = year + '-' + String(month + 1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
        var hasSession = SESSION_DAYS.indexOf(dateStr) !== -1;

        d.className = 'cal-day' + (isToday ? ' today' : '') + (hasSession ? ' has-session' : '');
        d.textContent = day;
        grid.appendChild(d);
    }

    // Next month fillers to complete last row
    var totalCells = startDow + daysInMonth;
    var remainder  = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
    for (var n = 1; n <= remainder; n++) {
        var d = document.createElement('div');
        d.className = 'cal-day other-month';
        d.textContent = n;
        grid.appendChild(d);
    }
}

function calPrev() { calDate.setMonth(calDate.getMonth() - 1); renderCalendar(); }
function calNext() { calDate.setMonth(calDate.getMonth() + 1); renderCalendar(); }

renderCalendar();
</script>
@endpush