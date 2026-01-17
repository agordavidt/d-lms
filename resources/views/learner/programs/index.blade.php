@extends('layouts.admin')

@section('title', 'Browse Programs')
@section('breadcrumb-parent', 'Programs')
@section('breadcrumb-current', 'Browse')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-2 font-weight-bold">Available Programs</h2>
                        <p class="text-muted mb-0">Explore our courses and start your learning journey</p>
                    </div>
                    <div>
                        <a href="{{ route('learner.dashboard') }}" class="btn btn-outline-primary">
                            <i class="icon-home mr-2"></i>My Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @forelse($programs as $program)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition">
            @if($program->image)
            <img src="{{ $program->image_url }}" class="card-img-top" alt="{{ $program->name }}" style="height: 200px; object-fit: cover;">
            @else
            <div class="bg-gradient-primary" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                <h1 class="text-white mb-0">{{ substr($program->name, 0, 1) }}</h1>
            </div>
            @endif
            
            <div class="card-body d-flex flex-column">
                <!-- Enrollment Badge -->
                @if(in_array($program->id, $enrolledProgramIds))
                <span class="badge badge-success mb-3 align-self-start">
                    <i class="icon-check mr-1"></i>Enrolled
                </span>
                @else
                <span class="badge badge-info mb-3 align-self-start">
                    <i class="icon-graduation mr-1"></i>Open for Enrollment
                </span>
                @endif

                <h5 class="card-title font-weight-bold mb-3">{{ $program->name }}</h5>
                <p class="card-text text-muted mb-4">{{ Str::limit($program->description, 100) }}</p>

                <!-- Program Info -->
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="icon-clock text-primary mr-2"></i>
                        <span class="text-muted small">{{ $program->duration }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="icon-people text-primary mr-2"></i>
                        <span class="text-muted small">{{ $program->enrollments_count }} students enrolled</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="icon-layers text-primary mr-2"></i>
                        <span class="text-muted small">{{ $program->cohorts->count() }} active cohorts</span>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="mb-4">
                    <div class="d-flex align-items-baseline">
                        <h4 class="text-primary font-weight-bold mb-0">₦{{ number_format($program->price, 2) }}</h4>
                        @if($program->discount_percentage > 0)
                        <small class="text-success ml-2">({{ $program->discount_percentage }}% off for one-time payment)</small>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-auto">
                    @if(in_array($program->id, $enrolledProgramIds))
                    <a href="{{ route('learner.dashboard') }}" class="btn btn-outline-primary btn-block">
                        View My Progress
                    </a>
                    @else
                    <a href="{{ route('learner.programs.show', $program->slug) }}" class="btn btn-primary btn-block">
                        View Details & Enroll
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="icon-book-open display-4 text-muted mb-3"></i>
                <h5 class="text-muted">No programs available at the moment</h5>
                <p class="text-muted">Check back later for new courses</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-center">
            {{ $programs->links() }}
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-shadow-lg {
        transition: all 0.3s ease;
    }
    .hover-shadow-lg:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
    }
</style>
@endpush