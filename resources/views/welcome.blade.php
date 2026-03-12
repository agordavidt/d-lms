<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>G-Luper | Live Learning Platform</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet">

    <style>
        body { font-family: 'DM Sans', sans-serif; }

        /* ── Slider ── */
        .slider-track {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
            gap: 20px;
        }
        .slider-track::-webkit-scrollbar { display: none; }
        .slide-card { scroll-snap-align: start; flex-shrink: 0; }

        /* ── Modal ── */
        .modal-overlay { backdrop-filter: blur(4px); }
        .modal-enter { animation: modalIn 0.2s ease-out; }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.97) translateY(8px); }
            to   { opacity: 1; transform: scale(1)   translateY(0); }
        }

        /* ── Course card ── */
        .course-card { transition: transform 0.2s, box-shadow 0.2s; }
        .course-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(0,0,0,0.09); }

        /* ── Button loading ── */
        .btn-loading { opacity: 0.65; pointer-events: none; }

        /* ── Field error border ── */
        .input-error { border-color: #f87171 !important; }

        /* ── Search focus ── */
        .search-wrap:focus-within { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }

        /* ── Dot animation ── */
        @keyframes dot-expand {
            from { width: 8px; }
            to   { width: 20px; }
        }
        .dot-active { animation: dot-expand 0.2s ease-out forwards; background: #2563eb; }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased">

@php $programs = $programs ?? collect([]); @endphp

{{-- ══════════════════════════════════════
     TOP NAVIGATION
══════════════════════════════════════ --}}
<nav id="top-nav" class="sticky top-0 z-50 bg-white border-b border-slate-200 transition-shadow duration-200">
    <div class="max-w-7xl mx-auto px-5 h-16 flex items-center gap-5">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[8px] flex items-center justify-center shadow-sm">
                <span class="text-white font-bold text-sm leading-none">G</span>
            </div>
            <span class="text-[17px] font-bold tracking-tight text-slate-900">Luper</span>
        </a>

        {{-- Explore --}}
        <a href="#programs" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition hidden md:block whitespace-nowrap">
            Explore
        </a>

        {{-- Search --}}
        <div class="search-wrap flex-1 max-w-lg flex items-center gap-2.5 bg-slate-50 border border-slate-200 rounded-full px-4 py-2.5 transition-all cursor-text hidden sm:flex" onclick="document.getElementById('nav-search').focus()">
            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="nav-search" type="text" placeholder="What do you want to learn?"
                class="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-full">
        </div>

        {{-- Auth --}}
        <div class="flex items-center gap-4 ml-auto flex-shrink-0">
            <button onclick="openModal('login-modal')"
                class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition hidden sm:block">
                Log In
            </button>
            <button onclick="openModal('register-modal')"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-colors shadow-sm">
                Join for Free
            </button>
        </div>
    </div>
</nav>

{{-- ══════════════════════════════════════
     HERO SLIDER
══════════════════════════════════════ --}}
<section class="bg-[#f0f6ff] py-8 px-5">
    <div class="max-w-7xl mx-auto">
        <div class="relative">
            <div class="slider-track pb-2" id="slider-track">

                {{-- Slide 1 — Brand hero --}}
                <div class="slide-card w-[90vw] sm:w-[580px] lg:w-[620px] bg-gradient-to-br from-blue-700 to-indigo-800 rounded-3xl p-9 text-white relative overflow-hidden">
                    <div class="absolute -top-16 -right-16 w-56 h-56 bg-white/5 rounded-full"></div>
                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/5 rounded-full"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 mb-5">
                            <span class="flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
                            </span>
                            <span class="text-xs font-bold uppercase tracking-widest text-blue-200">Enrollment Open · 2026</span>
                        </div>
                        <h2 class="text-4xl md:text-5xl font-bold leading-tight mb-4" style="font-family:'DM Serif Display',serif;">
                            Live Classes.<br>Real Growth.
                        </h2>
                        <p class="text-blue-100/75 text-sm leading-relaxed mb-7 max-w-sm">
                            Join a cohort, attend live hands-on sessions, and master tech skills with a global community.
                        </p>
                        <button onclick="openModal('register-modal')"
                            class="inline-flex items-center gap-2 bg-white text-blue-700 font-bold text-sm px-6 py-3 rounded-full hover:bg-blue-50 transition-colors shadow">
                            Get Started Free
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Slide 2 — Flexible payment --}}
                <div class="slide-card w-72 flex-shrink-0 bg-white border border-slate-200 rounded-3xl p-8">
                    <span class="inline-block bg-orange-100 text-orange-600 text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-5">Flexible Plans</span>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3 leading-snug">Pay 50% now,<br>50% later.</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">Learn without financial pressure. Split your tuition with our easy installment plan.</p>
                    <a href="#programs" class="inline-flex items-center gap-1.5 text-blue-600 font-bold text-sm hover:underline">
                        View programs
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>

                {{-- Slide 3 — Live sessions --}}
                <div class="slide-card w-72 flex-shrink-0 bg-slate-900 rounded-3xl p-8 text-white">
                    <span class="inline-block bg-white/10 border border-white/20 text-white text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-5">Live Sessions</span>
                    <h3 class="text-2xl font-bold mb-3 leading-snug">2 Live Classes<br>Every Week</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">Hands-on real-time instruction via Google Meet. Build projects, get instant feedback.</p>
                    <a href="#programs" class="inline-flex items-center gap-1.5 text-indigo-400 font-bold text-sm hover:underline">
                        Explore tracks
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>

                {{-- Slide 4 — Community --}}
                <div class="slide-card w-72 flex-shrink-0 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-8 text-white">
                    <span class="inline-block bg-white/20 text-white text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-5">Community</span>
                    <h3 class="text-2xl font-bold mb-3 leading-snug">4,000+<br>Learners & Growing</h3>
                    <p class="text-indigo-100/75 text-sm leading-relaxed mb-6">Dedicated WhatsApp cohort groups. 24/7 mentorship. Network with alumni.</p>
                    <button onclick="openModal('register-modal')" class="inline-flex items-center gap-1.5 text-white font-bold text-sm hover:underline">
                        Join community
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>

                {{-- Dynamic program slides --}}
                @foreach($programs->take(3) as $program)
                <div class="slide-card w-72 flex-shrink-0 bg-white border border-slate-200 rounded-3xl p-8">
                    <span class="inline-block bg-blue-50 text-blue-600 text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-5">
                        {{ $program->duration ?? '12 Weeks' }}
                    </span>
                    <h3 class="text-xl font-bold text-slate-900 mb-2 leading-snug">{{ $program->name }}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6 line-clamp-2">{{ $program->description }}</p>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">from</p>
                            <p class="text-lg font-bold text-slate-900">₦{{ number_format($program->price) }}</p>
                        </div>
                        <button onclick="openModal('register-modal')" class="text-blue-600 font-bold text-sm hover:underline">
                            Enroll →
                        </button>
                    </div>
                </div>
                @endforeach

            </div>

            {{-- Arrows --}}
            <button id="slide-prev" onclick="moveSlider(-1)"
                class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 -translate-x-5 w-10 h-10 bg-white border border-slate-200 rounded-full shadow-md items-center justify-center hover:bg-slate-50 transition z-10">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button id="slide-next" onclick="moveSlider(1)"
                class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 translate-x-5 w-10 h-10 bg-white border border-slate-200 rounded-full shadow-md items-center justify-center hover:bg-slate-50 transition z-10">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>

        {{-- Dots --}}
        <div class="flex justify-center items-center gap-2 mt-5" id="slider-dots"></div>
    </div>
