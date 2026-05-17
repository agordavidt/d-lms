<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'My Learning') | G-Luper</title>

    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600;700&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:        #2563eb;
            --blue-light:  #eff6ff;
            --indigo:      #4f46e5;
            --purple:      #7c3aed;
            --text:        #0f172a;
            --muted:       #64748b;
            --border:      #e2e8f0;
            --bg:          #f8fafc;
            --white:       #ffffff;
            --success:     #16a34a;
            --warning:     #b45309;
            --error:       #dc2626;
            --nav-h:       60px;
        }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 15px;
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
        }

        /* ── Nav ── */
        .l-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            height: var(--nav-h);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 0;
        }

        .l-nav-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }

        .l-nav-brand-icon {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
        }

        .l-nav-brand-icon span {
            color: #fff; font-weight: 800; font-size: 12px;
        }

        .l-nav-brand-name {
            font-size: 15px; font-weight: 700; color: var(--text);
        }

        .l-nav-links {
            display: flex;
            align-items: center;
            height: var(--nav-h);
            gap: 0;
        }

        .l-nav-link {
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: color 0.15s, border-color 0.15s;
            white-space: nowrap;
        }

        .l-nav-link:hover        { color: var(--blue); }
        .l-nav-link.active       { color: var(--blue); border-bottom-color: var(--blue); }

        .l-nav-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .l-avatar-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 10px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.15s;
        }

        .l-avatar-btn:hover { background: var(--bg); }

        .l-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--blue);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .l-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .l-avatar-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text);
        }

        /* Dropdown */
        .l-profile-wrap { position: relative; }

        .l-profile-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            width: 220px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
            z-index: 200;
            overflow: hidden;
        }

        .l-profile-menu.open { display: block; }

        .l-profile-menu-header {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .l-profile-menu-header p:first-child {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text);
        }

        .l-profile-menu-header p:last-child {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .l-profile-menu a,
        .l-profile-menu button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 16px;
            font-size: 0.875rem;
            color: var(--text);
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-align: left;
            transition: background 0.12s;
        }

        .l-profile-menu a:hover,
        .l-profile-menu button:hover { background: var(--bg); }

        .l-profile-menu .danger { color: var(--error); }
        .l-profile-menu .danger:hover { background: #fef2f2; }

        .l-profile-menu-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 4px 0;
        }

        /* ── Learning layout (sidebar + main) ── */
        .learn-wrap {
            display: flex;
            height: calc(100vh - var(--nav-h));
            overflow: hidden;
        }

        .learn-sidebar {
            width: 280px;
            flex-shrink: 0;
            background: var(--white);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width 0.25s ease;
        }

        .learn-sidebar.collapsed { width: 0; overflow: hidden; }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .learn-main {
            flex: 1;
            overflow-y: auto;
            min-width: 0;
        }

        /* Sidebar week items */
        .week-nav-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 14px;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: background 0.12s;
        }

        .week-nav-item:hover:not(.locked)  { background: var(--bg); }
        .week-nav-item.active              { background: var(--blue-light); border-left-color: var(--blue); }
        .week-nav-item.locked              { opacity: 0.5; cursor: default; }

        /* Sidebar content dot items */
        .content-nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px 6px 36px;
            font-size: 0.78rem;
            color: var(--muted);
        }

        .content-nav-dot {
            width: 14px; height: 14px;
            border-radius: 50%;
            border: 1.5px solid #d1d5db;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .content-nav-dot.done {
            background: var(--success);
            border-color: var(--success);
        }

        /* ── Topbar inside main ── */
        .learn-topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            height: 48px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* ── Content sections ── */
        .content-section {
            padding: 2rem 2.5rem;
            border-bottom: 1px solid var(--border);
            max-width: 820px;
        }

        .content-type-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 0.4rem;
        }

        .content-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1.25rem;
            line-height: 1.35;
        }

        /* Video */
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            border-radius: 10px;
            overflow: hidden;
            background: #000;
            margin-bottom: 0.75rem;
        }

        .video-watch-note {
            font-size: 0.78rem;
            color: var(--muted);
            margin-bottom: 0.75rem;
        }

        /* PDF */
        .pdf-frame {
            width: 100%;
            height: 600px;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        /* Article */
        .article-body {
            font-size: 0.95rem;
            line-height: 1.8;
            color: #1e293b;
            max-width: 680px;
        }

        .article-body p  { margin-bottom: 1rem; }
        .article-body h2 { font-size: 1.1rem; font-weight: 700; margin: 1.5rem 0 0.5rem; }
        .article-body h3 { font-size: 1rem;   font-weight: 700; margin: 1.25rem 0 0.4rem; }
        .article-body ul,
        .article-body ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        .article-body li { margin-bottom: 0.4rem; }

        /* Link card */
        .ext-link-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            background: var(--white);
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .ext-link-card:hover {
            border-color: var(--blue);
            box-shadow: 0 2px 8px rgba(37,99,235,.08);
        }

        /* Scroll sentinel */
        .scroll-sentinel { height: 1px; }

        /* Completion indicator */
        .completion-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 1rem;
            font-size: 0.78rem;
            color: var(--muted);
        }

        .ci-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #d1d5db;
            transition: background 0.3s;
        }

        .completion-indicator.done .ci-dot { background: var(--success); }
        .completion-indicator.done         { color: var(--success); }

        /* Progress pill */
        .prog-pill {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--blue);
            background: var(--blue-light);
            padding: 1px 8px;
            border-radius: 99px;
            flex-shrink: 0;
        }

        /* ── Assessment section (weekly quiz) ── */
        .assessment-section {
            padding: 2rem 2.5rem;
            max-width: 820px;
        }

        .assessment-header {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .question-card {
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
            transition: border-color 0.15s;
        }

        .question-card.unanswered { border-color: #fca5a5; }

        .question-text {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 1rem;
            line-height: 1.55;
        }

        .option-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.9rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: border-color 0.12s, background 0.12s;
            user-select: none;
        }

        .option-row:hover    { border-color: var(--indigo); background: #f5f3ff; }
        .option-row.selected { border-color: var(--indigo); background: #eef2ff; }

        .option-radio {
            width: 16px; height: 16px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            flex-shrink: 0;
            transition: border-color 0.12s, background 0.12s;
        }

        .option-row.selected .option-radio {
            border-color: var(--indigo);
            background: var(--indigo);
        }

        /* Result banners */
        .result-banner {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            border: 1px solid var(--border);
            background: var(--bg);
        }

        .result-banner.pass { background: #f0fdf4; border-color: #bbf7d0; }
        .result-banner.fail { background: #fef2f2; border-color: #fecaca; }

        /* ── Final exam section ── */
        .final-exam-section {
            padding: 2rem 2.5rem;
            max-width: 820px;
        }

        .final-exam-header {
            background: linear-gradient(135deg, #f5f3ff, #ede9fe);
            border: 1px solid #ddd6fe;
            border-radius: 14px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
        }

        .final-exam-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--purple);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 3px 10px;
            border-radius: 99px;
            margin-bottom: 0.75rem;
        }

        .final-exam-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: #2e1065;
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .final-exam-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.82rem;
            color: #5b21b6;
        }

        .btn-begin-exam {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--purple);
            color: #fff;
            padding: 13px 28px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
            box-shadow: 0 4px 14px rgba(124,58,237,.25);
        }

        .btn-begin-exam:hover { background: #6d28d9; }

        .final-result-pass {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
        }

        .exam-cooldown-box {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
        }

        .exam-cooldown-icon {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: #fef3c7;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .exam-cooldown-title     { font-weight: 700; font-size: 0.9rem; color: #92400e; margin-bottom: 0.35rem; }
        .exam-cooldown-countdown { font-size: 1.6rem; font-weight: 800; color: #b45309; font-variant-numeric: tabular-nums; margin-bottom: 0.25rem; }
        .exam-cooldown-sub       { font-size: 0.78rem; color: #b45309; line-height: 1.6; }

        /* ── Final exam take page ── */
        .final-timer-bar    { display:flex;align-items:center;gap:6px;background:#f5f3ff;color:#5b21b6;padding:5px 14px;border-radius:99px;font-weight:700;font-size:13px; }
        .final-timer-bar.warning { background:#fffbeb;color:#b45309; }
        .final-timer-bar.danger  { background:#fef2f2;color:#dc2626; }
        .final-timer-countdown   { font-variant-numeric:tabular-nums;font-size:14px;font-weight:800; }

        .final-option-row {
            display:flex;align-items:center;gap:12px;
            padding:14px 18px;
            border:1.5px solid var(--border);
            border-radius:10px;
            margin-bottom:8px;
            cursor:pointer;
            transition:border-color .12s,background .12s;
            user-select:none;
        }
        .final-option-row:hover    { border-color:var(--purple);background:#faf5ff; }
        .final-option-row.selected { border-color:var(--purple);background:#f5f3ff; }

        .final-option-radio {
            width:18px;height:18px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0;transition:all .12s;
        }
        .final-option-row.selected .final-option-radio { border-color:var(--purple);background:var(--purple); }

        /* ── Week footer ── */
        .week-footer {
            padding: 1.5rem 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--border);
            max-width: 820px;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.15s, border-color 0.15s;
            border: 1.5px solid var(--border);
        }

        .nav-btn-ghost   { color: var(--muted); background: var(--white); }
        .nav-btn-ghost:hover { background: var(--bg); }
        .nav-btn-primary { color: #fff; background: var(--blue); border-color: var(--blue); }
        .nav-btn-primary:hover { background: #1d4ed8; }

        @media (max-width: 768px) {
            .learn-sidebar { display: none; }
            .learn-sidebar.mobile-open { display: flex; position: fixed; inset: var(--nav-h) 0 0 0; z-index: 50; width: 280px; }
            .content-section  { padding: 1.5rem 1.25rem; }
            .assessment-section { padding: 1.5rem 1.25rem; }
            .final-exam-section { padding: 1.5rem 1.25rem; }
            .week-footer { padding: 1.25rem; }
            .learn-topbar { padding: 0 1rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

<form id="idle-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

{{-- ── Top Navigation ── --}}
<nav class="l-nav">
    <a href="{{ route('learner.my-learning') }}" class="l-nav-brand">
        <div class="l-nav-brand-icon"><span>G</span></div>
        <span class="l-nav-brand-name">Luper</span>
    </a>

    <div class="l-nav-links">
        <a href="{{ route('explore') }}"
           class="l-nav-link {{ request()->routeIs('explore') ? 'active' : '' }}">Explore</a>
        <a href="{{ route('learner.my-learning') }}"
           class="l-nav-link {{ request()->routeIs('learner.my-learning','learner.dashboard','learner.learning.*') ? 'active' : '' }}">My Learning</a>
        <a href="{{ route('learner.certifications') }}"
           class="l-nav-link {{ request()->routeIs('learner.certifications') ? 'active' : '' }}">Certifications</a>
    </div>

    <div class="l-nav-right">
        <div class="l-profile-wrap">
            <button class="l-avatar-btn" id="profile-btn">
                <div class="l-avatar">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="">
                    @else
                        {{ strtoupper(substr(auth()->user()->first_name,0,1)) }}
                    @endif
                </div>
                <span class="l-avatar-name">{{ auth()->user()->first_name }}</span>
                <svg width="14" height="14" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="l-profile-menu" id="profile-menu">
                <div class="l-profile-menu-header">
                    <p>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                    <p>{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('learner.profile.edit') }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile Settings
                </a>
                <hr class="l-profile-menu-divider">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="danger">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main>
    @if(session('message'))
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var type = '{{ session("alert-type","info") }}';
        var msg  = @json(session('message'));
        if      (type === 'success') toastr.success(msg);
        else if (type === 'error')   toastr.error(msg);
        else if (type === 'warning') toastr.warning(msg);
        else                         toastr.info(msg);
    });
    </script>
    @endif

    @yield('content')
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
toastr.options = { progressBar:true, positionClass:'toast-top-right', closeButton:true, timeOut:5000 };

// Profile dropdown
(function () {
    var btn  = document.getElementById('profile-btn');
    var menu = document.getElementById('profile-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function (e) { e.stopPropagation(); menu.classList.toggle('open'); });
    document.addEventListener('click', function () { menu.classList.remove('open'); });
})();

// Idle logout — 15 min
(function () {
    var LIMIT = 15 * 60 * 1000, WARN = 60 * 1000;
    var idle, warn;
    function reset() {
        clearTimeout(idle); clearTimeout(warn);
        warn = setTimeout(function () { toastr.warning('Session expires in 1 minute.','Idle Warning',{timeOut:60000,extendedTimeOut:0}); }, LIMIT - WARN);
        idle = setTimeout(function () { document.getElementById('idle-logout-form').submit(); }, LIMIT);
    }
    ['mousemove','keydown','click','scroll','touchstart'].forEach(function (e) {
        document.addEventListener(e, reset, { passive:true });
    });
    reset();
})();
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
@stack('scripts')
</body>
</html>