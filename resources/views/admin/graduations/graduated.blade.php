@extends('layouts.admin')

@section('title', 'Graduated Learners')
@section('breadcrumb-parent', 'Graduations')
@section('breadcrumb-current', 'Graduates')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Graduated Learners</h4>
                    <a href="{{ route('admin.graduations.index') }}" class="btn btn-primary">
                        Pending Requests
                    </a>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filterProgram" onchange="filterGraduates()">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterCohort" onchange="filterGraduates()">
                            <option value="">All Cohorts</option>
                            @foreach($cohorts as $cohort)
                                <option value="{{ $cohort->id }}" {{ request('cohort_id') == $cohort->id ? 'selected' : '' }}>
                                    {{ $cohort->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterMonth" onchange="filterGraduates()">
                            <option value="">All Time</option>
                            <option value="1" {{ request('month') == 1 ? 'selected' : '' }}>January</option>
                            <option value="2" {{ request('month') == 2 ? 'selected' : '' }}>February</option>
                            <option value="3" {{ request('month') == 3 ? 'selected' : '' }}>March</option>
                            <option value="4" {{ request('month') == 4 ? 'selected' : '' }}>April</option>
                            <option value="5" {{ request('month') == 5 ? 'selected' : '' }}>May</option>
                            <option value="6" {{ request('month') == 6 ? 'selected' : '' }}>June</option>
                            <option value="7" {{ request('month') == 7 ? 'selected' : '' }}>July</option>
                            <option value="8" {{ request('month') == 8 ? 'selected' : '' }}>August</option>
                            <option value="9" {{ request('month') == 9 ? 'selected' : '' }}>September</option>
                            <option value="10" {{ request('month') == 10 ? 'selected' : '' }}>October</option>
                            <option value="11" {{ request('month') == 11 ? 'selected' : '' }}>November</option>
                            <option value="12" {{ request('month') == 12 ? 'selected' : '' }}>December</option>
                        </select>
                    </div>
                </div>

                <!-- Graduates Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Learner</th>
                                <th>Program</th>
                                <th>Cohort</th>
                                <th>Final Grade</th>
                                <th>Graduated</th>
                                <th>Approved By</th>
                                <th>Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($graduates as $enrollment)
                            <tr>
                                <td>
                                    <strong>{{ $enrollment->user->name }}</strong>
                                    <br><small class="text-muted">{{ $enrollment->user->email }}</small>
                                </td>
                                <td>{{ $enrollment->program->name }}</td>
                                <td>{{ $enrollment->cohort->name }}</td>
                                <td>
                                    @if($enrollment->final_grade_avg)
                                        <span class="badge badge-success">
                                            {{ number_format($enrollment->final_grade_avg, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $enrollment->graduation_approved_at->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $enrollment->graduation_approved_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($enrollment->approver)
                                        {{ $enrollment->approver->name }}
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    @if($enrollment->certificate_key)
                                        <a href="{{ route('certificate.verify', $enrollment->certificate_key) }}" 
                                           target="_blank" class="btn btn-sm btn-secondary">
                                            View Certificate
                                        </a>
                                    @else
                                        <span class="text-muted">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No graduates found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $graduates->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterGraduates() {
    const programId = document.getElementById('filterProgram').value;
    const cohortId = document.getElementById('filterCohort').value;
    const month = document.getElementById('filterMonth').value;
    
    const params = new URLSearchParams();
    if (programId) params.append('program_id', programId);
    if (cohortId) params.append('cohort_id', cohortId);
    if (month) params.append('month', month);
    
    window.location.href = '{{ route("admin.graduations.graduated") }}?' + params.toString();
}
</script>
@endpush