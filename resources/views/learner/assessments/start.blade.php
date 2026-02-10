@extends('layouts.admin')

@section('title', 'Assessment')

@section('content')
<div class="container" style="max-width: 700px; margin: 20px auto; padding: 0 20px;">
    
    <div class="card">
        <div class="card-body" style="padding: 40px;">
            
            <h3 class="mb-2">{{ $assessment->title }}</h3>
            <p class="text-muted mb-4">{{ $week->title }} • Week {{ $week->week_number }}</p>

            <table class="table table-borderless mb-4">
                <tr>
                    <td width="40%">Questions</td>
                    <td><strong>{{ $assessment->questions->count() }}</strong></td>
                </tr>
                <tr>
                    <td>Total Points</td>
                    <td><strong>{{ $assessment->total_points }}</strong></td>
                </tr>
                @if($assessment->time_limit_minutes)
                <tr>
                    <td>Time Limit</td>
                    <td><strong>{{ $assessment->time_limit_minutes }} minutes</strong></td>
                </tr>
                @endif
                <tr>
                    <td>Attempts</td>
                    <td><strong>{{ $attemptsUsed }}/{{ $assessment->max_attempts }}</strong></td>
                </tr>
                @if($bestScore !== null)
                <tr>
                    <td>Your Best Score</td>
                    <td><strong class="text-success">{{ number_format($bestScore, 1) }}%</strong></td>
                </tr>
                @endif
            </table>

            @if($assessment->description)
            <div class="mb-4 pb-4 border-bottom">
                <p class="mb-0">{{ $assessment->description }}</p>
            </div>
            @endif

            @if($inProgressAttempt)
                <div class="alert alert-info mb-4">
                    You have an assessment in progress. Click below to continue.
                </div>
            @endif

            @if($attemptsUsed >= $assessment->max_attempts)
                <div class="alert alert-warning mb-4">
                    You have used all attempts. Your score of {{ number_format($bestScore, 1) }}% has been recorded.
                </div>
            @endif

            <div class="text-center">
                @if($inProgressAttempt)
                    <a href="{{ route('learner.attempts.show', $inProgressAttempt->id) }}" 
                       class="btn btn-primary btn-lg">
                        Continue Assessment
                    </a>
                @elseif($attemptsUsed < $assessment->max_attempts)
                    <button type="button" class="btn btn-primary btn-lg" onclick="startAssessment()">
                        @if($attemptsUsed > 0) Retake Assessment @else Begin Assessment @endif
                    </button>
                @endif

                <div class="mt-3">
                    <a href="{{ route('learner.learning.index') }}" class="btn btn-outline-secondary">
                        Back to Learning
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function startAssessment() {
    fetch('{{ route("learner.assessments.attempt", $assessment->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            toastr.error(data.message || 'Failed to start assessment');
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
    });
}
</script>
@endpush