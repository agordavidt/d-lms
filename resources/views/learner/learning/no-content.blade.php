@extends('layouts.admin')

@section('title', 'No Content Available')
@section('breadcrumb-parent', 'Learning')
@section('breadcrumb-current', 'No Content')

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size: 80px; margin-bottom: 2rem;">📚</div>
                <h3 class="mb-3">No Content Available</h3>
                <p class="text-muted mb-4">
                    We're still preparing the learning materials for this program. 
                    Our team is working hard to get everything ready for you.
                </p>

                <div class="mb-4">
                    <h5 class="mb-2">{{ $enrollment->program->name }}</h5>
                    <p class="text-muted mb-0">{{ $enrollment->cohort->name }}</p>
                </div>

                <div class="alert alert-info">
                    <strong>What can you do?</strong><br>
                    Check back soon, or contact our support team for more information about when content will be available.
                </div>

                <div class="mt-4">
                    <a href="{{ route('learner.dashboard') }}" class="btn btn-primary">
                        Go to Dashboard
                    </a>
                    <a href="{{ route('learner.calendar') }}" class="btn btn-outline-primary">
                        View Live Sessions
                    </a>
                </div>

                <div class="mt-4">
                    <small class="text-muted">
                        Need help? Contact us at support@gluper.com
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection