@extends('layouts.admin')

@section('title', 'Review Graduation Request')
@section('breadcrumb-parent', 'Graduations')
@section('breadcrumb-current', 'Review')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Learner Information -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Graduation Review</h4>
                <a href="{{ route('admin.graduations.index') }}" class="btn btn-secondary btn-sm">
                    Back to Queue
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Learner Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>Name:</strong></td>
                                <td>{{ $enrollment->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $enrollment->user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Program:</strong></td>
                                <td>{{ $enrollment->program->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cohort:</strong></td>
                                <td>{{ $enrollment->cohort->name }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Timeline</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>Enrolled:</strong></td>
                                <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Duration:</strong></td>
                                <td>{{ $enrollment->enrolled_at->diffInDays(now()) }} days</td>
                            </tr>
                            @if($enrollment->graduation_requested_at)
                            <tr>
                                <td><strong>Requested:</strong></td>
                                <td>{{ $enrollment->graduation_requested_at->format('M d, Y') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Graduation Checklist -->
                <h6 class="text-muted mb-3">Graduation Requirements</h6>
                <div class="list-group mb-4">
                    <div class="list-group-item {{ $eligibility['all_content_complete'] ? 'border-success' : 'border-warning' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Complete All Course Content</strong>
                                <br><small class="text-muted">{{ $completedWeeks }} of {{ $totalWeeks }} weeks completed</small>
                            </div>
                            @if($eligibility['all_content_complete'])
                                <span class="badge badge-success">Complete</span>
                            @else
                                <span class="badge badge-warning">Incomplete</span>
                            @endif
                        </div>
                    </div>

                    <div class="list-group-item {{ $eligibility['all_assessments_taken'] ? 'border-success' : 'border-warning' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Complete All Assessments</strong>
                                <br><small class="text-muted">{{ $assessmentBreakdown->count() }} assessments taken</small>
                            </div>
                            @if($eligibility['all_assessments_taken'])
                                <span class="badge badge-success">Complete</span>
                            @else
                                <span class="badge badge-warning">Incomplete</span>
                            @endif
                        </div>
                    </div>

                    <div class="list-group-item {{ $eligibility['meets_grade_requirement'] ? 'border-success' : 'border-warning' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Achieve Minimum Grade Average</strong>
                                <br><small class="text-muted">
                                    Required: {{ $enrollment->program->min_passing_average ?? 70 }}% • 
                                    Current: {{ $enrollment->final_grade_avg ? number_format($enrollment->final_grade_avg, 1) . '%' : 'N/A' }}
                                </small>
                            </div>
                            @if($eligibility['meets_grade_requirement'])
                                <span class="badge badge-success">Meets Requirement</span>
                            @else
                                <span class="badge badge-warning">Below Requirement</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Assessment Breakdown -->
                @if($assessmentBreakdown->count() > 0)
                <h6 class="text-muted mb-3">Assessment Scores</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Week</th>
                                <th>Score</th>
                                <th>Attempts</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessmentBreakdown as $weekProgress)
                            <tr>
                                <td>{{ $weekProgress->moduleWeek->title }}</td>
                                <td>
                                    <strong>{{ number_format($weekProgress->assessment_score, 1) }}%</strong>
                                </td>
                                <td>{{ $weekProgress->assessment_attempts }}</td>
                                <td>
                                    @if($weekProgress->assessment_passed)
                                        <span class="badge badge-success">Passed</span>
                                    @else
                                        <span class="badge badge-secondary">Recorded</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="border-top pt-4">
                    <form action="{{ route('admin.graduations.approve', $enrollment->id) }}" method="POST" 
                          id="approveForm" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg" 
                                @if(!$enrollment->isEligibleForGraduation()) disabled @endif>
                            Approve Graduation
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger btn-lg ml-2" 
                            data-toggle="modal" data-target="#rejectModal">
                        Reject Request
                    </button>

                    @if(!$enrollment->isEligibleForGraduation())
                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>Note:</strong> This learner does not meet all graduation requirements. 
                        Please review the checklist above before approving.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Final Grade Display -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Final Grade</h4>
            </div>
            <div class="card-body text-center">
                @if($enrollment->final_grade_avg)
                <div style="margin: 20px 0;">
                    <div style="width: 120px; height: 120px; margin: 0 auto; 
                                border-radius: 50%; display: flex; align-items: center; 
                                justify-content: center; flex-direction: column; 
                                border: 6px solid {{ $eligibility['meets_grade_requirement'] ? '#28a745' : '#ffc107' }}; 
                                background: {{ $eligibility['meets_grade_requirement'] ? '#f1f8f4' : '#fff9e6' }};">
                        <div style="font-size: 36px; font-weight: 700; 
                                    color: {{ $eligibility['meets_grade_requirement'] ? '#2e7d32' : '#f57c00' }};">
                            {{ number_format($enrollment->final_grade_avg, 1) }}%
                        </div>
                        <small style="color: #666;">Final Grade</small>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    @if($eligibility['meets_grade_requirement'])
                        <span class="text-success">Meets Requirement</span>
                    @else
                        Minimum: {{ $enrollment->program->min_passing_average ?? 70 }}%
                    @endif
                </p>
                @else
                <p class="text-muted">No grade available</p>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Completion Stats</h4>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Weeks Completed</span>
                        <h4 class="mb-0">{{ $completedWeeks }}/{{ $totalWeeks }}</h4>
                    </div>
                </div>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Assessments Taken</span>
                        <h4 class="mb-0">{{ $assessmentBreakdown->count() }}</h4>
                    </div>
                </div>
                @if($enrollment->weekly_assessment_avg)
                <div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Assessment Average</span>
                        <h4 class="mb-0">{{ number_format($enrollment->weekly_assessment_avg, 1) }}%</h4>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Quick Actions</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.learners.show', $enrollment->user_id) }}" 
                   class="btn btn-secondary btn-block mb-2" target="_blank">
                    View Full Profile
                </a>
                <a href="{{ route('admin.graduations.index') }}" 
                   class="btn btn-light btn-block">
                    Back to Queue
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.graduations.reject', $enrollment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Graduation Request</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting this graduation request. 
                       The learner will be notified with this message.</p>
                    
                    <div class="form-group">
                        <label>Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="4" 
                                  placeholder="Explain why the graduation request is being rejected..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('approveForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to approve this graduation? The learner will be notified and a certificate will be generated.')) {
        e.preventDefault();
    }
});
</script>
@endpush