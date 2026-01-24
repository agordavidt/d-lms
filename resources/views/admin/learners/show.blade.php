@extends('layouts.admin')

@section('title', 'Learner Details')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <a href="{{ route('admin.learners.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Learners
    </a>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $learner->avatar_url }}" class="rounded" style="width: 80px; height: 80px;" alt="{{ $learner->name }}">
                                <div>
                                    <h3 class="mb-1 fw-bold">{{ $learner->name }}</h3>
                                    <p class="text-muted mb-1">{{ $learner->email }}</p>
                                    <p class="text-muted mb-0">{{ $learner->phone ?? 'No phone number' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="mb-2">
                                @if($learner->status === 'active')
                                    <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Active</span>
                                @elseif($learner->status === 'suspended')
                                    <span class="badge bg-danger px-3 py-2"><i class="bi bi-ban me-1"></i> Suspended</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2"><i class="bi bi-dash-circle me-1"></i> Inactive</span>
                                @endif
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
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="bi bi-book text-primary me-2"></i>Enrollment Information
                    </h5>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted text-uppercase small fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Program</label>
                        <p class="mb-0 fw-semibold">{{ $enrollment->program->name }}</p>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted text-uppercase small fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Cohort</label>
                        <p class="mb-0">{{ $enrollment->cohort->name }}</p>
                        <small class="text-muted">{{ $enrollment->cohort->start_date->format('M d, Y') }} - {{ $enrollment->cohort->end_date->format('M d, Y') }}</small>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted text-uppercase small fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Mentor</label>
                        <p class="mb-0">{{ $enrollment->cohort->mentor ? $enrollment->cohort->mentor->name : 'Not Assigned' }}</p>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted text-uppercase small fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Enrollment Status</label>
                        <div>
                            @if($enrollment->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($enrollment->status === 'pending')
                                <span class="badge bg-warning">Pending Payment</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($enrollment->status) }}</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="text-muted text-uppercase small fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Enrolled On</label>
                        <p class="mb-0">{{ $enrollment->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="bi bi-credit-card text-success me-2"></i>Payment Information
                    </h5>

                    @if($enrollment->payments->count() > 0)
                        @foreach($enrollment->payments as $payment)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <label class="text-muted text-uppercase small fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                        @if($payment->installment_number)
                                            Installment {{ $payment->installment_number }}
                                        @else
                                            Full Payment
                                        @endif
                                    </label>
                                    <p class="mb-0 h5 fw-bold text-dark">₦{{ number_format($payment->final_amount, 2) }}</p>
                                </div>
                                <div>
                                    @if($payment->status === 'successful')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($payment->discount_amount > 0)
                            <p class="mb-1 small text-muted">
                                <i class="bi bi-tag-fill me-1"></i>
                                Discount Applied: ₦{{ number_format($payment->discount_amount, 2) }}
                            </p>
                            @endif
                            
                            <p class="mb-1 small text-muted">
                                <i class="bi bi-calendar-check me-1"></i>
                                {{ $payment->paid_at ? $payment->paid_at->format('M d, Y @ h:i A') : 'Not paid yet' }}
                            </p>
                            
                            <p class="mb-0 small text-muted">
                                <i class="bi bi-hash me-1"></i>
                                Ref: {{ $payment->reference }}
                            </p>
                        </div>
                        @endforeach

                        @if($enrollment->payments->where('installment_number', 1)->where('status', 'successful')->count() > 0 && 
                            $enrollment->payments->where('installment_number', 2)->count() === 0)
                        <div class="alert alert-info border-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Second installment pending
                        </div>
                        @endif
                    @else
                        <div class="alert alert-warning border-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
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
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="bi bi-graph-up text-info me-2"></i>Learning Progress
                    </h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 bg-light rounded">
                                <h2 class="mb-1 fw-bold text-primary">{{ $progressStats['completion_percentage'] }}%</h2>
                                <p class="text-muted mb-0">Overall Completion</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 bg-light rounded">
                                <h2 class="mb-1 fw-bold text-success">{{ $progressStats['completed_content'] }}</h2>
                                <p class="text-muted mb-0">Content Completed</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 bg-light rounded">
                                <h2 class="mb-1 fw-bold text-warning">{{ $progressStats['total_content'] - $progressStats['completed_content'] }}</h2>
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
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="bi bi-clock-history text-secondary me-2"></i>Recent Content Progress
                    </h5>

                    @if($learner->contentProgress->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Content</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($learner->contentProgress()->latest()->take(10)->get() as $progress)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $progress->content->title }}</div>
                                        <small class="text-muted">{{ $progress->content->week->title }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($progress->content->type) }}</span>
                                    </td>
                                    <td>
                                        @if($progress->status === 'completed')
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completed</span>
                                        @elseif($progress->status === 'in_progress')
                                            <span class="badge bg-warning"><i class="bi bi-hourglass-split me-1"></i>In Progress</span>
                                        @else
                                            <span class="badge bg-secondary">Not Started</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $progress->updated_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info border-0">
                        <i class="bi bi-info-circle me-2"></i>
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
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                    <h5 class="mt-3 mb-2">Not Enrolled</h5>
                    <p class="text-muted">This learner has not enrolled in any program yet.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection