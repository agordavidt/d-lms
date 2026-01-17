@extends('layouts.admin')

@section('title', 'My Students')
@section('breadcrumb-parent', 'Students')
@section('breadcrumb-current', 'All Students')

@section('content')

<!-- Filter Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('mentor.students.index') }}" method="GET" class="row align-items-end">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="font-weight-semibold mb-2 small">Filter by Program</label>
                        <select name="program_id" class="form-control">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="font-weight-semibold mb-2 small">Filter by Cohort</label>
                        <select name="cohort_id" class="form-control">
                            <option value="">All Cohorts</option>
                            @foreach($cohorts as $cohort)
                            <option value="{{ $cohort->id }}" {{ request('cohort_id') == $cohort->id ? 'selected' : '' }}>
                                {{ $cohort->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-magnifier mr-1"></i>Filter
                            </button>
                            <a href="{{ route('mentor.students.index') }}" class="btn btn-outline-secondary">
                                <i class="icon-refresh mr-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Students List -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="icon-people text-primary mr-2"></i>My Students
                        <span class="badge badge-primary ml-2">{{ $students->total() }}</span>
                    </h5>
                    <div>
                        <form action="{{ route('mentor.students.index') }}" method="GET" class="d-inline-block">
                            <input type="hidden" name="program_id" value="{{ request('program_id') }}">
                            <input type="hidden" name="cohort_id" value="{{ request('cohort_id') }}">
                            <div class="input-group">
                                <input type="text" 
                                    name="search" 
                                    class="form-control" 
                                    placeholder="Search students..."
                                    value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="icon-magnifier"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                @forelse($students as $enrollment)
                <div class="border-bottom p-4 hover-bg-light">
                    <div class="row align-items-center">
                        <div class="col-lg-5">
                            <div class="d-flex align-items-center">
                                <img src="{{ $enrollment->user->avatar_url }}" 
                                    class="rounded-circle mr-3" 
                                    width="50" 
                                    height="50" 
                                    alt="{{ $enrollment->user->name }}">
                                <div>
                                    <h6 class="mb-1 font-weight-bold">{{ $enrollment->user->name }}</h6>
                                    <p class="text-muted small mb-0">
                                        <i class="icon-envelope mr-1"></i>{{ $enrollment->user->email }}
                                    </p>
                                    @if($enrollment->user->phone)
                                    <p class="text-muted small mb-0">
                                        <i class="icon-phone mr-1"></i>{{ $enrollment->user->phone }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 mt-3 mt-lg-0">
                            <p class="mb-1 small text-muted">Program</p>
                            <p class="mb-1 font-weight-semibold">{{ $enrollment->program->name }}</p>
                            <p class="mb-0 small text-muted">{{ $enrollment->cohort->name }}</p>
                        </div>

                        <div class="col-lg-2 mt-3 mt-lg-0">
                            <p class="mb-1 small text-muted">Progress</p>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 mr-2" style="height: 8px;">
                                    <div class="progress-bar bg-primary" 
                                        style="width: {{ $enrollment->progress_percentage }}%">
                                    </div>
                                </div>
                                <span class="font-weight-bold small">{{ round($enrollment->progress_percentage) }}%</span>
                            </div>
                            <p class="mb-0 small text-muted mt-1">
                                Enrolled: {{ $enrollment->enrolled_at->format('M d, Y') }}
                            </p>
                        </div>

                        <div class="col-lg-2 text-lg-right mt-3 mt-lg-0">
                            <a href="{{ route('mentor.students.show', $enrollment->user_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="icon-eye mr-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="icon-people display-4 text-muted mb-3"></i>
                    <h6 class="text-muted">No students found</h6>
                    @if(request()->filled('search') || request()->filled('program_id') || request()->filled('cohort_id'))
                    <p class="text-muted small mb-3">Try adjusting your filters</p>
                    <a href="{{ route('mentor.students.index') }}" class="btn btn-sm btn-outline-primary">
                        Clear Filters
                    </a>
                    @endif
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center">
                    {{ $students->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Stats Summary -->
<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="icon-people text-primary display-4 mb-2"></i>
                <h3 class="font-weight-bold mb-0">{{ $students->total() }}</h3>
                <p class="text-muted small mb-0">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="icon-book-open text-success display-4 mb-2"></i>
                <h3 class="font-weight-bold mb-0">{{ $programs->count() }}</h3>
                <p class="text-muted small mb-0">Programs Teaching</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="icon-layers text-info display-4 mb-2"></i>
                <h3 class="font-weight-bold mb-0">{{ $cohorts->count() }}</h3>
                <p class="text-muted small mb-0">Active Cohorts</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-bg-light:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
</style>
@endpush