</section>

{{-- ══════════════════════════════════════
     PROGRAMS / COURSES SECTION
══════════════════════════════════════ --}}
<section id="programs" class="max-w-7xl mx-auto px-5 py-16">
    <div class="flex items-end justify-between mb-10">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Active Programs</h2>
            <p class="text-slate-500 mt-1 text-sm">Pick a track and join the next cohort.</p>
        </div>
    </div>

    @if($programs->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
        $accents = [
            ['bar' => 'from-blue-500 to-indigo-500',   'badge' => 'bg-blue-50 text-blue-700'],
            ['bar' => 'from-purple-500 to-pink-500',    'badge' => 'bg-purple-50 text-purple-700'],
            ['bar' => 'from-indigo-500 to-cyan-500',    'badge' => 'bg-indigo-50 text-indigo-700'],
            ['bar' => 'from-emerald-500 to-teal-500',   'badge' => 'bg-emerald-50 text-emerald-700'],
            ['bar' => 'from-orange-500 to-amber-500',   'badge' => 'bg-orange-50 text-orange-700'],
            ['bar' => 'from-pink-500 to-rose-500',      'badge' => 'bg-pink-50 text-pink-700'],
        ];
        @endphp

        @foreach($programs as $i => $program)
        @php $a = $accents[$i % count($accents)]; @endphp
        <div class="course-card bg-white border border-slate-200 rounded-2xl overflow-hidden group cursor-pointer">
            <div class="h-1 bg-gradient-to-r {{ $a['bar'] }}"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $a['badge'] }} px-2.5 py-1 rounded-full">
                        Enrollment Open
                    </span>
                    <span class="text-xs text-slate-400 font-medium">{{ $program->duration ?? '12 Weeks' }}</span>
                </div>
                <h3 class="text-[17px] font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors leading-snug">
                    {{ $program->name }}
                </h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-5 line-clamp-2">{{ $program->description }}</p>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div>
                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Starting from</p>
                        <p class="text-xl font-bold text-slate-900 mt-0.5">₦{{ number_format($program->price) }}</p>
                    </div>
                    <button onclick="openModal('register-modal')"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-full transition-colors shadow-sm">
                        Enroll Now
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 font-medium text-center mt-3">50/50 installment plan available</p>
            </div>
        </div>
        @endforeach
    </div>

    @else
    {{-- Empty state --}}
    <div class="text-center py-20 bg-slate-50 rounded-3xl border border-slate-200">
        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <p class="text-lg font-bold text-slate-700">Programs coming soon.</p>
        <p class="text-slate-500 text-sm mt-1 mb-6">Register now to be notified when enrollment opens.</p>
        <button onclick="openModal('register-modal')"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-7 py-3 rounded-full transition-colors">
            Get Notified
        </button>
    </div>
    @endif
