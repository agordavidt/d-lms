@extends('layouts.admin')

@section('title', 'Graduation Queue')
@section('breadcrumb-parent', 'Learner Management')
@section('breadcrumb-current', 'Graduations')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Pending Review</h6>
                                <h2 class="mb-0 text-warning">{{ $stats['pending_count'] }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Graduated This Month</h6>
                                <h2 class="mb-0 text-success">{{ $stats['graduated_this_month'] }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Average Final Grade</h6>
                                <h2 class="mb-0 text-primary">{{ $stats['avg_grade'] ? number_format($stats['avg_grade'], 1) . '%' : 'N/A' }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Pending Graduation Requests</h4>
                    <div>
                        <a href="{{ route('admin.graduations.graduated') }}" class="btn btn-secondary">
                            View Graduates
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-control" id="filterProgram" onchange="filterGraduations()">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filterCohort" onchange="filterGraduations()">
                            <option value="">All Cohorts</option>
                            @foreach($cohorts as $cohort)
                                <option value="{{ $cohort->id }}" {{ request('cohort_id') == $cohort->id ? 'selected' : '' }}>
                                    {{ $cohort->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Graduations Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <th>Learner</th>
                                <th>Program</th>
                                <th>Cohort</th>
                                <th>Final Grade</th>
                                <th>Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingGraduations as $enrollment)
                            <tr>
                                <td>
                                    <input type="checkbox" class="graduation-checkbox" value="{{ $enrollment->id }}">
                                </td>
                                <td>
                                    <strong>{{ $enrollment->user->name }}</strong>
                                    <br><small class="text-muted">{{ $enrollment->user->email }}</small>
                                </td>
                                <td>{{ $enrollment->program->name }}</td>
                                <td>{{ $enrollment->cohort->name }}</td>
                                <td>
                                    @if($enrollment->final_grade_avg)
                                        <span class="badge badge-{{ $enrollment->final_grade_avg >= ($enrollment->program->min_passing_average ?? 70) ? 'success' : 'warning' }}">
                                            {{ number_format($enrollment->final_grade_avg, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($enrollment->graduation_requested_at)
                                        {{ $enrollment->graduation_requested_at->format('M d, Y') }}
                                        <br><small class="text-muted">{{ $enrollment->graduation_requested_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Auto-detected</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.graduations.review', $enrollment->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        Review
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="quickApprove({{ $enrollment->id }})">
                                        Quick Approve
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No pending graduation requests.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Bulk Actions -->
                @if($pendingGraduations->count() > 0)
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-success" onclick="bulkApprove()" id="bulkApproveBtn" disabled>
                            Approve Selected
                        </button>
                        <span class="ml-2 text-muted" id="selectedCount">0 selected</span>
                    </div>
                </div>
                @endif

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $pendingGraduations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterGraduations() {
    const programId = document.getElementById('filterProgram').value;
    const cohortId = document.getElementById('filterCohort').value;
    
    const params = new URLSearchParams();
    if (programId) params.append('program_id', programId);
    if (cohortId) params.append('cohort_id', cohortId);
    
    window.location.href = '{{ route("admin.graduations.index") }}?' + params.toString();
}

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.graduation-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.graduation-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
});

function updateBulkActions() {
    const checked = document.querySelectorAll('.graduation-checkbox:checked');
    const count = checked.length;
    document.getElementById('selectedCount').textContent = `${count} selected`;
    document.getElementById('bulkApproveBtn').disabled = count === 0;
}

function quickApprove(enrollmentId) {
    if (confirm('Are you sure you want to approve this graduation? The learner will be notified and a certificate will be generated.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/graduations/${enrollmentId}/approve`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkApprove() {
    const checked = document.querySelectorAll('.graduation-checkbox:checked');
    if (checked.length === 0) {
        toastr.warning('Please select at least one graduation to approve.');
        return;
    }
    
    if (confirm(`Are you sure you want to approve ${checked.length} graduation(s)?`)) {
        const enrollmentIds = Array.from(checked).map(cb => cb.value);
        
        fetch('{{ route("admin.graduations.bulk-approve") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ enrollment_ids: enrollmentIds })
        })
        .then(response => response.json())
        .then(data => {
            toastr.success('Graduations approved successfully!');
            setTimeout(() => location.reload(), 1500);
        })
        .catch(error => {
            toastr.error('Failed to approve graduations. Please try again.');
        });
    }
}
</script>
@endpush