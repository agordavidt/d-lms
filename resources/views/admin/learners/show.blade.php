@extends('layouts.admin')

@section('title', 'Learner Details')
@section('breadcrumb-parent', 'Learners')
@section('breadcrumb-current', 'Details')

@push('styles')
<style>
.nav-tabs .nav-link {
    color: #666;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 12px 24px;
}
.nav-tabs .nav-link.active {
    color: #7571f9;
    border-bottom-color: #7571f9;
    background: none;
}
.nav-tabs {
    border-bottom: 1px solid #e0e0e0;
}
.assessment-status-badge {
    font-size: 11px;
    padding: 4px 8px;
}
.score-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
}
.score-excellent { background: #e8f5e9; color: #2e7d32; }
.score-good { background: #e3f2fd; color: #1976d2; }
.score-fair { background: #fff3e0; color: #f57c00; }
.score-poor { background: #ffebee; color: #c62828; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <a href="{{ route('admin.learners.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        Back to Learners
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
                                        <span class="badge badge-success px-3 py-2">Active</span>
                                        @break
                                    @case('suspended')
                                        <span class="badge badge-danger px-3 py-2">Suspended</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary px-3 py-2">Inactive</span>
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
    <!-- Tabs -->
    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#overview">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#assessments">
                        Assessment Performance
                        @if($assessmentStats && $assessmentStats['pending_assessments'] > 0)
                            <span class="badge badge-warning ml-1">{{ $assessmentStats['pending_assessments'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#content-progress">Content Progress</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#payments">Payments</a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold mb-3">Enrollment Information</h5>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Program:</strong></td>
                                    <td>{{ $enrollment->program->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cohort:</strong></td>
                                    <td>{{ $enrollment->cohort->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Start Date:</strong></td>
                                    <td>{{ $enrollment->cohort->start_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>End Date:</strong></td>
                                    <td>{{ $enrollment->cohort->end_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Mentor:</strong></td>
                                    <td>{{ $enrollment->cohort->mentor ? $enrollment->cohort->mentor->name : 'Not Assigned' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
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
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Enrolled On:</strong></td>
                                    <td>{{ $enrollment->created_at->format('F d, Y') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="font-weight-bold mb-3">Performance Summary</h5>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Overall Progress:</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 20px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $progressStats['completion_percentage'] }}%">
                                                    {{ $progressStats['completion_percentage'] }}%
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Content Completed:</strong></td>
                                    <td>{{ $progressStats['completed_content'] }}/{{ $progressStats['total_content'] }}</td>
                                </tr>
                                @if($assessmentStats)
                                <tr>
                                    <td><strong>Final Grade Average:</strong></td>
                                    <td>
                                        <strong class="text-{{ $enrollment->final_grade_avg >= 70 ? 'success' : 'warning' }}">
                                            {{ $enrollment->final_grade_avg ? number_format($enrollment->final_grade_avg, 1) . '%' : 'N/A' }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Assessments Taken:</strong></td>
                                    <td>{{ $assessmentStats['completed_assessments'] }}/{{ $assessmentStats['total_assessments'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Assessments Passed:</strong></td>
                                    <td>
                                        <span class="badge badge-success">{{ $assessmentStats['passed_assessments'] }}</span>
                                        <span class="badge badge-danger ml-1">{{ $assessmentStats['failed_assessments'] }} Failed</span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Assessment Performance Tab -->
                <div class="tab-pane fade" id="assessments">
                    @if($assessmentStats)
                    <!-- Assessment Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="border-left border-primary pl-3">
                                <h6 class="text-muted mb-1">Overall Average</h6>
                                <h3 class="mb-0 font-weight-bold text-primary">
                                    {{ number_format($assessmentStats['average_score'], 1) }}%
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-left border-success pl-3">
                                <h6 class="text-muted mb-1">Highest Score</h6>
                                <h3 class="mb-0 font-weight-bold text-success">
                                    {{ number_format($assessmentStats['highest_score'], 1) }}%
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-left border-warning pl-3">
                                <h6 class="text-muted mb-1">Lowest Score</h6>
                                <h3 class="mb-0 font-weight-bold text-warning">
                                    {{ $assessmentStats['lowest_score'] > 0 ? number_format($assessmentStats['lowest_score'], 1) . '%' : 'N/A' }}
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-left border-info pl-3">
                                <h6 class="text-muted mb-1">Total Attempts</h6>
                                <h3 class="mb-0 font-weight-bold text-info">
                                    {{ $assessmentStats['total_attempts'] }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Assessment Breakdown -->
                    <h5 class="font-weight-bold mb-3">Weekly Assessment Performance</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Week</th>
                                    <th>Module</th>
                                    <th>Assessment</th>
                                    <th>Best Score</th>
                                    <th>Latest Score</th>
                                    <th>Attempts</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($weeklyBreakdown as $item)
                                <tr>
                                    <td><strong>Week {{ $item['week']->week_number }}</strong></td>
                                    <td>{{ $item['module']->title }}</td>
                                    <td>
                                        {{ $item['assessment']->title }}
                                        <br><small class="text-muted">
                                            {{ $item['assessment']->questions->count() }} questions • 
                                            {{ $item['assessment']->total_points }} pts
                                        </small>
                                    </td>
                                    <td>
                                        @if($item['best_score'])
                                            @php
                                                $scoreClass = 'score-poor';
                                                if($item['best_score'] >= 85) $scoreClass = 'score-excellent';
                                                elseif($item['best_score'] >= 70) $scoreClass = 'score-good';
                                                elseif($item['best_score'] >= 50) $scoreClass = 'score-fair';
                                            @endphp
                                            <div class="score-circle {{ $scoreClass }}">
                                                {{ number_format($item['best_score'], 0) }}%
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['latest_score'])
                                            {{ number_format($item['latest_score'], 1) }}%
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $item['attempts_count'] }}/{{ $item['assessment']->max_attempts }}
                                        </span>
                                    </td>
                                    <td>
                                        @switch($item['status'])
                                            @case('not_started')
                                                <span class="badge badge-secondary assessment-status-badge">Not Started</span>
                                                @break
                                            @case('passed')
                                                <span class="badge badge-success assessment-status-badge">Passed</span>
                                                @break
                                            @case('in_progress')
                                                <span class="badge badge-warning assessment-status-badge">In Progress</span>
                                                @break
                                            @case('attempts_exhausted')
                                                <span class="badge badge-danger assessment-status-badge">Max Attempts</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($item['attempts_count'] > 0)
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewAttempts({{ $item['assessment']->id }})">
                                                View Attempts
                                            </button>
                                        @else
                                            <span class="text-muted small">No attempts</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">
                                        No assessments in this program yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Recent Attempts -->
                    @if($recentAttempts->count() > 0)
                    <h5 class="font-weight-bold mt-4 mb-3">Recent Assessment Attempts</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Assessment</th>
                                    <th>Week</th>
                                    <th>Score</th>
                                    <th>Time Spent</th>
                                    <th>Result</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttempts as $attempt)
                                <tr>
                                    <td>
                                        {{ $attempt->submitted_at->format('M d, Y') }}
                                        <br><small class="text-muted">{{ $attempt->submitted_at->format('h:i A') }}</small>
                                    </td>
                                    <td>{{ $attempt->assessment->title }}</td>
                                    <td>Week {{ $attempt->assessment->moduleWeek->week_number }}</td>
                                    <td>
                                        <strong class="text-{{ $attempt->passed ? 'success' : 'danger' }}">
                                            {{ number_format($attempt->percentage, 1) }}%
                                        </strong>
                                    </td>
                                    <td>{{ gmdate('i:s', $attempt->time_spent_seconds) }}</td>
                                    <td>
                                        @if($attempt->passed)
                                            <span class="badge badge-success">Passed</span>
                                        @else
                                            <span class="badge badge-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.learners.assessment-attempt', [$learner->id, $attempt->id]) }}" 
                                           class="btn btn-sm btn-outline-secondary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    @else
                    <div class="alert alert-info">
                        No assessment data available yet.
                    </div>
                    @endif
                </div>

                <!-- Content Progress Tab -->
                <div class="tab-pane fade" id="content-progress">
                    @php
                        $recentProgress = $learner->contentProgress()
                            ->with(['weekContent.moduleWeek'])
                            ->latest()
                            ->take(20)
                            ->get();
                    @endphp

                    @if($recentProgress->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Content</th>
                                    <th>Week</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Time Spent</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProgress as $progress)
                                <tr>
                                    <td>{{ $progress->weekContent->title }}</td>
                                    <td>{{ $progress->weekContent->moduleWeek->title }}</td>
                                    <td><span class="badge badge-light">{{ $progress->weekContent->type_display }}</span></td>
                                    <td>
                                        @if($progress->is_completed)
                                            <span class="badge badge-success">Completed</span>
                                        @else
                                            <span class="badge badge-warning">In Progress</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 6px; width: 100px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $progress->progress_percentage ?? 0 }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $progress->progress_percentage ?? 0 }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($progress->time_spent_seconds > 0)
                                            {{ gmdate('H:i:s', $progress->time_spent_seconds) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $progress->updated_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        No learning activity yet.
                    </div>
                    @endif
                </div>

                <!-- Payments Tab -->
                <div class="tab-pane fade" id="payments">
                    @if($enrollment->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Discount</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollment->payments as $payment)
                                    <tr>
                                        <td>
                                            @if($payment->installment_number)
                                                Installment {{ $payment->installment_number }}
                                            @else
                                                Full Payment
                                            @endif
                                        </td>
                                        <td><strong>₦{{ number_format($payment->final_amount, 2) }}</strong></td>
                                        <td>
                                            @if($payment->discount_amount > 0)
                                                <span class="badge badge-success">₦{{ number_format($payment->discount_amount, 2) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
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
                                        </td>
                                        <td><code>{{ $payment->reference }}</code></td>
                                        <td>
                                            @if($payment->paid_at)
                                                {{ $payment->paid_at->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">Not paid</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            No payment records found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- Not Enrolled -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <h5 class="mt-3 mb-2">Not Enrolled</h5>
                    <p class="text-muted">This learner has not enrolled in any program yet.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Attempts Modal -->
<div class="modal fade" id="attemptsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assessment Attempts</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="attemptsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewAttempts(assessmentId) {
    $('#attemptsModal').modal('show');
    
    // Load attempts via AJAX
    fetch(`/admin/learners/{{ $learner->id }}/assessments/${assessmentId}/attempts`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('attemptsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('attemptsContent').innerHTML = 
                '<div class="alert alert-danger">Failed to load attempts.</div>';
        });
}
</script>
@endpush