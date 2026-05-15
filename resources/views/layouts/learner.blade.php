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

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <link rel="stylesheet" href="{{ asset('assets/css/learner.css') }}">

    <style>
        .nav-item-active { color: #2563eb !important; border-bottom-color: #2563eb !important; }
        #toast-container { margin-top: 70px; }
    </style>

    @stack('styles')
</head>
<body class="bg-[#f7f9fc] text-slate-900 antialiased">

<form id="idle-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

{{-- ── Top Navigation ── --}}
<nav class="sticky top-0 z-50 bg-white border-b border-slate-200" id="learner-nav">
    <div class="max-w-7xl mx-auto px-5 h-[60px] flex items-center">

        <a href="{{ route('learner.my-learning') }}" class="flex items-center gap-2 flex-shrink-0 mr-6 group">
            <div class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[7px] flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                <span class="text-white font-bold text-xs leading-none">G</span>
            </div>
            <span class="text-[16px] font-bold tracking-tight text-slate-900 hidden sm:block">Luper</span>
        </a>

        <div class="flex items-center h-[60px] gap-1">
            <a href="{{ route('explore') }}"
               class="h-full flex items-center px-3 text-sm font-medium text-slate-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all {{ request()->routeIs('explore') ? 'nav-item-active' : '' }}">
                Explore
            </a>
            <a href="{{ route('learner.my-learning') }}"
               class="h-full flex items-center px-3 text-sm font-medium text-slate-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all {{ request()->routeIs('learner.my-learning', 'learner.dashboard', 'learner.learning.*') ? 'nav-item-active' : '' }}">
                My Learning
            </a>
            <a href="{{ route('learner.certifications') }}"
               class="h-full flex items-center px-3 text-sm font-medium text-slate-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all {{ request()->routeIs('learner.certifications') ? 'nav-item-active' : '' }}">
                Certifications
            </a>
        </div>

        <div class="flex-1 max-w-xs mx-5 hidden lg:block">
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-full px-4 py-2 focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" placeholder="Search courses…" class="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-full">
            </div>
        </div>

        <div class="ml-auto relative" id="profile-wrap">
            <button id="profile-btn" class="flex items-center gap-2 hover:bg-slate-50 rounded-xl px-2.5 py-1.5 transition-colors focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden flex-shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{ auth()->user()->initials }}
                    @endif
                </div>
                <span class="text-sm font-semibold text-slate-700 hidden sm:block">{{ auth()->user()->first_name }}</span>
                <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="profile-menu" class="hidden absolute right-0 top-[calc(100%+8px)] w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-sm font-bold text-slate-900">{{ auth()->user()->full_name }}</p>
                    <p class="text-xs text-slate-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('learner.profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile Settings
                </a>
                <div class="border-t border-slate-100 mt-1 pt-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors w-full text-left">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<main>
    @if(session('message'))
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var type = '{{ session("alert-type", "info") }}';
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
toastr.options = { progressBar: true, positionClass: 'toast-top-right', closeButton: true, timeOut: 5000 };

(function() {
    var btn  = document.getElementById('profile-btn');
    var menu = document.getElementById('profile-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function(e) { e.stopPropagation(); menu.classList.toggle('hidden'); });
    document.addEventListener('click', function() { menu.classList.add('hidden'); });
})();

(function() {
    var IDLE_LIMIT  = 15 * 60 * 1000;
    var WARN_BEFORE =  1 * 60 * 1000;
    var idleTimer, warnTimer, warned = false;

    function resetTimer() {
        clearTimeout(idleTimer); clearTimeout(warnTimer); warned = false;
        warnTimer = setTimeout(function() {
            if (!warned) { warned = true; toastr.warning('Session expires in 1 minute.', 'Idle Warning', { timeOut: 60000, extendedTimeOut: 0 }); }
        }, IDLE_LIMIT - WARN_BEFORE);
        idleTimer = setTimeout(function() { document.getElementById('idle-logout-form').submit(); }, IDLE_LIMIT);
    }
    ['mousemove','keydown','click','scroll','touchstart'].forEach(function(e) {
        document.addEventListener(e, resetTimer, { passive: true });
    });
    resetTimer();
})();
</script>

@stack('scripts')
</body>
</html>