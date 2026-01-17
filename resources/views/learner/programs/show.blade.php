@extends('layouts.admin')

@section('title', $program->name)
@section('breadcrumb-parent', 'Programs')
@section('breadcrumb-current', $program->name)

@section('content')

<!-- Program Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <a href="{{ route('learner.programs.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="icon-arrow-left mr-1"></i> Back to Programs
                </a>
                
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        @if($enrollment)
                        <span class="badge badge-success mb-3">
                            <i class="icon-check mr-1"></i>You are enrolled
                        </span>
                        @endif
                        <h1 class="h2 font-weight-bold mb-3">{{ $program->name }}</h1>
                        <p class="lead text-muted mb-4">{{ $program->description }}</p>
                        
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <div class="d-flex align-items-center mr-4">
                                <i class="icon-clock text-primary mr-2"></i>
                                <span class="font-weight-semibold">{{ $program->duration }}</span>
                            </div>
                            <div class="d-flex align-items-center mr-4">
                                <i class="icon-people text-primary mr-2"></i>
                                <span class="font-weight-semibold">{{ $program->enrollments_count }} Students</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="icon-layers text-primary mr-2"></i>
                                <span class="font-weight-semibold">{{ $program->cohorts->count() }} Active Cohorts</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 text-lg-right">
                        <div class="card bg-light border-0 p-4">
                            <p class="text-muted mb-2 small text-uppercase font-weight-bold">Program Fee</p>
                            <h2 class="text-primary font-weight-bold mb-3">₦{{ number_format($program->price, 2) }}</h2>
                            
                            @if($program->discount_percentage > 0)
                            <div class="alert alert-success py-2 px-3 mb-3">
                                <small class="font-weight-bold">
                                    <i class="icon-tag mr-1"></i>{{ $program->discount_percentage }}% off for one-time payment
                                </small>
                            </div>
                            @endif
                            
                            @if($enrollment)
                                @if($enrollment->status === 'pending')
                                <div class="alert alert-warning py-2 px-3 mb-2">
                                    <small><strong>Pending:</strong> Complete payment to activate</small>
                                </div>
                                @else
                                <a href="{{ route('learner.dashboard') }}" class="btn btn-outline-primary btn-block">
                                    Go to My Dashboard
                                </a>
                                @endif
                            @else
                            <a href="{{ route('learner.programs.enroll', $program->slug) }}" class="btn btn-primary btn-block btn-lg">
                                <i class="icon-login mr-2"></i>Enroll Now
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Program Details Tabs -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#overview">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#features">What You'll Learn</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#requirements">Requirements</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#cohorts">Available Cohorts</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview">
                        <div class="mb-4">
                            <h4 class="font-weight-bold mb-3">Program Overview</h4>
                            <div class="text-muted">
                                {!! nl2br(e($program->overview ?? $program->description)) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Features Tab -->
                    <div class="tab-pane fade" id="features">
                        <h4 class="font-weight-bold mb-4">What You'll Learn</h4>
                        @if($program->features && count($program->features) > 0)
                        <div class="row">
                            @foreach($program->features as $feature)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="icon-check text-success mr-3 mt-1"></i>
                                    <span>{{ $feature }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted">Features will be updated soon.</p>
                        @endif
                    </div>

                    <!-- Requirements Tab -->
                    <div class="tab-pane fade" id="requirements">
                        <h4 class="font-weight-bold mb-4">Prerequisites</h4>
                        @if($program->requirements && count($program->requirements) > 0)
                        <ul class="list-unstyled">
                            @foreach($program->requirements as $requirement)
                            <li class="mb-3">
                                <i class="icon-info text-primary mr-2"></i>{{ $requirement }}
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-muted">No specific prerequisites required. This program is open to all learners.</p>
                        @endif
                    </div>

                    <!-- Cohorts Tab -->
                    <div class="tab-pane fade" id="cohorts">
                        <h4 class="font-weight-bold mb-4">Available Cohorts</h4>
                        @if($program->cohorts->count() > 0)
                        <div class="row">
                            @foreach($program->cohorts as $cohort)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="font-weight-bold mb-0">{{ $cohort->name }}</h5>
                                            <span class="badge 
                                                @if($cohort->status === 'upcoming') badge-info
                                                @elseif($cohort->status === 'ongoing') badge-success
                                                @else badge-secondary
                                                @endif">
                                                {{ ucfirst($cohort->status) }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-muted small mb-3">Code: {{ $cohort->code }}</p>
                                        
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Start Date:</span>
                                                <span class="font-weight-semibold">{{ $cohort->start_date->format('M d, Y') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">End Date:</span>
                                                <span class="font-weight-semibold">{{ $cohort->end_date->format('M d, Y') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted small">Spots Available:</span>
                                                <span class="font-weight-semibold 
                                                    @if($cohort->spots_remaining < 10) text-danger
                                                    @elseif($cohort->spots_remaining < 20) text-warning
                                                    @else text-success
                                                    @endif">
                                                    {{ $cohort->spots_remaining }} / {{ $cohort->max_students }}
                                                </span>
                                            </div>
                                        </div>

                                        @if($cohort->canEnroll())
                                        <span class="badge badge-success">
                                            <i class="icon-check mr-1"></i>Accepting Enrollments
                                        </span>
                                        @else
                                        <span class="badge badge-secondary">
                                            <i class="icon-lock mr-1"></i>Enrollment Closed
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="icon-info mr-2"></i>
                            No active cohorts at the moment. Check back soon!
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection