@extends('layouts.admin')

@section('title', 'Mentor Management')
@section('breadcrumb-parent', 'Users')
@section('breadcrumb-current', 'Mentors')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Mentor Management</h4>
                    <a href="{{ route('admin.mentors.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus-circle mr-2"></i>Add New Mentor
                    </a>
                </div>

                <!-- Filters Form -->
                <form method="GET" action="{{ route('admin.mentors.index') }}" id="filterForm">
                    <div class="row mb-4">
                        <!-- Search -->
                        <div class="col-md-6">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   name="search" 
                                   placeholder="Search by name or email..." 
                                   value="{{ request('search') }}"
                                   id="searchInput">
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-6">
                            <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Active Filters & Clear -->
                    @if(request()->hasAny(['status', 'search']))
                    <div class="mb-3">
                        <a href="{{ route('admin.mentors.index') }}" class="btn btn-sm btn-outline-secondary">
                            Clear All Filters
                        </a>
                    </div>
                    @endif
                </form>

                <!-- Mentors Table -->
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 25%;">Mentor</th>
                                <th style="width: 20%;">Contact</th>
                                <th style="width: 20%;">Programs</th>
                                <th style="width: 10%;">Cohorts</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Joined</th>
                                <th style="width: 5%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mentors as $mentor)
                            @php
                                // Get unique programs this mentor teaches
                                $programs = $mentor->cohorts()
                                    ->with('program')
                                    ->get()
                                    ->pluck('program')
                                    ->unique('id')
                                    ->pluck('name')
                                    ->toArray();
                            @endphp
                            <tr>
                                <!-- Mentor Name & Avatar -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $mentor->avatar_url }}" 
                                             class="rounded mr-2" 
                                             style="width: 35px; height: 35px; object-fit: cover;" 
                                             alt="{{ $mentor->name }}">
                                        <div>
                                            <div class="font-weight-bold">{{ $mentor->name }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td>
                                    <div class="small text-muted">{{ $mentor->email }}</div>
                                    @if($mentor->phone)
                                        <div class="small text-muted">{{ $mentor->phone }}</div>
                                    @endif
                                </td>

                                <!-- Programs -->
                                <td>
                                    @if(count($programs) > 0)
                                        <span class="small">{{ Str::limit(implode(', ', $programs), 40) }}</span>
                                    @else
                                        <span class="text-muted small">No Programs</span>
                                    @endif
                                </td>

                                <!-- Cohorts Count -->
                                <td>
                                    <span class="badge badge-primary badge-pill">{{ $mentor->cohorts_count }}</span>
                                </td>

                                <!-- Status -->
                                <td>
                                    @switch($mentor->status)
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
                                </td>

                                <!-- Joined Date -->
                                <td>
                                    <small class="text-muted">{{ $mentor->created_at->format('M d, Y') }}</small>
                                </td>

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-light" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.mentors.edit', $mentor->id) }}" class="dropdown-item">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item" onclick="changeStatus({{ $mentor->id }}, '{{ $mentor->status }}')">
                                                <i class="fa fa-refresh"></i> Change Status
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item text-danger" onclick="deleteMentor({{ $mentor->id }})">
                                                <i class="fa fa-trash"></i> Delete
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
                                        <p>No mentors found</p>
                                        @if(request()->hasAny(['status', 'search']))
                                            <a href="{{ route('admin.mentors.index') }}" class="btn btn-sm btn-outline-primary">Clear Filters</a>
                                        @else
                                            <a href="{{ route('admin.mentors.create') }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-plus-circle mr-2"></i>Add First Mentor
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($mentors->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $mentors->firstItem() }} to {{ $mentors->lastItem() }} of {{ $mentors->total() }} entries
                    </div>
                    <div>
                        {{ $mentors->links() }}
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
                <h5 class="modal-title">Change Mentor Status</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="statusMentorId">
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
                        Changing status will affect the mentor's access and their assigned cohorts.
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
function changeStatus(mentorId, currentStatus) {
    $('#statusMentorId').val(mentorId);
    $('#newStatus').val(currentStatus);
    $('#statusModal').modal('show');
}

$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    
    let mentorId = $('#statusMentorId').val();
    let newStatus = $('#newStatus').val();
    
    $.ajax({
        url: '/admin/mentors/' + mentorId + '/status',
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

// Delete Mentor
function deleteMentor(mentorId) {
    if (!confirm('Are you sure you want to delete this mentor? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: '/admin/mentors/' + mentorId,
        type: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            toastr.success(response.message);
            setTimeout(() => window.location.reload(), 1000);
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Failed to delete mentor');
        }
    });
}
</script>
@endpush