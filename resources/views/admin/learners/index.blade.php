@extends('layouts.admin')

@section('title', 'Learner Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 font-weight-bold">Learner Management</h4>
            <p class="text-muted mb-0">View and manage all registered learners</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="bi bi-people-fill" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Learners</h6>
                            <h3 class="mb-0 font-weight-bold">{{ \App\Models\User::where('role', 'learner')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="bi bi-check-circle-fill" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Active</h6>
                            <h3 class="mb-0 font-weight-bold">{{ \App\Models\User::where('role', 'learner')->where('status', 'active')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="bi bi-book-fill" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Enrolled</h6>
                            <h3 class="mb-0 font-weight-bold">{{ \App\Models\Enrollment::whereIn('status', ['active', 'pending'])->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="bi bi-ban" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Suspended</h6>
                            <h3 class="mb-0 font-weight-bold">{{ \App\Models\User::where('role', 'learner')->where('status', 'suspended')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Filter by Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Filter by Program</label>
                    <select id="programFilter" class="form-select">
                        <option value="">All Programs</option>
                        @foreach(\App\Models\Program::all() as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="learnersTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Learner</th>
                            <th>Contact</th>
                            <th>Program</th>
                            <th>Cohort</th>
                            <th>Mentor</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Change Learner Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="statusLearnerId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Status</label>
                        <select class="form-select" id="newStatus" required>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="alert alert-warning border-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Changing status will affect the learner's access to the platform.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#learnersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.learners.data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.program = $('#programFilter').val();
            }
        },
        columns: [
            {
                data: 'name',
                name: 'first_name',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center gap-2">
                            <img src="${row.avatar_url}" class="rounded" style="width: 40px; height: 40px;" alt="${row.name}">
                            <div>
                                <div class="fw-semibold">${row.name}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'email',
                name: 'email',
                render: function(data, type, row) {
                    return `
                        <div class="small">
                            <div class="text-muted">${data}</div>
                            <div class="text-muted">${row.phone}</div>
                        </div>
                    `;
                }
            },
            {
                data: 'program',
                name: 'program',
                render: function(data) {
                    if (data === 'Not Enrolled') {
                        return '<span class="badge bg-secondary">Not Enrolled</span>';
                    }
                    return `<span class="text-dark fw-medium">${data}</span>`;
                }
            },
            {
                data: 'cohort',
                name: 'cohort',
                render: function(data) {
                    return data === 'N/A' ? '<span class="text-muted">—</span>' : data;
                }
            },
            {
                data: 'mentor',
                name: 'mentor',
                render: function(data) {
                    return data === 'N/A' ? '<span class="text-muted">—</span>' : data;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    let badgeClass = '';
                    let icon = '';
                    switch(data) {
                        case 'active':
                            badgeClass = 'bg-success';
                            icon = 'check-circle';
                            break;
                        case 'suspended':
                            badgeClass = 'bg-danger';
                            icon = 'ban';
                            break;
                        case 'inactive':
                            badgeClass = 'bg-secondary';
                            icon = 'dash-circle';
                            break;
                    }
                    
                    let enrollmentBadge = '';
                    if (row.enrollment_status === 'pending') {
                        enrollmentBadge = '<br><span class="badge bg-warning mt-1">Payment Pending</span>';
                    }
                    
                    return `<span class="badge ${badgeClass}"><i class="bi bi-${icon} me-1"></i>${data.charAt(0).toUpperCase() + data.slice(1)}</span>${enrollmentBadge}`;
                }
            },
            {
                data: 'joined_at',
                name: 'created_at',
                render: function(data) {
                    return `<span class="text-muted small">${data}</span>`;
                }
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <a href="/admin/learners/${row.id}" class="btn btn-outline-primary" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-outline-warning change-status" data-id="${row.id}" data-status="${row.status}" title="Change Status">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[6, 'desc']],
        pageLength: 10,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search learners...",
            lengthMenu: "_MENU_ per page"
        }
    });

    // Filter handlers
    $('#statusFilter, #programFilter').on('change', function() {
        table.ajax.reload();
    });

    // Change Status
    $(document).on('click', '.change-status', function() {
        let learnerId = $(this).data('id');
        let currentStatus = $(this).data('status');
        
        $('#statusLearnerId').val(learnerId);
        $('#newStatus').val(currentStatus);
        $('#statusModal').modal('show');
    });

    $('#statusForm').on('submit', function(e) {
        e.preventDefault();
        
        let learnerId = $('#statusLearnerId').val();
        let newStatus = $('#newStatus').val();
        
        $.ajax({
            url: `/admin/learners/${learnerId}/status`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: newStatus
            },
            success: function(response) {
                $('#statusModal').modal('hide');
                toastr.success(response.message);
                table.ajax.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to update status');
            }
        });
    });
});
</script>
@endpush