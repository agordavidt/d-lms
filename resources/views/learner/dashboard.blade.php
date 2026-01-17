@extends('layouts.admin')

@section('title', 'My Learning Dashboard')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Overview')

@section('content')

<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-gradient-primary text-white">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h3 font-weight-bold mb-2">Welcome back, {{ auth()->user()->first_name }}! 👋</h2>
                        <p class="mb-0 opacity-90">Ready to continue your learning journey? Here's your overview.</p>
                    </div>
                    <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                        <a href="{{ route('learner.programs.index') }}" class="btn btn-light btn-lg">
                            <i class="icon-plus mr-2"></i>Browse Programs
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
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small font-weight-semibold text-uppercase">Active Programs</p>
                        <h2 class="h1 font-weight-bold mb-0">{{ $stats['active_programs'] }}</h2>
                    </div>
                    <div class="rounded-circle bg-gradient-primary p-3">
                        <i class="icon-book-open text-white"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small font-weight-semibold text-uppercase">Upcoming Sessions</p>
                        <h2 class="h1 font-weight-bold mb-0">{{ $stats['upcoming_sessions'] }}</h2>
                    </div>
                    <div class="rounded-circle bg-gradient-info p-3">
                        <i class="icon-calendar text-white"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small font-weight-semibold text-uppercase">Sessions Attended</p>
                        <h2 class="h1 font-weight-bold mb-0">{{ $stats['sessions_attended'] }}</h2>
                        <small class="text-muted">of {{ $stats['total_sessions'] }} total</small>
                    </div>
                    <div class="rounded-circle bg-gradient-success p-3">
                        <i class="icon-check text-white"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: {{ $stats['attendance_percentage'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small font-weight-semibold text-uppercase">Attendance Rate</p>
                        <h2 class="h1 font-weight-bold mb-0">{{ $stats['attendance_percentage'] }}%</h2>
                    </div>
                    <div class="rounded-circle bg-gradient-warning p-3">
                        <i class="icon-graph text-white"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: {{ $stats['attendance_percentage'] }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Sessions Alert -->
@if($todaySessions->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="icon-bell mr-3 h4 mb-0"></i>
                <div>
                    <h5 class="mb-1 font-weight-bold">You have {{ $todaySessions->count() }} session(s) scheduled for today!</h5>
                    <p class="mb-0">Don't forget to join on time. Check your schedule below.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- My Programs -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="icon-book-open text-primary mr-2"></i>My Programs
                    </h5>
                    <a href="{{ route('learner.programs.index') }}" class="btn btn-sm btn-outline-primary">
                        Browse More
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($enrollments as $enrollment)
                <div class="border-bottom p-4 hover-bg-light transition">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="font-weight-bold mb-2">{{ $enrollment->program->name }}</h6>
                            <p class="text-muted small mb-2">
                                <i class="icon-layers mr-1"></i>
                                Cohort: {{ $enrollment->cohort->name }}
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="icon-calendar mr-1"></i>
                                {{ $enrollment->cohort->start_date->format('M d') }} - {{ $enrollment->cohort->end_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($enrollment->status === 'active')
                            <span class="badge badge-success mb-2">Active</span>
                            @elseif($enrollment->status === 'pending')
                            <span class="badge badge-warning mb-2">Pending Payment</span>
                            @endif
                            <div class="text-muted small">{{ round($enrollment->progress_percentage, 0) }}% Complete</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-primary" 
                            role="progressbar" 
                            style="width: {{ $enrollment->progress_percentage }}%"
                            aria-valuenow="{{ $enrollment->progress_percentage }}" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                        </div>
                    </div>

                    <!-- Payment Status -->
                    @php
                        $totalPaid = $enrollment->payments->where('status', 'successful')->sum('final_amount');
                        $isPaid = $totalPaid >= $enrollment->program->price;
                    @endphp

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($isPaid)
                            <span class="badge badge-success">
                                <i class="icon-check mr-1"></i>Fully Paid
                            </span>
                            @else
                            <span class="badge badge-warning">
                                <i class="icon-info mr-1"></i>Balance: ₦{{ number_format($enrollment->remaining_balance, 2) }}
                            </span>
                            @endif
                        </div>
                        <div>
                            @if($enrollment->status === 'pending')
                            <form action="{{ route('payment.initiate') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="program_id" value="{{ $enrollment->program_id }}">
                                <input type="hidden" name="cohort_id" value="{{ $enrollment->cohort_id }}">
                                <input type="hidden" name="payment_plan" value="one-time">
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="icon-credit-card mr-1"></i>Complete Payment
                                </button>
                            </form>
                            @elseif(!$isPaid && $enrollment->status === 'active')
                            <form action="{{ route('payment.installment') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="icon-credit-card mr-1"></i>Pay Balance
                                </button>
                            </form>
                            @else
                            <a href="{{ route('learner.calendar') }}" class="btn btn-sm btn-primary">
                                <i class="icon-calendar mr-1"></i>View Schedule
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- WhatsApp Link (if available and active) -->
                    @if($enrollment->status === 'active' && $enrollment->cohort->whatsapp_link)
                    <div class="mt-3 pt-3 border-top">
                        <a href="{{ $enrollment->cohort->whatsapp_link }}" target="_blank" class="btn btn-sm btn-success btn-block">
                            <i class="bi bi-whatsapp mr-2"></i>Join WhatsApp Community
                        </a>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="icon-book-open display-4 text-muted mb-3"></i>
                    <h6 class="text-muted">You're not enrolled in any programs yet</h6>
                    <p class="text-muted small mb-3">Start your learning journey today!</p>
                    <a href="{{ route('learner.programs.index') }}" class="btn btn-primary">
                        <i class="icon-plus mr-2"></i>Browse Programs
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Upcoming Sessions -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 font-weight-bold">
                    <i class="icon-calendar text-info mr-2"></i>Upcoming Sessions
                </h5>
            </div>
            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                @forelse($upcomingSessions as $session)
                <div class="border-bottom p-3 hover-bg-light transition">
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle bg-info text-white p-2 mr-3 flex-shrink-0">
                            <i class="icon-video"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="font-weight-bold mb-1 small">{{ $session->title }}</h6>
                            <p class="text-muted small mb-1">{{ $session->program->name }}</p>
                            <p class="text-muted small mb-2">
                                <i class="icon-calendar mr-1"></i>
                                {{ $session->start_time->format('M d, g:i A') }}
                            </p>
                            @if($session->meet_link)
                            <a href="{{ $session->meet_link }}" target="_blank" class="btn btn-sm btn-outline-info btn-block">
                                <i class="icon-video mr-1"></i>Join Meeting
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="icon-calendar text-muted h3 mb-2"></i>
                    <p class="text-muted small mb-0">No upcoming sessions</p>
                </div>
                @endforelse
            </div>
            @if($upcomingSessions->count() > 0)
            <div class="card-footer bg-white border-0 text-center">
                <a href="{{ route('learner.calendar') }}" class="text-primary font-weight-semibold small">
                    View Full Calendar <i class="icon-arrow-right ml-1"></i>
                </a>
            </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 font-weight-bold">
                    <i class="icon-settings text-secondary mr-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <a href="{{ route('learner.calendar') }}" class="btn btn-outline-primary btn-block mb-2">
                    <i class="icon-calendar mr-2"></i>My Schedule
                </a>
                <a href="{{ route('learner.programs.index') }}" class="btn btn-outline-primary btn-block mb-2">
                    <i class="icon-book-open mr-2"></i>Browse Programs
                </a>
                <a href="#" class="btn btn-outline-primary btn-block">
                    <i class="icon-user mr-2"></i>My Profile
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Pending Enrollments Warning -->
@if($stats['pending_enrollments'] > 0)
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="icon-info mr-3 h4 mb-0"></i>
                <div>
                    <strong>Action Required:</strong> You have {{ $stats['pending_enrollments'] }} pending enrollment(s). 
                    Complete your payment to activate full access to program materials and live sessions.
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .hover-bg-light:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
    .transition {
        transition: all 0.2s ease;
    }
</style>
@endpush