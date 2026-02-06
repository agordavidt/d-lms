@extends('layouts.admin')

@section('title', 'Learner Details')
@section('breadcrumb-parent', 'Learners')
@section('breadcrumb-current', 'Details')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <a href="{{ route('admin.learners.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="fa fa-arrow-left mr-1"></i> Back to Learners
    </a>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <img src="{{ $learner->avatar_url }}" 
                                     class="rounded mr-3" 
                                     style="width: 80px; height: 80px; object-fit: cover;" 
                                     alt="{{ $learner->name }}">
                                <div>
                                    <h3 class="mb-1 font-weight-bold">{{ $learner->name }}</h3>
                                    <p class="text-muted mb-1">{{ $learner->email }}</p>
                                    <p class="text-muted mb-0">{{ $learner->phone ?? 'No phone number' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right mt-3 mt-md-0">
                            <div class="mb-2">
                                @switch($learner->status)
                                    @case('active')
                                        <span class="badge badge-success px-3 py-2">
                                            <i class="fa fa-check-circle mr-1"></i> Active
                                        </span>
                                        @break
                                    @case('suspended')
                                        <span class="badge badge-danger px-3 py-2">
                                            <i class="fa fa-ban mr-1"></i> Suspended
                                        </span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary px-3 py-2">
                                            <i class="fa fa-minus-circle mr-1"></i> Inactive
                                        </span>
                                @endswitch
                            </div>
                            <div class="text-muted small">
                                Joined {{ $learner->created_at->format('F d, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($enrollment)
    <!-- Enrollment Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold mb-4">
                        <i class="fa fa-book text-primary mr-2"></i>Enrollment Information
                    </h5>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted text-uppercase small font-weight-bold mb-1">Program</label>
                        <p class="mb-0 font-weight-bold">{{ $enrollment->program->name }}</p>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted text-uppercase small font-weight-bold mb-1">Cohort</label>
                        <p class="mb-0">{{ $enrollment->cohort->name }}</p>
                        <small class="text-muted">
                            {{ $enrollment->cohort->start_date->format('M d, Y') }} - 
                            {{ $enrollment->cohort->end_date->format('M d, Y') }}
                        </small>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted text-uppercase small font-weight-bold mb-1">Mentor</label>
                        <p class="mb-0">{{ $enrollment->cohort->mentor ? $enrollment->cohort->mentor->name : 'Not Assigned' }}</p>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted text-uppercase small font-weight-bold mb-1">Enrollment Status</label>
                        <div>
                            @switch($enrollment->status)
                                @case('active')
                                    <span class="badge badge-success">Active</span>
                                    @break
                                @case('pending')
                                    <span class="badge badge-warning">Pending Payment</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ ucfirst($enrollment->status) }}</span>
                            @endswitch
                        </div>
                    </div>

                    <div>
                        <label class="text-muted text-uppercase small font-weight-bold mb-1">Enrolled On</label>
                        <p class="mb-0">{{ $enrollment->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold mb-4">
                        <i class="fa fa-credit-card text-success mr-2"></i>Payment Information
                    </h5>

                    @if($enrollment->payments->count() > 0)
                        @foreach($enrollment->payments as $payment)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <label class="text-muted text-uppercase small font-weight-bold mb-1">
                                        @if($payment->installment_number)
                                            Installment {{ $payment->installment_number }}
                                        @else
                                            Full Payment
                                        @endif
                                    </label>
                                    <p class="mb-0 h5 font-weight-bold text-dark">
                                        ₦{{ number_format($payment->final_amount, 2) }}
                                    </p>
                                </div>
                                <div>
                                    @switch($payment->status)
                                        @case('successful')
                                            <span class="badge badge-success">Paid</span>
                                            @break
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
                                            @break
                                        @default
                                            <span class="badge badge-danger">Failed</span>
                                    @endswitch
                                </div>
                            </div>
                            
                            @if($payment->discount_amount > 0)
                            <p class="mb-1 small text-muted">
                                <i class="fa fa-tag mr-1"></i>
                                Discount Applied: ₦{{ number_format($payment->discount_amount, 2) }}
                            </p>
                            @endif
                            
                            <p class="mb-1 small text-muted">
                                <i class="fa fa-calendar-check mr-1"></i>
                                {{ $payment->paid_at ? $payment->paid_at->format('M d, Y @ h:i A') : 'Not paid yet' }}
                            </p>
                            
                            <p class="mb-0 small text-muted">
                                <i class="fa fa-hashtag mr-1"></i>
                                Ref: {{ $payment->reference }}
                            </p>
                        </div>
                        @endforeach

                        @if($enrollment->payments->where('installment_number', 1)->where('status', 'successful')->count() > 0 && 
                            $enrollment->payments->where('installment_number', 2)->count() === 0)
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle mr-2"></i>
                            Second installment pending
                        </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle mr-2"></i>
                            No payment records found
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Learning Progress -->
    @if($progressStats)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold mb-4">
                        <i class="fa fa-line-chart text-info mr-2"></i>Learning Progress
                    </h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 bg-light rounded">
                                <h2 class="mb-1 font-weight-bold text-primary">{{ $progressStats['completion_percentage'] }}%</h2>
                                <p class="text-muted mb-0">Overall Completion</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 bg-light rounded">
                                <h2 class="mb-1 font-weight-bold text-success">{{ $progressStats['completed_content'] }}</h2>
                                <p class="text-muted mb-0">Content Completed</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 bg-light rounded">
                                <h2 class="mb-1 font-weight-bold text-warning">{{ $progressStats['total_content'] - $progressStats['completed_content'] }}</h2>
                                <p class="text-muted mb-0">Remaining</p>
                            </div>
                        </div>
                    </div>

                    <div class="progress mt-3" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $progressStats['completion_percentage'] }}%"
                             aria-valuenow="{{ $progressStats['completion_percentage'] }}" 
                             aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold mb-4">
                        <i class="fa fa-clock-o text-secondary mr-2"></i>Recent Content Progress
                    </h5>

                    @php
                        // Get recent content progress with proper relationship
                        $recentProgress = $learner->contentProgress()
                            ->with(['weekContent.moduleWeek'])
                            ->latest()
                            ->take(10)
                            ->get();
                    @endphp

                    @if($recentProgress->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Content</th>
                                    <th>Week</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProgress as $progress)
                                <tr>
                                    <td>
                                        <div class="font-weight-medium">{{ $progress->weekContent->title }}</div>
                                        <small class="text-muted">{{ $progress->weekContent->type_display }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $progress->weekContent->moduleWeek->title }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($progress->is_completed)
                                            <span class="badge badge-success">
                                                <i class="fa fa-check-circle mr-1"></i>Completed
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                <i class="fa fa-hourglass-half mr-1"></i>In Progress
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 6px; width: 80px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $progress->progress_percentage ?? 0 }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $progress->progress_percentage ?? 0 }}%</small>
                                        </div>
                                    </td>
                                    <td class="text-muted small">{{ $progress->updated_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle mr-2"></i>
                        No learning activity yet
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @else
    <!-- Not Enrolled -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fa fa-inbox text-muted" style="font-size: 48px;"></i>
                    <h5 class="mt-3 mb-2">Not Enrolled</h5>
                    <p class="text-muted">This learner has not enrolled in any program yet.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection