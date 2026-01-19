@extends('layouts.admin')

@section('title', 'Program Completed')
@section('breadcrumb-parent', 'Learning')
@section('breadcrumb-current', 'Completed')

@push('styles')
<style>
.celebration {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 2rem;
    border-radius: 8px;
    text-align: center;
}
.stat-box {
    background-color: white;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.stat-box h2 {
    color: #7571f9;
    margin-bottom: 0.5rem;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Celebration Header -->
        <div class="celebration mb-4">
            <div style="font-size: 80px; margin-bottom: 1rem;">🎉</div>
            <h1 class="mb-3">Congratulations!</h1>
            <p class="lead mb-0">You've successfully completed {{ $enrollment->program->name }}</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Completion Stats -->
    <div class="col-md-4 mb-3">
        <div class="stat-box">
            <h2>{{ $enrollment->program->total_weeks }}</h2>
            <p class="text-muted mb-0">Weeks Completed</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-box">
            <h2>{{ \App\Models\WeekProgress::where('enrollment_id', $enrollment->id)->where('is_completed', true)->count() }}</h2>
            <p class="text-muted mb-0">Modules Mastered</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-box">
            <h2>{{ \App\Models\LiveSession::whereIn('cohort_id', [$enrollment->cohort_id])->where('status', 'completed')->whereJsonContains('attendees', auth()->id())->count() }}</h2>
            <p class="text-muted mb-0">Sessions Attended</p>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-8 mx-auto">
        <!-- Certificate Section -->
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size: 64px; margin-bottom: 1rem;">📜</div>
                <h4 class="mb-3">Your Certificate of Completion</h4>
                <p class="text-muted mb-4">Download your certificate to showcase your achievement</p>
                <button class="btn btn-primary btn-lg" onclick="alert('Certificate download feature coming soon!')">
                    Download Certificate
                </button>
            </div>
        </div>

        <!-- Program Details -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-3">Program Summary</h5>
                <div class="mb-2">
                    <strong>Program:</strong> {{ $enrollment->program->name }}
                </div>
                <div class="mb-2">
                    <strong>Cohort:</strong> {{ $enrollment->cohort->name }}
                </div>
                <div class="mb-2">
                    <strong>Started:</strong> {{ $enrollment->enrolled_at->format('M d, Y') }}
                </div>
                <div class="mb-2">
                    <strong>Completed:</strong> {{ now()->format('M d, Y') }}
                </div>
                <div class="mb-0">
                    <strong>Duration:</strong> {{ $enrollment->program->duration }}
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-3">What's Next?</h5>
                <p class="mb-3">Continue your learning journey with another program</p>
                <a href="{{ route('learner.programs.index') }}" class="btn btn-primary btn-block">
                    Browse More Programs
                </a>
                <a href="{{ route('learner.dashboard') }}" class="btn btn-outline-primary btn-block">
                    Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection