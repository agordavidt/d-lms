@extends('layouts.learner')

@section('title', 'Waiting for Content — ' . $enrollment->program->name)

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center px-4 py-16">
    <div class="max-w-lg w-full text-center">

        {{-- Illustration --}}
        <div class="w-24 h-24 bg-indigo-50 rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-sm">
            <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>

        <h1 class="text-2xl font-black text-slate-900 mb-3">
            Content is on its way
        </h1>

        <p class="text-slate-500 text-base leading-relaxed mb-8 max-w-sm mx-auto">
            You're enrolled in
            <span class="font-semibold text-slate-700">{{ $enrollment->program->name }}</span>.
            Your first week's content hasn't been published yet — check back soon or watch your email for an update from your mentor.
        </p>

        {{-- Cohort / Start date info --}}
        @if($enrollment->cohort?->start_date)
        <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-8 inline-flex items-center gap-4 mx-auto shadow-sm">
            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-left">
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Cohort Start Date</p>
                <p class="text-sm font-bold text-slate-800">
                    {{ $enrollment->cohort->start_date->format('M j, Y') }}
                </p>
            </div>
        </div>
        @endif

        {{-- Upcoming sessions if any --}}
        @php
            $upcomingSessions = \App\Models\LiveSession::where('cohort_id', $enrollment->cohort_id)
                ->where('status', 'scheduled')
                ->where('start_time', '>', now())
                ->orderBy('start_time')
                ->limit(3)
                ->get();
        @endphp

        @if($upcomingSessions->count())
        <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-8 text-left shadow-sm">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">Upcoming Sessions</p>
            <div class="space-y-3">
                @foreach($upcomingSessions as $session)
                <div class="flex items-center justify-between gap-4 py-2 border-t border-slate-50 first:border-t-0 first:pt-0">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $session->title }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $session->start_time->format('M j · g:i A') }}
                        </p>
                    </div>
                    @if($session->meet_link)
                    <a href="{{ $session->meet_link }}" target="_blank" rel="noopener"
                       class="flex-shrink-0 text-xs font-bold text-indigo-600 hover:text-indigo-700 transition-colors bg-indigo-50 px-3 py-1.5 rounded-lg">
                        Join
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('learner.my-learning') }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 px-5 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                ← My Learning
            </a>
            <button onclick="window.location.reload()"
                class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-5 py-3 rounded-xl hover:bg-indigo-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Check Again
            </button>
        </div>

    </div>
</div>
@endsection