</section>

{{-- ══════════════════════════════════════
     TESTIMONIALS
══════════════════════════════════════ --}}
<section class="bg-[#f7f9fc] py-16">
    <div class="max-w-7xl mx-auto px-5">
        <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-10 tracking-tight">Why learners choose G-Luper</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-white rounded-2xl p-6 border border-slate-200">
                <div class="flex gap-1 mb-4">
                    @for($s=0;$s<5;$s++)<svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                </div>
                <p class="text-slate-600 text-sm leading-relaxed mb-5">"G-Luper's live sessions changed everything. I wasn't watching passive videos — I was building real projects with a mentor alongside a cohort that actually held me accountable."</p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold flex-shrink-0">AF</div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Aisha Farida</p>
                        <p class="text-xs text-slate-400">Fullstack Track · Batch 1</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200">
                <div class="flex gap-1 mb-4">
                    @for($s=0;$s<5;$s++)<svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                </div>
                <p class="text-slate-600 text-sm leading-relaxed mb-5">"The installment plan removed my biggest barrier. Now I work as a UI/UX designer just 3 months after completing the course — the ROI has been incredible."</p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xs font-bold flex-shrink-0">KE</div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Kelvin Emeka</p>
                        <p class="text-xs text-slate-400">UI/UX Design · Batch 2</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200">
                <div class="flex gap-1 mb-4">
                    @for($s=0;$s<5;$s++)<svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                </div>
                <p class="text-slate-600 text-sm leading-relaxed mb-5">"The WhatsApp community is worth the fee alone. Career advice, job referrals, and relationships I'll carry for life — all from a 12-week program."</p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-xs font-bold flex-shrink-0">NB</div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Ngozi Blessing</p>
                        <p class="text-xs text-slate-400">Data Analytics · Batch 1</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════
     CTA DARK SECTION
══════════════════════════════════════ --}}
<section class="max-w-7xl mx-auto px-5 py-16">
    <div class="bg-slate-900 rounded-3xl p-10 md:p-16 text-center relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 leading-tight tracking-tight" style="font-family:'DM Serif Display',serif;">
                Ready to build your future?
            </h2>
            <p class="text-slate-400 text-sm leading-relaxed mb-8 max-w-md mx-auto">
                Register for free, pick your track, and pay only when you're ready to join the cohort.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <button onclick="openModal('register-modal')"
                    class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-9 py-3.5 rounded-full transition-colors shadow-lg shadow-blue-900/30">
                    Create Free Account
                </button>
                <button onclick="openModal('login-modal')"
                    class="bg-white/5 hover:bg-white/10 text-white border border-white/10 font-bold px-9 py-3.5 rounded-full transition-colors">
                    Sign In
                </button>
            </div>
        </div>
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    </div>
</section>

{{-- ══════════════════════════════════════
     FOOTER (Simple)
══════════════════════════════════════ --}}
<footer class="border-t border-slate-200 py-8">
    <div class="max-w-7xl mx-auto px-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-slate-500 text-sm">
            <div class="w-6 h-6 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[6px] flex items-center justify-center">
                <span class="text-white font-bold text-[10px]">G</span>
            </div>
            <span class="font-semibold text-slate-700">Luper</span>
            <span>&copy; {{ date('Y') }}</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">Privacy</a>
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">Terms</a>
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">Support</a>
        </div>
    </div>
</footer>


{{-- ══════════════════════════════════════
     LOGIN MODAL
══════════════════════════════════════ --}}
<div id="login-modal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/50" onclick="closeModal('login-modal')"></div>
    <div class="relative z-10 w-full max-w-md modal-enter">
        <div class="bg-white rounded-3xl shadow-2xl p-8">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-7">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xs">G</span>
                    </div>
                    <span class="text-sm font-bold text-slate-700">G-Luper</span>
                </div>
                <button onclick="closeModal('login-modal')"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 mb-1">Welcome back</h2>
            <p class="text-sm text-slate-500 mb-6">Enter your credentials to access your portal.</p>

            {{-- General error banner --}}
            <div id="login-general-error" class="hidden mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 font-medium"></div>

            <form id="login-form" class="space-y-4" novalidate>
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" name="email" id="login-email" placeholder="name@example.com" autocomplete="email"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition placeholder-slate-400">
                    <p id="login-email-error" class="hidden mt-1.5 text-xs text-red-500 font-medium"></p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-blue-600 font-semibold hover:underline">Forgot?</a>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="login-password" placeholder="••••••••" autocomplete="current-password"
                            class="w-full px-4 py-3 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition placeholder-slate-400">
                        <button type="button" onclick="togglePwd('login-password')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p id="login-password-error" class="hidden mt-1.5 text-xs text-red-500 font-medium"></p>
                </div>

                <div class="flex items-center gap-2.5">
                    <input type="checkbox" name="remember" id="login-remember"
                        class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="login-remember" class="text-sm text-slate-600">Remember me</label>
                </div>

                <button type="submit" id="login-btn"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition-colors text-sm flex items-center justify-center gap-2 mt-2">
                    <span id="login-btn-text">Sign In to Account</span>
                    <svg id="login-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                New to G-Luper?
                <button onclick="switchModal('login-modal','register-modal')"
                    class="text-blue-600 font-bold hover:underline ml-1">Create an account</button>
            </p>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════
     REGISTER MODAL
