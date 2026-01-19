@extends('layouts.admin')

@section('title', 'My Learning')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Learning')

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
.content-item.completed .content-title {
    color: #6c757d;
}
.content-icon {
    font-size: 32px;
    width: 50px;
    text-align: center;
}
.progress-ring {
    width: 120px;
    height: 120px;
}
.stat-card {
    border-left: 4px solid #7571f9;
}
</style>
@endpush

@section('content')
<div class="row">
    <!-- Main Learning Area (70%) -->
    <div class="col-lg-8">
        <!-- Current Week Header -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted mb-1">{{ $currentWeek->programModule->title }}</h6>
                        <h3 class="mb-0">{{ $currentWeek->title }}</h3>
                        <p class="text-muted mt-2 mb-0">Week {{ $currentWeek->week_number }} of {{ $enrollment->program->total_weeks }}</p>
                    </div>
                    <div class="text-center">
                        <div class="mb-2">
                            <h2 class="mb-0" style="color: #7571f9;">{{ $currentWeekProgress->progress_percentage }}%</h2>
                            <small class="text-muted">Complete</small>
                        </div>
                        <div class="progress" style="height: 8px; width: 120px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $currentWeekProgress->progress_percentage }}%; background-color: #7571f9;"
                                 aria-valuenow="{{ $currentWeekProgress->progress_percentage }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>

                @if($currentWeek->description)
                <p class="mb-0">{{ $currentWeek->description }}</p>
                @endif

                @if($currentWeek->learning_outcomes && count($currentWeek->learning_outcomes) > 0)
                <div class="mt-3">
                    <strong class="text-muted" style="font-size: 12px;">WHAT YOU'LL LEARN:</strong>
                    <ul class="pl-3 mb-0 mt-2">
                        @foreach($currentWeek->learning_outcomes as $outcome)
                            <li style="font-size: 14px;">{{ $outcome }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
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
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 content-title">
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
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            @if($isCompleted)
                                                <a href="{{ route('learner.learning.content', $content->id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    Review Again
                                                </a>
                                            @else
                                                <a href="{{ route('learner.learning.content', $content->id) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    @if($content->content_type === 'video')
                                                        Watch Video
                                                    @elseif($content->content_type === 'pdf')
                                                        Read Document
                                                    @elseif($content->content_type === 'link')
                                                        Open Resource
                                                    @else
                                                        Read Article
                                                    @endif
                                                </a>
                                                @if($progress && $progress->progress_percentage > 0)
                                                    <span class="text-muted ml-2" style="font-size: 12px;">
                                                        {{ $progress->progress_percentage }}% complete
                                                    </span>
                                                @endif
                                            @endif
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

        <!-- Live Sessions This Week -->
        @if($upcomingSessions->count() > 0)
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-4">Live Sessions This Week</h5>
                @foreach($upcomingSessions as $session)
                    <div class="d-flex align-items-center p-3 mb-2 rounded" style="background-color: #f8f9fa; border-left: 4px solid #7571f9;">
                        <div class="mr-3" style="font-size: 32px;">🎓</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $session->title }}</h6>
                            <p class="text-muted mb-0" style="font-size: 13px;">
                                {{ $session->start_time->format('l, M d') }} • 
                                {{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}
                                @if($session->mentor)
                                    • {{ $session->mentor->name }}
                                @endif
                            </p>
                        </div>
                        @if($session->meet_link)
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

    <!-- Sidebar (30%) -->
    <div class="col-lg-4">
        <!-- Overall Progress -->
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-3">OVERALL PROGRESS</h6>
                <div class="text-center mb-3">
                    <h1 style="color: #7571f9;">{{ $stats['overall_progress'] }}%</h1>
                    <p class="text-muted mb-0">{{ $stats['completed_weeks'] }} of {{ $stats['total_weeks'] }} weeks complete</p>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" 
                         style="width: {{ $stats['overall_progress'] }}%; background-color: #7571f9;"
                         aria-valuenow="{{ $stats['overall_progress'] }}" 
                         aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-muted mb-3">STATISTICS</h6>
                
                <div class="mb-3 pb-3" style="border-bottom: 1px solid #e9ecef;">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Contents Completed</span>
                        <strong style="color: #7571f9;">{{ $stats['completed_contents'] }}/{{ $stats['total_contents'] }}</strong>
                    </div>
                </div>

                <div class="mb-3 pb-3" style="border-bottom: 1px solid #e9ecef;">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Session Attendance</span>
                        <strong style="color: #7571f9;">{{ $stats['attendance_rate'] }}%</strong>
                    </div>
                    <small class="text-muted">{{ $stats['attended_sessions'] }} of {{ $stats['total_sessions'] }} sessions</small>
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Program</span>
                        <strong style="color: #7571f9;">{{ $enrollment->program->name }}</strong>
                    </div>
                    <small class="text-muted">{{ $enrollment->cohort->name }}</small>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-muted mb-3">QUICK ACTIONS</h6>
                <a href="{{ route('learner.learning.curriculum') }}" class="btn btn-outline-primary btn-block mb-2">
                    View Full Curriculum
                </a>
                <a href="{{ route('learner.calendar') }}" class="btn btn-outline-primary btn-block mb-2">
                    View All Sessions
                </a>
                <a href="{{ route('learner.profile.edit') }}" class="btn btn-outline-primary btn-block">
                    Update Profile
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        @if($recentContents->count() > 0)
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-muted mb-3">RECENT ACTIVITY</h6>
                @foreach($recentContents->take(5) as $recent)
                    <div class="mb-3">
                        <small class="text-muted d-block">
                            {{ $recent->last_accessed_at->diffForHumans() }}
                        </small>
                        <a href="{{ route('learner.learning.content', $recent->weekContent->id) }}" 
                           style="font-size: 13px; color: #495057;">
                            {{ $recent->weekContent->icon }} {{ Str::limit($recent->weekContent->title, 35) }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh session status every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush