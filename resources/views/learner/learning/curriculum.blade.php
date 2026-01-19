@extends('layouts.admin')

@section('title', 'Curriculum')
@section('breadcrumb-parent', 'Learning')
@section('breadcrumb-current', 'Curriculum')

@push('styles')
<style>
.module-card {
    border-left: 4px solid #7571f9;
}
.week-item {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}
.week-item:hover {
    background-color: #f8f9fa;
    border-left-color: #7571f9;
}
.week-item.unlocked {
    cursor: pointer;
}
.week-item.locked {
    opacity: 0.6;
    background-color: #f9f9f9;
}
.week-item.completed {
    background-color: #f1f9f4;
    border-left-color: #28a745;
}
.week-status-icon {
    font-size: 24px;
    width: 40px;
    text-align: center;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-2">{{ $enrollment->program->name }}</h3>
                        <p class="text-muted mb-0">{{ $enrollment->cohort->name }} • {{ $enrollment->program->duration }}</p>
                    </div>
                    <div class="text-right">
                        <h2 style="color: #7571f9;">{{ $enrollment->learning_progress }}%</h2>
                        <p class="text-muted mb-0">Overall Progress</p>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" 
                         style="width: {{ $enrollment->learning_progress }}%; background-color: #7571f9;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules and Weeks -->
        @foreach($modules as $module)
        <div class="card mt-3 module-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ $module->title }}</h5>
                        @if($module->description)
                            <p class="text-muted mb-0">{{ $module->description }}</p>
                        @endif
                    </div>
                    <span class="badge badge-light">{{ $module->duration_weeks }} weeks</span>
                </div>

                @if($module->learning_objectives && count($module->learning_objectives) > 0)
                <div class="mb-3 p-2" style="background-color: #f8f9fa; border-radius: 4px;">
                    <small class="text-muted d-block mb-1"><strong>Module Objectives:</strong></small>
                    <ul class="pl-3 mb-0" style="font-size: 13px;">
                        @foreach($module->learning_objectives as $objective)
                            <li>{{ $objective }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Weeks in this Module -->
                <div class="list-group list-group-flush">
                    @foreach($module->weeks as $week)
                        @php
                            $weekProgress = $week->weekProgress->first();
                            $isUnlocked = $weekProgress && $weekProgress->is_unlocked;
                            $isCompleted = $weekProgress && $weekProgress->is_completed;
                            $progressPercentage = $weekProgress ? $weekProgress->progress_percentage : 0;
                        @endphp
                        
                        <div class="list-group-item week-item {{ $isUnlocked ? 'unlocked' : 'locked' }} {{ $isCompleted ? 'completed' : '' }}"
                             @if($isUnlocked) onclick="window.location.href='{{ route('learner.learning.week', $week->id) }}'" @endif>
                            <div class="d-flex align-items-center">
                                <div class="week-status-icon mr-3">
                                    @if($isCompleted)
                                        <span style="color: #28a745;">✓</span>
                                    @elseif($isUnlocked)
                                        <span style="color: #7571f9;">{{ $week->week_number }}</span>
                                    @else
                                        <span style="color: #ccc;">🔒</span>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                Week {{ $week->week_number }}: {{ $week->title }}
                                                @if(!$isUnlocked)
                                                    <span class="badge badge-secondary">Locked</span>
                                                @elseif($isCompleted)
                                                    <span class="badge badge-success">Completed</span>
                                                @elseif($weekProgress && $weekProgress->progress_percentage > 0)
                                                    <span class="badge badge-info">In Progress</span>
                                                @endif
                                            </h6>
                                            @if($week->description)
                                                <p class="text-muted mb-0" style="font-size: 13px;">{{ Str::limit($week->description, 100) }}</p>
                                            @endif
                                            @if($isUnlocked && $weekProgress)
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        {{ $weekProgress->contents_completed }} of {{ $weekProgress->total_contents }} contents complete
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                        @if($isUnlocked)
                                            <div class="text-right ml-3">
                                                <div class="mb-1" style="font-size: 18px; color: #7571f9; font-weight: bold;">
                                                    {{ $progressPercentage }}%
                                                </div>
                                                <div class="progress" style="width: 80px; height: 6px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $progressPercentage }}%; background-color: {{ $isCompleted ? '#28a745' : '#7571f9' }};">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($week->learning_outcomes && count($week->learning_outcomes) > 0 && $isUnlocked)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <strong>Learning Outcomes:</strong>
                                                {{ implode(' • ', array_slice($week->learning_outcomes, 0, 2)) }}
                                                @if(count($week->learning_outcomes) > 2)
                                                    and {{ count($week->learning_outcomes) - 2 }} more
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <!-- Legend -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="mb-3">Legend</h6>
                <div class="row">
                    <div class="col-md-3">
                        <span style="color: #28a745; font-size: 18px;">✓</span>
                        <span class="ml-2">Completed</span>
                    </div>
                    <div class="col-md-3">
                        <span style="color: #7571f9; font-size: 18px;">1</span>
                        <span class="ml-2">Unlocked / Current</span>
                    </div>
                    <div class="col-md-3">
                        <span style="color: #ccc; font-size: 18px;">🔒</span>
                        <span class="ml-2">Locked</span>
                    </div>
                    <div class="col-md-3">
                        <span class="badge badge-info">In Progress</span>
                        <span class="ml-2">Started</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-3 mb-4">
            <a href="{{ route('learner.learning.index') }}" class="btn btn-outline-primary">
                Back to Current Week
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add tooltip for locked weeks
    const lockedWeeks = document.querySelectorAll('.week-item.locked');
    lockedWeeks.forEach(week => {
        week.style.cursor = 'not-allowed';
        week.title = 'Complete previous weeks to unlock this week';
    });
});
</script>
@endpush