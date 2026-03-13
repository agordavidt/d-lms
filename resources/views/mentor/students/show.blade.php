@extends('mentor.layouts.app')
@section('title', $enrollment->user->first_name . ' ' . $enrollment->user->last_name)

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('mentor.students.index') }}">My Learners</a></div>
        <h1>{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</h1>
    </div>
</div>

<div class="container section">
<div style="display: grid; grid-template-columns: 1fr 280px; gap: 2rem; align-items: start;">

    {{-- Left: week progress --}}
    <div>
        <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem; margin-bottom: 1rem;">
            {{ $enrollment->program->name }}
        </h2>

        @foreach($enrollment->program->modules as $module)
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted); margin-bottom: 0.5rem;">
                {{ $module->title }}
            </div>

            @foreach($module->weeks as $week)
            @php $wp = $weekProgress[$week->id] ?? null; @endphp
            <div class="card" style="margin-bottom: 0.5rem;">
                <div class="card-body" style="padding: 0.9rem 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                        <div style="flex: 1;">
                            <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em;">Week {{ $week->week_number }}</div>
                            <div style="font-weight: 500; font-size: 0.9rem;">{{ $week->title }}</div>
                        </div>

                        <div style="display: flex; gap: 1.5rem; align-items: center; flex-shrink: 0;">
                            @if($wp)
                                {{-- Content progress --}}
                                <div style="text-align: center;">
                                    <div style="font-size: 0.75rem; color: var(--muted);">Content</div>
                                    <div style="font-size: 0.875rem; font-weight: 500;">
                                        {{ $wp->contents_completed }}/{{ $wp->total_contents }}
                                    </div>
                                </div>

                                {{-- Assessment --}}
                                @if($week->has_assessment)
                                <div style="text-align: center;">
                                    <div style="font-size: 0.75rem; color: var(--muted);">Assessment</div>
                                    <div style="font-size: 0.875rem; font-weight: 500;
                                                color: {{ $wp->assessment_passed ? 'var(--success)' : ($wp->assessment_attempts > 0 ? 'var(--error)' : 'var(--muted)') }}">
                                        @if($wp->assessment_passed)
                                            {{ number_format($wp->assessment_score, 0) }}% ✓
                                        @elseif($wp->assessment_attempts > 0)
                                            {{ number_format($wp->assessment_score, 0) }}% ({{ $wp->assessment_attempts }} attempt{{ $wp->assessment_attempts !== 1 ? 's' : '' }})
                                        @else
                                            Not attempted
                                        @endif
                                    </div>
                                </div>
                                @endif

                                {{-- Completed --}}
                                <div style="text-align: center;">
                                    <div style="font-size: 0.75rem; color: var(--muted);">Status</div>
                                    <div style="font-size: 0.875rem; color: {{ $wp->is_completed ? 'var(--success)' : ($wp->is_unlocked ? 'var(--text)' : 'var(--muted)') }}; font-weight: 500;">
                                        @if($wp->is_completed) Done
                                        @elseif($wp->is_unlocked) In Progress
                                        @else Locked
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-muted text-small">Not started</span>
                            @endif
                        </div>
                    </div>

                    {{-- Thin progress bar --}}
                    @if($wp && $wp->is_unlocked)
                    <div class="progress-bar-track" style="margin-top: 0.6rem;">
                        <div class="progress-bar-fill" style="width: {{ $wp->progress_percentage }}%"></div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- Right: summary card --}}
    <div>
        <div class="card card-body" style="margin-bottom: 1rem;">
            <div style="font-weight: 600; margin-bottom: 1rem; font-family: 'Source Serif 4', serif;">Summary</div>

            <div style="display: grid; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted text-small">Overall Progress</span>
                    <span style="font-weight: 500; font-size: 0.875rem;">{{ number_format($enrollment->progress_percentage, 0) }}%</span>
                </div>
                <div class="progress-bar-track">
                    <div class="progress-bar-fill" style="width: {{ $enrollment->progress_percentage }}%"></div>
                </div>

                @if($enrollment->weekly_assessment_avg !== null)
                <div style="display: flex; justify-content: space-between; margin-top: 0.25rem;">
                    <span class="text-muted text-small">Assessment Average</span>
                    <span style="font-weight: 500; font-size: 0.875rem;">{{ number_format($enrollment->weekly_assessment_avg, 1) }}%</span>
                </div>
                @endif

                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted text-small">Enrolled</span>
                    <span class="text-small">{{ \Carbon\Carbon::parse($enrollment->enrolled_at)->format('M j, Y') }}</span>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted text-small">Graduation</span>
                    <span class="text-small">
                        {{ match($enrollment->graduation_status) {
                            'graduated'      => 'Graduated',
                            'pending_review' => 'Pending Review',
                            default          => 'In Progress',
                        } }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card card-body">
            <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif;">Contact</div>
            <div class="text-small">{{ $enrollment->user->email }}</div>
            @if($enrollment->user->phone)
            <div class="text-small" style="margin-top: 0.25rem;">{{ $enrollment->user->phone }}</div>
            @endif
        </div>
    </div>

</div>
</div>
@endsection