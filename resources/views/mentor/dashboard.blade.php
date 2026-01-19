@extends('layouts.admin')

@section('title', 'Mentor Dashboard')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Overview')

@section('content')

<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-gradient-7 text-white">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h3 font-weight-bold mb-2">Welcome, {{ auth()->user()->first_name }}!</h2>
                        <p class="mb-0 opacity-90">Manage your classes, track student progress, and deliver exceptional learning experiences.</p>
                    </div>
                    <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                        <a href="{{ route('mentor.sessions.create') }}" class="btn btn-light btn-lg">
                            <i class="icon-plus mr-2"></i>Schedule Session
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card gradient-1 border-0 shadow-sm">
            <div class="card-body text-white">
                <h3 class="card-title">Total Sessions</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ $stats['total_sessions'] }}</h2>
                    <p class="text-white mb-0">All Time</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-calendar"></i></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card gradient-4 border-0 shadow-sm">
            <div class="card-body text-white">
                <h3 class="card-title">Upcoming</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ $stats['upcoming_sessions'] }}</h2>
                    <p class="text-white mb-0">Sessions Scheduled</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-clock"></i></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card gradient-8 border-0 shadow-sm">
            <div class="card-body text-white">
                <h3 class="card-title">Students</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ $stats['total_students'] }}</h2>
                    <p class="text-white mb-0">Active Learners</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-people"></i></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card gradient-7 border-0 shadow-sm">
            <div class="card-body text-white">
                <h3 class="card-title">Avg Attendance</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ $stats['average_attendance'] }}</h2>
                    <p class="text-white mb-0">Per Session</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-graph"></i></span>
            </div>
        </div>
    </div>
</div>

<!-- Today's Sessions Alert -->
@if($todaySessions->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="icon-bell mr-3 h4 mb-0"></i>
                <div>
                    <h5 class="mb-1 font-weight-bold">You have {{ $todaySessions->count() }} session(s) today!</h5>
                    <p class="mb-0">Make sure you're prepared and materials are ready.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Upcoming Sessions -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="icon-calendar text-primary mr-2"></i>Upcoming Sessions
                    </h5>
                    <a href="{{ route('mentor.sessions.calendar') }}" class="btn btn-sm btn-outline-primary">
                        View Calendar
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($upcomingSessions as $session)
                <div class="border-bottom p-4 hover-bg-light">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-start mb-2">
                                <div class="rounded-circle bg-primary text-white p-2 mr-3">
                                    <i class="icon-video"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1">{{ $session->title }}</h6>
                                    <p class="text-muted small mb-1">
                                        <i class="icon-book-open mr-1"></i>{{ $session->program->name }}
                                    </p>
                                    <p class="text-muted small mb-1">
                                        <i class="icon-layers mr-1"></i>{{ $session->cohort->name }}
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="icon-calendar mr-1"></i>
                                        {{ $session->start_time->format('D, M d, Y \a\t g:i A') }}
                                        <span class="text-primary">({{ $session->start_time->diffForHumans() }})</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                            <div class="btn-group">
                                @if($session->meet_link)
                                <a href="{{ $session->meet_link }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="icon-video mr-1"></i>Join
                                </a>
                                @endif
                                <a href="{{ route('mentor.sessions.edit', $session) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="icon-pencil"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="icon-calendar display-4 text-muted mb-3"></i>
                    <h6 class="text-muted">No upcoming sessions</h6>
                    <p class="text-muted small mb-3">Schedule your next class</p>
                    <a href="{{ route('mentor.sessions.create') }}" class="btn btn-primary">
                        <i class="icon-plus mr-2"></i>Schedule Session
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Completed Sessions -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 font-weight-bold">
                    <i class="icon-check text-success mr-2"></i>Recent Completed Sessions
                </h5>
            </div>
            <div class="card-body p-0">
                @forelse($recentSessions as $session)
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="font-weight-bold mb-1 small">{{ $session->title }}</h6>
                            <p class="text-muted small mb-1">{{ $session->cohort->name }}</p>
                            <p class="text-muted small mb-0">
                                <i class="icon-calendar mr-1"></i>{{ $session->end_time->format('M d, Y') }}
                                <span class="ml-3">
                                    <i class="icon-people mr-1"></i>{{ $session->total_attendees }} attended
                                </span>
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('mentor.sessions.show', $session) }}" class="btn btn-sm btn-outline-secondary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <p class="text-muted small mb-0">No completed sessions yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Students & Quick Actions -->
    <div class="col-lg-4 mb-4">
        <!-- My Students -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="icon-people text-info mr-2"></i>My Students
                    </h5>
                    <span class="badge badge-primary">{{ $stats['total_students'] }}</span>
                </div>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                @forelse($students->take(10) as $enrollment)
                <div class="border-bottom p-3 hover-bg-light">
                    <div class="d-flex align-items-center">
                        <img src="{{ $enrollment->user->avatar_url }}" 
                            class="rounded-circle mr-3" 
                            width="40" 
                            height="40" 
                            alt="{{ $enrollment->user->name }}">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small font-weight-bold">{{ $enrollment->user->name }}</h6>
                            <p class="text-muted small mb-0">{{ $enrollment->program->name }}</p>
                        </div>
                        <div>
                            <span class="badge badge-light">{{ round($enrollment->progress_percentage) }}%</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <p class="text-muted small mb-0">No students yet</p>
                </div>
                @endforelse
            </div>
            @if($students->count() > 10)
            <div class="card-footer bg-white border-0 text-center">
                <a href="{{ route('mentor.students.index') }}" class="text-primary small font-weight-semibold">
                    View All Students <i class="icon-arrow-right ml-1"></i>
                </a>
            </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 font-weight-bold">
                    <i class="icon-settings text-secondary mr-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <a href="{{ route('mentor.sessions.create') }}" class="btn btn-primary btn-block mb-2">
                    <i class="icon-plus mr-2"></i>Schedule New Session
                </a>
                <a href="{{ route('mentor.sessions.calendar') }}" class="btn btn-outline-primary btn-block mb-2">
                    <i class="icon-calendar mr-2"></i>View Calendar
                </a>
                <a href="{{ route('mentor.students.index') }}" class="btn btn-outline-primary btn-block">
                    <i class="icon-people mr-2"></i>Manage Students
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-bg-light:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
</style>
@endpush