══════════════════════════════════════ --}}
<div id="register-modal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/50" onclick="closeModal('register-modal')"></div>
    <div class="relative z-10 w-full max-w-md modal-enter">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-h-[96vh] overflow-y-auto">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-7">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xs">G</span>
                    </div>
                    <span class="text-sm font-bold text-slate-700">G-Luper</span>
                </div>
                <button onclick="closeModal('register-modal')"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 mb-1">Create Account</h2>
            <p class="text-sm text-slate-500 mb-6">Join G-Luper — it only takes a minute.</p>

            <div id="register-general-error" class="hidden mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 font-medium"></div>

            <form id="register-form" class="space-y-4" novalidate>
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">First Name</label>
                        <input type="text" name="first_name" id="reg-first-name" placeholder="John" autocomplete="given-name"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition placeholder-slate-400">
                        <p id="reg-first-name-error" class="hidden mt-1.5 text-xs text-red-500 font-medium"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Last Name</label>
                        <input type="text" name="last_name" id="reg-last-name" placeholder="Doe" autocomplete="family-name"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition placeholder-slate-400">
                        <p id="reg-last-name-error" class="hidden mt-1.5 text-xs text-red-500 font-medium"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" name="email" id="reg-email" placeholder="name@example.com" autocomplete="email"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition placeholder-slate-400">
                    <p id="reg-email-error" class="hidden mt-1.5 text-xs text-red-500 font-medium"></p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="reg-password" placeholder="Min. 8 characters" autocomplete="new-password"
                            class="w-full px-4 py-3 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition placeholder-slate-400">
                        <button type="button" onclick="togglePwd('reg-password')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p id="reg-password-error" class="hidden mt-1.5 text-xs text-red-500 font-medium"></p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="reg-confirm-password" placeholder="Repeat password" autocomplete="new-password"
                            class="w-full px-4 py-3 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition placeholder-slate-400">
                        <button type="button" onclick="togglePwd('reg-confirm-password')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3.5">
                    <p class="text-xs text-slate-600 leading-relaxed">
                        <strong class="text-slate-700">Legal name required.</strong>
                        Use your official name as it appears on your ID — it will appear on your certificate.
                    </p>
                </div>

                <button type="submit" id="register-btn"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition-colors text-sm flex items-center justify-center gap-2 mt-1">
                    <span id="register-btn-text">Create My Account</span>
                    <svg id="register-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Already have an account?
                <button onclick="switchModal('register-modal','login-modal')"
                    class="text-blue-600 font-bold hover:underline ml-1">Sign In</button>
            </p>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════
     SCRIPTS
══════════════════════════════════════ --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
// ── TOASTR ──────────────────────────────────────────────
toastr.options = { progressBar: true, positionClass: 'toast-top-right', closeButton: true, timeOut: 5000 };

@if(Session::has('message'))
    (function(){
        var type = "{{ Session::get('alert-type','info') }}";
        var msg  = "{{ Session::get('message') }}";
        if (type === 'success') toastr.success(msg);
        else if (type === 'error') toastr.error(msg);
        else if (type === 'warning') toastr.warning(msg);
        else toastr.info(msg);
    })();
@endif

// ── NAV SCROLL SHADOW ────────────────────────────────────
window.addEventListener('scroll', function() {
    document.getElementById('top-nav').classList.toggle('shadow-md', window.scrollY > 4);
}, { passive: true });

// ── SEARCH ──────────────────────────────────────────────
document.getElementById('nav-search').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && this.value.trim()) {
        document.getElementById('programs').scrollIntoView({ behavior: 'smooth' });
    }
});

// ── MODAL HELPERS ───────────────────────────────────────
function openModal(id) {
    var modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    var modal = document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
    clearErrors(id === 'login-modal' ? 'login' : 'register');
}

function switchModal(from, to) {
    closeModal(from);
    setTimeout(function() { openModal(to); }, 120);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('login-modal');
        closeModal('register-modal');
    }
});

// ── PASSWORD TOGGLE ─────────────────────────────────────
function togglePwd(inputId) {
    var input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// ── ERROR HELPERS ────────────────────────────────────────
function showFieldError(elId, message) {
    var el = document.getElementById(elId);
    if (!el) return;
    el.textContent = message;
    el.classList.remove('hidden');
    // Highlight closest input
    var input = el.parentElement.querySelector('input');
    if (input) input.classList.add('input-error');
}

function showGeneralError(prefix, message) {
    var el = document.getElementById(prefix + '-general-error');
    if (!el) return;
    el.textContent = message;
    el.classList.remove('hidden');
}

function clearErrors(prefix) {
    var selector = '[id^="' + prefix + '-"][id$="-error"]';
    document.querySelectorAll(selector).forEach(function(el) {
        el.textContent = '';
        el.classList.add('hidden');
    });
    var formId = prefix === 'login' ? 'login-form' : 'register-form';
    document.querySelectorAll('#' + formId + ' input').forEach(function(input) {
        input.classList.remove('input-error');
    });
}

function setLoading(prefix, loading) {
    var btn     = document.getElementById(prefix + '-btn');
    var text    = document.getElementById(prefix + '-btn-text');
    var spinner = document.getElementById(prefix + '-spinner');
    if (!btn) return;

    btn.disabled = loading;
    btn.classList.toggle('btn-loading', loading);
    if (spinner) spinner.classList.toggle('hidden', !loading);
    if (text)    text.textContent = loading
        ? (prefix === 'login' ? 'Signing in…' : 'Creating account…')
        : (prefix === 'login' ? 'Sign In to Account' : 'Create My Account');
}

var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── AJAX: LOGIN ──────────────────────────────────────────
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearErrors('login');
    setLoading('login', true);

    try {
        var res = await fetch('/login', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email:    document.getElementById('login-email').value.trim(),
                password: document.getElementById('login-password').value,
                remember: document.getElementById('login-remember').checked
            })
        });

        // Session expired — reload so a fresh CSRF token is issued
        if (res.status === 419) {
            showGeneralError('login', 'Your session has expired. Please refresh the page and try again.');
            return;
        }

        var data = await res.json();

        if (res.ok && data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        if (data.errors) {
            Object.entries(data.errors).forEach(function([field, msgs]) {
                showFieldError('login-' + field + '-error', msgs[0]);
            });
        } else {
            // Never expose server internals — show only the safe message
            showGeneralError('login', 'Incorrect email or password.');
        }

    } catch (err) {
        showGeneralError('login', 'Unable to connect. Please check your connection and try again.');
    } finally {
        setLoading('login', false);
    }
});

// ── AJAX: REGISTER ───────────────────────────────────────
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearErrors('register');
    setLoading('register', true);

    try {
        var res = await fetch('/register', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                first_name:            document.getElementById('reg-first-name').value.trim(),
                last_name:             document.getElementById('reg-last-name').value.trim(),
                email:                 document.getElementById('reg-email').value.trim(),
                password:              document.getElementById('reg-password').value,
                password_confirmation: document.getElementById('reg-confirm-password').value
            })
        });

        if (res.status === 419) {
            showGeneralError('register', 'Your session has expired. Please refresh the page and try again.');
            return;
        }

        var data = await res.json();

        if (res.ok && data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        if (data.errors) {
            Object.entries(data.errors).forEach(function([field, msgs]) {
                var elId = 'reg-' + field.replace(/_/g, '-') + '-error';
                showFieldError(elId, msgs[0]);
            });
        } else {
            showGeneralError('register', 'Registration could not be completed. Please try again.');
        }

    } catch (err) {
        showGeneralError('register', 'Unable to connect. Please check your connection and try again.');
    } finally {
        setLoading('register', false);
    }
});

// ── SLIDER ───────────────────────────────────────────────
(function() {
    var track       = document.getElementById('slider-track');
    var dotsWrap    = document.getElementById('slider-dots');
    var slides      = Array.from(track.querySelectorAll('.slide-card'));
    var current     = 0;
    var total       = slides.length;
    var autoTimer   = null;

    if (total === 0) return;

    // Build dots
    slides.forEach(function(_, i) {
        var dot = document.createElement('span');
        dot.className = 'h-2 rounded-full cursor-pointer transition-all bg-slate-300';
        dot.style.width = '8px';
        dot.addEventListener('click', function() { goTo(i); restartAuto(); });
        dotsWrap.appendChild(dot);
    });

    function updateDots() {
        dotsWrap.querySelectorAll('span').forEach(function(dot, i) {
            if (i === current) {
                dot.classList.remove('bg-slate-300');
                dot.classList.add('bg-blue-600');
                dot.style.width = '20px';
            } else {
                dot.classList.remove('bg-blue-600');
                dot.classList.add('bg-slate-300');
                dot.style.width = '8px';
            }
        });
    }

    function goTo(n) {
        current = ((n % total) + total) % total;
        slides[current].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
        updateDots();
    }

    window.moveSlider = function(dir) {
        goTo(current + dir);
        restartAuto();
    };

    // IntersectionObserver to sync current index with scroll
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var idx = slides.indexOf(entry.target);
                if (idx !== -1) { current = idx; updateDots(); }
            }
        });
    }, { root: track, threshold: 0.55 });

    slides.forEach(function(s) { observer.observe(s); });

    function startAuto() {
        autoTimer = setInterval(function() { goTo(current + 1); }, 5500);
    }

    function restartAuto() {
        clearInterval(autoTimer);
        startAuto();
    }

    updateDots();
    startAuto();
})();
</script>
</body>
</html>