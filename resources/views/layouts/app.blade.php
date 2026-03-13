<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mentor') — G-Luper</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:       #0056d2;
            --blue-light: #e8f0fe;
            --text:       #1f1f1f;
            --muted:      #6b7280;
            --border:     #e5e7eb;
            --bg:         #f9fafb;
            --white:      #ffffff;
            --nav-h:      60px;
            --success:    #16a34a;
            --warning:    #b45309;
            --error:      #dc2626;
        }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 15px;
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
        }

        /* ── Top Nav ──────────────────────────────────────────────── */
        .top-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--nav-h);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            gap: 2rem;
            z-index: 100;
        }

        .nav-brand {
            font-family: 'Source Serif 4', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--blue);
            text-decoration: none;
            white-space: nowrap;
            margin-right: 1rem;
        }

        .nav-links {
            display: flex;
            gap: 0;
            flex: 1;
        }

        .nav-link {
            display: inline-block;
            padding: 0 1.25rem;
            height: var(--nav-h);
            line-height: var(--nav-h);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: color 0.15s, border-color 0.15s;
            white-space: nowrap;
        }

        .nav-link:hover       { color: var(--text); }
        .nav-link.active      { color: var(--blue); border-bottom-color: var(--blue); }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }

        .nav-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--blue);
            color: var(--white);
            font-size: 0.8rem;
            font-weight: 600;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            position: relative;
        }

        .avatar-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.5rem 0;
            min-width: 180px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            z-index: 200;
        }

        .nav-avatar:hover .avatar-dropdown { display: block; }

        .avatar-dropdown a,
        .avatar-dropdown button {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: var(--text);
            text-decoration: none;
            text-align: left;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .avatar-dropdown a:hover,
        .avatar-dropdown button:hover { background: var(--bg); }

        .avatar-dropdown hr {
            border: none;
            border-top: 1px solid var(--border);
            margin: 0.25rem 0;
        }

        /* ── Page wrapper ─────────────────────────────────────────── */
        .page-body {
            margin-top: var(--nav-h);
            min-height: calc(100vh - var(--nav-h));
        }

        /* ── Page header (title bar) ──────────────────────────────── */
        .page-header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header h1 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text);
        }

        .page-header .breadcrumb {
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 0.2rem;
        }

        .page-header .breadcrumb a {
            color: var(--blue);
            text-decoration: none;
        }

        /* ── Content container ────────────────────────────────────── */
        .container { max-width: 1100px; margin: 0 auto; padding: 0 2rem; }
        .section    { padding: 2rem 0; }

        /* ── Buttons ──────────────────────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.5rem 1.1rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
            line-height: 1.4;
        }

        .btn-primary  { background: var(--blue); color: var(--white); border-color: var(--blue); }
        .btn-primary:hover  { background: #0047b0; }
        .btn-outline  { background: var(--white); color: var(--blue); border-color: var(--blue); }
        .btn-outline:hover  { background: var(--blue-light); }
        .btn-ghost    { background: transparent; color: var(--muted); border-color: var(--border); }
        .btn-ghost:hover    { background: var(--bg); color: var(--text); }
        .btn-danger   { background: var(--white); color: var(--error); border-color: #fca5a5; }
        .btn-danger:hover   { background: #fef2f2; }
        .btn-sm       { padding: 0.35rem 0.75rem; font-size: 0.8rem; }

        /* ── Alert flash ──────────────────────────────────────────── */
        .flash {
            padding: 0.8rem 1.25rem;
            border-radius: 6px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }
        .flash-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .flash-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .flash-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* ── Cards ────────────────────────────────────────────────── */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .card-body { padding: 1.5rem; }

        /* ── Form controls ────────────────────────────────────────── */
        .form-group { margin-bottom: 1.25rem; }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.35rem;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 0.55rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.875rem;
            font-family: inherit;
            color: var(--text);
            background: var(--white);
            transition: border-color 0.15s;
            line-height: 1.5;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0,86,210,0.08);
        }

        select.form-control { cursor: pointer; }
        textarea.form-control { resize: vertical; min-height: 90px; }

        .form-hint {
            font-size: 0.78rem;
            color: var(--muted);
            margin-top: 0.25rem;
        }

        .invalid-feedback {
            font-size: 0.78rem;
            color: var(--error);
            margin-top: 0.25rem;
        }

        /* ── Table ────────────────────────────────────────────────── */
        .table { width: 100%; border-collapse: collapse; }
        .table th {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
            padding: 0.75rem 1rem;
            border-bottom: 2px solid var(--border);
            text-align: left;
        }
        .table td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.875rem;
            vertical-align: middle;
        }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: var(--bg); }

        /* ── Badge ────────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.73rem;
            font-weight: 500;
        }
        .badge-blue    { background: var(--blue-light); color: var(--blue); }
        .badge-green   { background: #f0fdf4; color: #166534; }
        .badge-yellow  { background: #fffbeb; color: #92400e; }
        .badge-gray    { background: var(--bg); color: var(--muted); border: 1px solid var(--border); }

        /* ── Progress bar ─────────────────────────────────────────── */
        .progress-bar-track {
            height: 4px; background: var(--border); border-radius: 2px; overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%; background: var(--blue); border-radius: 2px; transition: width 0.3s;
        }

        /* ── Stat boxes ───────────────────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
        }

        .stat-box .stat-value {
            font-family: 'Source Serif 4', serif;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text);
            line-height: 1;
            margin-bottom: 0.3rem;
        }

        .stat-box .stat-label {
            font-size: 0.8rem;
            color: var(--muted);
        }

        /* ── Modal ────────────────────────────────────────────────── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 500;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }

        .modal {
            background: var(--white);
            border-radius: 10px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem;
            position: relative;
        }

        .modal h2 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.15rem;
            margin-bottom: 1.25rem;
        }

        .modal-close {
            position: absolute;
            top: 1rem; right: 1rem;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--muted);
            line-height: 1;
        }

        /* ── Misc helpers ─────────────────────────────────────────── */
        .text-muted  { color: var(--muted); }
        .text-small  { font-size: 0.8rem; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-1 { gap: 0.5rem; }
        .gap-2 { gap: 1rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        @media (max-width: 640px) {
            .top-nav { padding: 0 1rem; gap: 1rem; }
            .container { padding: 0 1rem; }
            .grid-2 { grid-template-columns: 1fr; }
            .nav-link { padding: 0 0.75rem; font-size: 0.8rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

<nav class="top-nav">
    <a href="{{ route('mentor.dashboard') }}" class="nav-brand">G-Luper</a>

    <div class="nav-links">
        <a href="{{ route('mentor.programs.index') }}"
           class="nav-link {{ request()->routeIs('mentor.programs.*', 'mentor.curriculum.*', 'mentor.assessments.*') ? 'active' : '' }}">
            Course Management
        </a>
        <a href="{{ route('mentor.students.index') }}"
           class="nav-link {{ request()->routeIs('mentor.students.*') ? 'active' : '' }}">
            My Learners
        </a>
        <a href="{{ route('mentor.sessions.index') }}"
           class="nav-link {{ request()->routeIs('mentor.sessions.*') ? 'active' : '' }}">
            Sessions
        </a>
    </div>

    <div class="nav-right">
        <div class="nav-avatar">
            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
            <div class="avatar-dropdown">
                <div style="padding: 0.5rem 1rem 0.4rem; font-size: 0.8rem; color: var(--muted);">
                    {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                </div>
                <hr>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="page-body">

    @if(session('message'))
        <div style="padding: 0 2rem; padding-top: 1rem;">
            <div class="flash flash-{{ session('alert-type') === 'success' ? 'success' : (session('alert-type') === 'warning' ? 'warning' : 'error') }}">
                {{ session('message') }}
            </div>
        </div>
    @endif

    @yield('content')
</div>

@stack('scripts')
</body>
</html>