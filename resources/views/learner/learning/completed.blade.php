@extends('layouts.admin')

@section('title', 'Program Status')
@section('breadcrumb-parent', 'Learning')
@section('breadcrumb-current', 'Status')

@section('content')

@php
    $graduationStatus = $enrollment->graduation_status ?? 'active';
    $finalGrade = $enrollment->final_grade_avg;
    $minGrade = $enrollment->program->min_passing_average ?? 70;
    $isEligible = $enrollment->isEligibleForGraduation();
    $allContentComplete = $enrollment->hasCompletedAllContent();
    $allAssessmentsComplete = $enrollment->hasAttemptedAllAssessments();
    $meetsGradeRequirement = $enrollment->meetsMinimumGradeRequirement();
    
    $totalWeeks = \App\Models\ModuleWeek::whereHas('programModule', fn($q) => $q->where('program_id', $enrollment->program_id))->where('status', 'published')->count();
    $completedWeeks = \App\Models\WeekProgress::where('enrollment_id', $enrollment->id)->where('is_completed', true)->count();
    
    $totalAssessments = \App\Models\ModuleWeek::whereHas('programModule', fn($q) => $q->where('program_id', $enrollment->program_id))
        ->where('status', 'published')
        ->where('has_assessment', true)
        ->whereHas('assessment')
        ->count();
    $completedAssessments = \App\Models\WeekProgress::where('enrollment_id', $enrollment->id)
        ->whereIn('module_week_id', \App\Models\ModuleWeek::whereHas('programModule', fn($q) => $q->where('program_id', $enrollment->program_id))
            ->where('has_assessment', true)
            ->whereHas('assessment')
            ->pluck('id'))
        ->where('assessment_attempts', '>', 0)
        ->count();
        
    $sessionsAttended = \App\Models\LiveSession::where('cohort_id', $enrollment->cohort_id)
        ->where('status', 'completed')
        ->whereJsonContains('attendees', auth()->id())
        ->count();
@endphp

<div class="row">
    <div class="col-12">
        <!-- Status Alert -->
        @if($graduationStatus === 'graduated')
            <div class="alert alert-success">
                <h4 class="alert-heading">Congratulations!</h4>
                <p class="mb-0">You have successfully completed {{ $enrollment->program->name }}</p>
                @if($finalGrade)
                <p class="mb-0 mt-2"><strong>Final Grade:</strong> {{ number_format($finalGrade, 1) }}%</p>
                @endif
            </div>
        @elseif($graduationStatus === 'pending_review')
            <div class="alert alert-warning">
                <h4 class="alert-heading">Graduation Under Review</h4>
                <p class="mb-0">Your graduation request is being reviewed. This typically takes 2-3 business days.</p>
                @if($enrollment->graduation_requested_at)
                <p class="mb-0 mt-2"><small>Submitted: {{ $enrollment->graduation_requested_at->format('M d, Y') }}</small></p>
                @endif
            </div>
        @elseif($isEligible)
            <div class="alert alert-info">
                <h4 class="alert-heading">Ready for Graduation</h4>
                <p class="mb-3">You have completed all requirements for {{ $enrollment->program->name }}</p>
                <button type="button" class="btn btn-success" onclick="requestGraduation()">
                    Request Graduation
                </button>
            </div>
        @else
            <div class="alert alert-primary">
                <h4 class="alert-heading">Program In Progress</h4>
                <p class="mb-0">{{ $enrollment->program->name }}</p>
                @if($finalGrade)
                <p class="mb-0 mt-2"><strong>Current Grade:</strong> {{ number_format($finalGrade, 1) }}%</p>
                @endif
            </div>
        @endif
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Graduation Requirements -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Graduation Requirements</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td width="50">
                                @if($allContentComplete)
                                    <span class="badge badge-success">✓</span>
                                @else
                                    <span class="badge badge-secondary">○</span>
                                @endif
                            </td>
                            <td>
                                <strong>Complete All Course Content</strong>
                                <br><small class="text-muted">{{ $completedWeeks }} of {{ $totalWeeks }} weeks completed</small>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @if($allAssessmentsComplete)
                                    <span class="badge badge-success">✓</span>
                                @else
                                    <span class="badge badge-secondary">○</span>
                                @endif
                            </td>
                            <td>
                                <strong>Complete All Assessments</strong>
                                <br><small class="text-muted">{{ $completedAssessments }} of {{ $totalAssessments }} assessments taken</small>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @if($meetsGradeRequirement)
                                    <span class="badge badge-success">✓</span>
                                @else
                                    <span class="badge badge-secondary">○</span>
                                @endif
                            </td>
                            <td>
                                <strong>Achieve Minimum Grade Average</strong>
                                <br><small class="text-muted">
                                    @if($finalGrade)
                                        Current: {{ number_format($finalGrade, 1) }}% • Required: {{ $minGrade }}%
                                    @else
                                        Required: {{ $minGrade }}% average
                                    @endif
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Certificate Section -->
        @if($graduationStatus === 'graduated')
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Certificate</h5>
            </div>
            <div class="card-body text-center">
                <h5 class="mb-3">Certificate of Completion</h5>
                
                @if($enrollment->certificate_key)
                <div class="mb-3">
                    <small class="text-muted">Certificate ID:</small><br>
                    <code>{{ $enrollment->certificate_key }}</code>
                </div>
                @endif

                <button class="btn btn-primary" onclick="alert('Certificate generation coming soon!')">
                    Download Certificate
                </button>
                
                @if($enrollment->certificate_key)
                <div class="mt-3">
                    <a href="{{ route('certificate.verify', $enrollment->certificate_key) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        Verify Certificate
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Actions -->
        @if($graduationStatus === 'graduated')
        <div class="card mt-3">
            <div class="card-body text-center">
                <h5 class="mb-3">Continue Your Learning Journey</h5>
                <a href="{{ route('learner.programs.index') }}" class="btn btn-primary">
                    Browse More Programs
                </a>
            </div>
        </div>
        @elseif(!$isEligible && $graduationStatus !== 'pending_review')
        <div class="card mt-3">
            <div class="card-body text-center">
                <a href="{{ route('learner.learning.index') }}" class="btn btn-primary">
                    Continue Learning
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Progress Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    @if($finalGrade)
                    <tr>
                        <td><strong>Final Grade</strong></td>
                        <td class="text-right">
                            <span class="badge badge-{{ $meetsGradeRequirement ? 'success' : 'warning' }} badge-lg">
                                {{ number_format($finalGrade, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>Weeks Completed</td>
                        <td class="text-right"><strong>{{ $completedWeeks }}</strong> / {{ $totalWeeks }}</td>
                    </tr>
                    <tr>
                        <td>Assessments Taken</td>
                        <td class="text-right"><strong>{{ $completedAssessments }}</strong> / {{ $totalAssessments }}</td>
                    </tr>
                    <tr>
                        <td>Sessions Attended</td>
                        <td class="text-right"><strong>{{ $sessionsAttended }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Program Details -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Program Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted">Program</td>
                        <td class="text-right"><strong>{{ $enrollment->program->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cohort</td>
                        <td class="text-right">{{ $enrollment->cohort->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Started</td>
                        <td class="text-right">{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                    </tr>
                    @if($graduationStatus === 'graduated' && $enrollment->graduation_approved_at)
                    <tr>
                        <td class="text-muted">Graduated</td>
                        <td class="text-right">{{ $enrollment->graduation_approved_at->format('M d, Y') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('learner.curriculum') }}" class="btn btn-outline-primary btn-block btn-sm mb-2">
                    View Curriculum
                </a>
                <a href="{{ route('learner.dashboard') }}" class="btn btn-outline-primary btn-block btn-sm">
                    Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function requestGraduation() {
    if (confirm('Submit your graduation request for review?\n\nYour request will be reviewed by our team within 2-3 business days.')) {
        fetch('{{ route("learner.graduation.request") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Graduation request submitted successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || 'Failed to submit request');
            }
        })
        .catch(error => {
            toastr.error('An error occurred. Please try again.');
        });
    }
}
</script>
@endpush