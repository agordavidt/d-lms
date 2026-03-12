@extends('layouts.learner')

@section('title', 'My Learning')

@push('styles')
<style>
    .tab-active {
        color: #1d4ed8;
        border-bottom: 2px solid #1d4ed8;
        font-weight: 700;
    }
    .tab-inactive {
        color: #64748b;
        border-bottom: 2px solid transparent;
        font-weight: 600;
    }
    .schedule-item + .schedule-item {
        border-top: 1px solid #f1f5f9;
    }
    .session-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 5px;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-5 py-8">

    {{-- ── Pending Payment Banner ──────────────────────────────────────── --}}
    @if($pendingEnrollment)
    <div class="mb-6 flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="flex-1">
            <p class="text-sm font-bold text-amber-900">Payment pending for <span class="font-black">{{ $pendingEnrollment->program->name }}</span></p>
            <p class="text-xs text-amber-700 mt-0.5">Complete your payment to activate your enrollment and start learning.</p>
        </div>
        <form action="{{ route('payment.pay-installment') }}" method="POST">
            @csrf
            <input type="hidden" name="enrollment_id" value="{{ $pendingEnrollment->id }}">
            <button type="submit"
                class="flex-shrink-0 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2 rounded-xl transition-colors">
                Complete Payment
            </button>
        </form>
    </div>
    @endif

    {{-- ── Page header ────────────────────────────────────────────────── --}}
    <div class="mb-7">
        <h1 class="text-2xl font-bold text-slate-900">
            My Learning
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            @if($enrollments->isNotEmpty())
                {{ $enrollments->where('status','active')->count() }} program{{ $enrollments->where('status','active')->count() !== 1 ? 's' : '' }} in progress
            @else
                You haven't enrolled in any programs yet.
            @endif
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ══════════════════════════════════════════════════════
             LEFT — Course grid
        ════════════════════════════════════════════════════════ --}}
        <div class="flex-1 min-w-0">

            @if($enrollments->isEmpty())
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Start your learning journey</h3>
                <p class="text-slate-500 text-sm mb-6 max-w-xs">Explore our programs and enroll in the one that fits your goals.</p>
                <a href="{{ route('explore') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-6 py-3 rounded-xl transition-colors">
                    Browse Programs
                </a>
            </div>

            @else

            {{-- Tabs: In Progress / Completed --}}
            <div class="flex gap-0 border-b border-slate-200 mb-6" id="course-tabs">
                <button data-tab="active"
                    class="px-4 py-3 text-sm tab-active mr-1"
                    onclick="switchTab('active')">
                    In Progress
                    <span class="ml-1.5 bg-blue-100 text-blue-700 text-[11px] font-bold px-2 py-0.5 rounded-full">
                        {{ $enrollments->where('status','active')->count() }}
                    </span>
                </button>
                <button data-tab="completed"
                    class="px-4 py-3 text-sm tab-inactive"
                    onclick="switchTab('completed')">
                    Completed
                    <span class="ml-1.5 bg-slate-100 text-slate-500 text-[11px] font-bold px-2 py-0.5 rounded-full">
                        {{ $enrollments->where('status','completed')->count() }}
                    </span>
                </button>
            </div>

            {{-- In Progress courses --}}
            <div id="tab-active">
                @php $active = $enrollments->where('status', 'active'); @endphp

                @if($active->isEmpty())
                <div class="py-10 text-center text-slate-400 text-sm">No active courses.</div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($active as $enrollment)
                    @php $p = $enrollment->progress_data; @endphp
                    <div class="course-card bg-white border border-slate-100 rounded-2xl overflow-hidden group">

                        {{-- Program color band --}}
                        <div class="h-2 w-full bg-gradient-to-r from-blue-500 to-indigo-600"></div>

                        <div class="p-5">
                            {{-- Program icon + name --}}
                            <div class="flex items-start gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 text-lg">
                                    @if($enrollment->program->image)
                                        <img src="{{ $enrollment->program->image_url }}" alt="" class="w-full h-full object-cover rounded-xl">
                                    @else
                                        📚
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-slate-900 leading-snug line-clamp-2">
                                        {{ $enrollment->program->name }}
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $enrollment->cohort->name ?? 'Cohort' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Progress bar --}}
                            <div class="mb-3">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-xs text-slate-500">
                                        {{ $p['completed_weeks'] }} / {{ $p['total_weeks'] }} weeks
                                    </span>
                                    <span class="text-xs font-bold text-blue-600">{{ $p['percentage'] }}%</span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="prog-fill h-full bg-blue-600 rounded-full"
                                         style="width: {{ $p['percentage'] }}%"></div>
                                </div>
                            </div>

                            {{-- Last accessed --}}
                            @if($p['last_accessed'])
                            <p class="text-[11px] text-slate-400 mb-4">
                                Last accessed {{ \Carbon\Carbon::parse($p['last_accessed'])->diffForHumans() }}
                            </p>
                            @else
                            <p class="text-[11px] text-slate-400 mb-4">Not started yet</p>
                            @endif

                            {{-- CTA --}}
                            <a href="{{ route('learner.learning.index', $enrollment->id) }}"
                               class="flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 rounded-xl transition-colors group-hover:shadow-md group-hover:shadow-blue-200">
                                @if(!$p['has_started'])
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Start Learning
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Resume
                                @endif
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Completed courses --}}
            <div id="tab-completed" class="hidden">
                @php $completed = $enrollments->where('status', 'completed'); @endphp

                @if($completed->isEmpty())
                <div class="py-10 text-center text-slate-400 text-sm">No completed courses yet. Keep going!</div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($completed as $enrollment)
                    <div class="course-card bg-white border border-slate-100 rounded-2xl overflow-hidden">
                        <div class="h-2 w-full bg-gradient-to-r from-green-400 to-emerald-500"></div>
                        <div class="p-5">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0 text-lg">
                                    ✅
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-slate-900 leading-snug line-clamp-2">
                                        {{ $enrollment->program->name }}
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $enrollment->cohort->name ?? 'Cohort' }}
                                    </p>
                                </div>
                            </div>

                            <div class="h-1.5 bg-green-100 rounded-full mb-4">
                                <div class="h-full bg-green-500 rounded-full w-full"></div>
                            </div>

                            <a href="{{ route('learner.learning.index', $enrollment->id) }}"
                               class="flex items-center justify-center gap-2 w-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold py-2.5 rounded-xl transition-colors">
                                Review Course
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            @endif {{-- end enrollments check --}}
        </div>

        {{-- ══════════════════════════════════════════════════════
             RIGHT — Upcoming Schedule
        ════════════════════════════════════════════════════════ --}}
        <div class="w-full lg:w-72 xl:w-80 flex-shrink-0">
            <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden sticky top-[76px]">

                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900">Upcoming Schedule</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Live sessions across your programs</p>
                </div>

                @if($upcomingSessions->isEmpty())
                <div class="px-5 py-10 text-center">
                    <div class="text-3xl mb-3">📅</div>
                    <p class="text-xs text-slate-400 font-medium">No upcoming sessions</p>
                </div>
                @else

                {{-- Group sessions by day --}}
                @php
                    $grouped = $upcomingSessions->groupBy(function($s) {
                        return $s->start_time->format('Y-m-d');
                    });
                @endphp

                <div class="divide-y divide-slate-50 max-h-[520px] overflow-y-auto">
                    @foreach($grouped as $date => $sessions)
                    @php
                        $dt = \Carbon\Carbon::parse($date);
                        $label = $dt->isToday() ? 'Today'
                               : ($dt->isTomorrow() ? 'Tomorrow'
                               : $dt->format('D, M j'));
                    @endphp

                    {{-- Date heading --}}
                    <div class="px-5 py-2 bg-slate-50 sticky top-0">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">
                            {{ $label }}
                        </span>
                    </div>

                    @foreach($sessions as $session)
                    @php
                        $typeColor = match($session->session_type ?? 'live_class') {
                            'live_class' => 'bg-indigo-500',
                            'workshop'   => 'bg-blue-500',
                            'q&a'        => 'bg-green-500',
                            'assessment' => 'bg-orange-400',
                            default      => 'bg-slate-400',
                        };
                    @endphp
                    <div class="schedule-item px-5 py-3">
                        <div class="flex gap-3">
                            <div class="session-dot {{ $typeColor }} mt-1.5"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-800 leading-snug line-clamp-1">
                                    {{ $session->title }}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $session->start_time->format('g:i A') }} · {{ $session->duration_minutes ?? '—' }} min
                                </p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $session->cohort->program->name ?? '' }}
                                </p>
                                @if($session->meet_link && $session->isUpcoming())
                                <a href="{{ $session->meet_link }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 mt-1.5 text-[11px] font-bold text-blue-600 hover:text-blue-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Join
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @endforeach
                </div>
                @endif

            </div>
        </div>

    </div>{{-- end flex --}}
</div>
@endsection

@push('scripts')
<script>
function switchTab(tab) {
    // Update panel visibility
    document.getElementById('tab-active').classList.toggle('hidden', tab !== 'active');
    document.getElementById('tab-completed').classList.toggle('hidden', tab !== 'completed');

    // Update tab styles
    document.querySelectorAll('[data-tab]').forEach(function(btn) {
        var isActive = btn.dataset.tab === tab;
        btn.classList.toggle('tab-active', isActive);
        btn.classList.toggle('tab-inactive', !isActive);
    });
}
</script>
@endpush