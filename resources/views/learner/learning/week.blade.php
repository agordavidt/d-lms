@extends('layouts.admin')

@section('title', $week->title)
@section('breadcrumb-parent', 'Learning')
@section('breadcrumb-current', 'Week ' . $week->week_number)

@push('styles')
<style>
.content-item {
    border-left: 4px solid #7571f9;
    transition: all 0.3s ease;
}
.content-item:hover {
    background-color: #f8f9fa;
    border-left-color: #4c49d4;
}
.content-item.completed {
    border-left-color: #28a745;
    opacity: 0.9;
}
.content-icon {
    font-size: 32px;
    width: 50px;
    text-align: center;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Week Navigation -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('learner.learning.curriculum') }}" class="btn btn-outline-primary btn-sm">
                        ← Back to Curriculum
                    </a>
                    <div class="text-center">
                        <h6 class="text-muted mb-0">{{ $week->programModule->title }}</h6>
                    </div>
                    <span></span>
                </div>
            </div>
        </div>

        <!-- Week Header -->
        <!-- Replace the week-header section in curriculum/index.blade.php with this updated version -->

        <div class="week-header" onclick="toggleWeek({{ $week->id }})">
            <div class="week-title-area">
                <div class="week-number {{ $isUnlocked ? 'unlocked' : '' }}">
                    W{{ $week->week_number }}
                </div>
                <span class="week-title-text">{{ $week->title }}</span>
            </div>
            
            <div class="week-status">
                <!-- Content Progress -->
                <span style="font-size: 13px; color: #666; margin-right: 12px;">
                    Content: {{ $weekCompletedCount }}/{{ $weekContentsCount }}
                </span>
                
                <!-- Assessment Status (if week has assessment) -->
                @if($week->has_assessment && $week->assessment)
                    @php
                        $assessmentScore = $weekProgress ? $weekProgress->assessment_score : null;
                        $assessmentAttempts = $weekProgress ? $weekProgress->assessment_attempts : 0;
                    @endphp
                    
                    @if($assessmentScore !== null)
                        <span class="assessment-score" style="font-size: 13px; color: #2e7d32; font-weight: 600; margin-right: 12px;">
                            Assessment: {{ number_format($assessmentScore, 1) }}%
                        </span>
                    @elseif($assessmentAttempts === 0 && $isUnlocked)
                        <span class="assessment-pending" style="font-size: 13px; color: #f57c00; margin-right: 12px;">
                            Assessment: Pending
                        </span>
                    @endif
                @endif
                
                <!-- Week Status Icon -->
                <div class="status-icon {{ $isCompleted ? 'status-completed' : ($isUnlocked ? 'status-in-progress' : 'status-locked') }}">
                    @if($isCompleted)
                        ✓
                    @elseif($isUnlocked)
                        ⋯
                    @else
                        🔒
                    @endif
                </div>
                
                <span style="margin-left: 8px; margin-right: 8px;">{{ $chevron }}</span>
            </div>
        </div>

        <!-- Week Contents -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-4">Week Content</h5>

                @if($contents->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($contents as $content)
                            @php
                                $progress = $content->contentProgress->first();
                                $isCompleted = $progress && $progress->is_completed;
                            @endphp
                            <div class="list-group-item content-item {{ $isCompleted ? 'completed' : '' }} mb-3 rounded">
                                <div class="d-flex align-items-start">
                                    <div class="content-icon mr-3">{{ $content->icon }}</div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            {{ $content->title }}
                                            @if($content->is_required)
                                                <span class="badge badge-primary">Required</span>
                                            @endif
                                            @if($isCompleted)
                                                <span class="badge badge-success">✓ Completed</span>
                                            @endif
                                        </h6>
                                        <div class="mb-2">
                                            <span class="badge badge-light">{{ $content->type_display }}</span>
                                            @if($content->content_type === 'video' && $content->video_duration_minutes)
                                                <span class="text-muted" style="font-size: 12px;">• {{ $content->video_duration_minutes }} min</span>
                                            @endif
                                        </div>
                                        @if($content->description)
                                            <p class="text-muted mb-2" style="font-size: 13px;">{{ $content->description }}</p>
                                        @endif
                                        <div class="mt-2">
                                            <a href="{{ route('learner.learning.content', $content->id) }}" 
                                               class="btn btn-sm {{ $isCompleted ? 'btn-outline-primary' : 'btn-primary' }}">
                                                {{ $isCompleted ? 'Review Again' : 'Start' }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted">No content available for this week yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Live Sessions -->
        @if($sessions->count() > 0)
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-4">Live Sessions</h5>
                @foreach($sessions as $session)
                    <div class="d-flex align-items-center p-3 mb-2 rounded" style="background-color: #f8f9fa;">
                        <div class="mr-3" style="font-size: 32px;">🎓</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $session->title }}</h6>
                            <p class="text-muted mb-0" style="font-size: 13px;">
                                {{ $session->start_time->format('M d, Y - g:i A') }}
                            </p>
                        </div>
                        @if($session->meet_link && $session->status === 'scheduled')
                            <a href="{{ $session->meet_link }}" target="_blank" class="btn btn-primary btn-sm">
                                Join Meeting
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-3">WEEK PROGRESS</h6>
                <div class="text-center mb-3">
                    <h1 style="color: #7571f9;">{{ $weekProgress->progress_percentage }}%</h1>
                    <p class="text-muted mb-0">{{ $weekProgress->contents_completed }} of {{ $weekProgress->total_contents }} complete</p>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-muted mb-3">NAVIGATION</h6>
                <a href="{{ route('learner.learning.index') }}" class="btn btn-outline-primary btn-block mb-2">
                    Current Week
                </a>
                <a href="{{ route('learner.learning.curriculum') }}" class="btn btn-outline-primary btn-block">
                    Full Curriculum
                </a>
            </div>
        </div>
    </div>
</div>
@endsection