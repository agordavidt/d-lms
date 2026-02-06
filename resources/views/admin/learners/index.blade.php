@extends('layouts.admin')

@section('title', 'Learner Management')
@section('breadcrumb-parent', 'Users')
@section('breadcrumb-current', 'Learners')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Learner Management</h4>
                    <div class="text-muted">
                        Total: {{ $learners->total() }} learners
                    </div>
                </div>

                <!-- Filters Form -->
                <form method="GET" action="{{ route('admin.learners.index') }}" id="filterForm">
                    <div class="row mb-4">
                        <!-- Search -->
                        <div class="col-md-4">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   name="search" 
                                   placeholder="Search by name or email..." 
                                   value="{{ request('search') }}"
                                   id="searchInput">
                        </div>

                        <!-- Program Filter -->
                        <div class="col-md-4">
                            <select class="form-control form-control-sm" name="program_id" onchange="this.form.submit()">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-4">
                            <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Active Filters & Clear -->
                    @if(request()->hasAny(['program_id', 'status', 'search']))
                    <div class="mb-3">
                        <a href="{{ route('admin.learners.index') }}" class="btn btn-sm btn-outline-secondary">
                            Clear All Filters
                        </a>
                    </div>
                    @endif
                </form>

                <!-- Learners Table -->
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 25%;">Learner</th>
                                <th style="width: 20%;">Contact</th>
                                <th style="width: 20%;">Program</th>
                                <th style="width: 15%;">Cohort</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Joined</th>
                                <th style="width: 5%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($learners as $learner)
                            @php
                                $enrollment = $learner->enrollments->first();
                            @endphp
                            <tr>
                                <!-- Learner Name & Avatar -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $learner->avatar_url }}" 
                                             class="rounded mr-2" 
                                             style="width: 35px; height: 35px; object-fit: cover;" 
                                             alt="{{ $learner->name }}">
                                        <div>
                                            <div class="font-weight-bold">{{ $learner->name }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td>
                                    <div class="small text-muted">{{ $learner->email }}</div>
                                    @if($learner->phone)
                                        <div class="small text-muted">{{ $learner->phone }}</div>
                                    @endif
                                </td>

                                <!-- Program -->
                                <td>
                                    @if($enrollment && $enrollment->program)
                                        <span class="small">{{ Str::limit($enrollment->program->name, 30) }}</span>
                                    @else
                                        <span class="text-muted small">Not Enrolled</span>
                                    @endif
                                </td>

                                <!-- Cohort -->
                                <td>
                                    @if($enrollment && $enrollment->cohort)
                                        <span class="small">{{ $enrollment->cohort->name }}</span>
                                        @if($enrollment->cohort->mentor)
                                            <div class="text-muted small">{{ $enrollment->cohort->mentor->name }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td>
                                    @switch($learner->status)
                                        @case('active')
                                            <span class="badge badge-sm badge-success">Active</span>
                                            @break
                                        @case('suspended')
                                            <span class="badge badge-sm badge-danger">Suspended</span>
                                            @break
                                        @case('inactive')
                                            <span class="badge badge-sm badge-secondary">Inactive</span>
                                            @break
                                    @endswitch
                                    
                                    @if($enrollment && $enrollment->status === 'pending')
                                        <br><span class="badge badge-sm badge-warning mt-1">Payment Pending</span>
                                    @endif
                                </td>

                                <!-- Joined Date -->
                                <td>
                                    <small class="text-muted">{{ $learner->created_at->format('M d, Y') }}</small>
                                </td>

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-light" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.learners.show', $learner->id) }}" class="dropdown-item">
                                                <i class="fa fa-eye"></i> View Details
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item" onclick="changeStatus({{ $learner->id }}, '{{ $learner->status }}')">
                                                <i class="fa fa-refresh"></i> Change Status
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa fa-users fa-3x mb-3"></i>
                                        <p>No learners found</p>
                                        @if(request()->hasAny(['program_id', 'status', 'search']))
                                            <a href="{{ route('admin.learners.index') }}" class="btn btn-sm btn-outline-primary">Clear Filters</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($learners->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $learners->firstItem() }} to {{ $learners->lastItem() }} of {{ $learners->total() }} entries
                    </div>
                    <div>
                        {{ $learners->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Learner Status</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="statusLearnerId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">New Status</label>
                        <select class="form-control" id="newStatus" required>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle mr-2"></i>
                        Changing status will affect the learner's access to the platform.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Debounced search
let searchTimeout;
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
});

// Change Status
function changeStatus(learnerId, currentStatus) {
    $('#statusLearnerId').val(learnerId);
    $('#newStatus').val(currentStatus);
    $('#statusModal').modal('show');
}

$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    
    let learnerId = $('#statusLearnerId').val();
    let newStatus = $('#newStatus').val();
    
    $.ajax({
        url: '/admin/learners/' + learnerId + '/status',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: newStatus
        },
        success: function(response) {
            $('#statusModal').modal('hide');
            toastr.success(response.message);
            setTimeout(() => window.location.reload(), 1000);
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Failed to update status');
        }
    });
});
</script>
@endpush