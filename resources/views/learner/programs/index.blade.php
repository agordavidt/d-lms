@extends('layouts.admin')

@section('title', 'Browse Programs')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Programs')

@push('styles')
<style>
.program-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
    height: 100%;
}
.program-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-color: #7571f9;
}
.program-image {
    height: 200px;
    background-size: cover;
    background-position: center;
    background-color: #f0f4ff;
}
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body text-center py-5">
                <h2 class="mb-3">Choose Your Learning Path</h2>
                <p class="lead mb-0">Explore our programs and start your journey to success</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @forelse($programs as $program)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card program-card">
            <div class="program-image" style="background-image: url('{{ $program->image_url }}');"></div>
            <div class="card-body">
                <h5 class="mb-2">{{ $program->name }}</h5>
                <p class="text-muted mb-3" style="font-size: 14px;">
                    {{ Str::limit($program->description, 100) }}
                </p>

                <div class="mb-3">
                    <span class="badge badge-light mr-1">⏱️ {{ $program->duration }}</span>
                    <span class="badge badge-light mr-1">👥 {{ $program->enrollments_count }} enrolled</span>
                    @if($program->cohorts->count() > 0)
                        <span class="badge badge-success">{{ $program->cohorts->count() }} cohorts available</span>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        @if($program->discount_percentage > 0)
                            <del class="text-muted">₦{{ number_format($program->price, 2) }}</del>
                            <h4 class="mb-0" style="color: #7571f9;">₦{{ number_format($program->discounted_price, 2) }}</h4>
                        @else
                            <h4 class="mb-0" style="color: #7571f9;">₦{{ number_format($program->price, 2) }}</h4>
                        @endif
                    </div>
                    @if($program->discount_percentage > 0)
                        <span class="badge badge-warning">{{ $program->discount_percentage }}% OFF</span>
                    @endif
                </div>

                <a href="{{ route('learner.programs.show', $program->slug) }}" class="btn btn-primary btn-block">
                    View Details & Enroll
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size: 64px; margin-bottom: 2rem;">📚</div>
                <h4 class="mb-3">No Programs Available</h4>
                <p class="text-muted mb-4">Check back soon for new programs!</p>
                <a href="{{ route('learner.dashboard') }}" class="btn btn-primary">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($programs->hasPages())
<div class="row">
    <div class="col-12">
        {{ $programs->links() }}
    </div>
</div>
@endif
@endsection