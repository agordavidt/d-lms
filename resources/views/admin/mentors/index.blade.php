@extends('layouts.admin')

@section('title', 'Mentor Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 font-weight-bold">Mentor Management</h4>
            <p class="text-muted mb-0">Manage mentor accounts and assignments</p>
        </div>
        <a href="{{ route('admin.mentors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New Mentor
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="bi bi-person-badge-fill" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Mentors</h6>
                            <h3 class="mb-0 font-weight-bold">{{ \App\Models\User::where('role', 'mentor')->count() }}</h3>
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
                            <h3 class="mb-0 font-weight-bold">{{ \App\Models\User::where('role', 'mentor')->where('status', 'active')->count() }}</h3>
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
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="bi bi-calendar-event-fill" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Cohorts</h6>
                            <h3 class="mb-0 font-weight-bold">{{ \App\Models\Cohort::count() }}</h3>
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
                                <i class="bi bi-book-half" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Programs</h6>
                            <h3 class="mb-0 font-weight-bold">{{ \App\Models\Program::count() }}</h3>
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
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="mentorsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Mentor</th>
                            <th>Contact</th>
                            <th>Programs</th>
                            <th>Cohorts</th>
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
                <h5 class="modal-title fw-bold">Change Mentor Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="statusMentorId">
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
                        Changing status will affect the mentor's access and their assigned cohorts.
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
    let table = $('#mentorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.mentors.data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
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
                data: 'programs',
                name: 'programs',
                orderable: false,
                render: function(data) {
                    if (data === 'No Programs') {
                        return '<span class="text-muted">—</span>';
                    }
                    return `<span class="small">${data}</span>`;
                }
            },
            {
                data: 'cohorts_count',
                name: 'cohorts_count',
                render: function(data) {
                    return `<span class="badge bg-primary rounded-pill">${data}</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
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
                    return `<span class="badge ${badgeClass}"><i class="bi bi-${icon} me-1"></i>${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
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
                            <a href="/admin/mentors/${row.id}/edit" class="btn btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-outline-warning change-status" data-id="${row.id}" data-status="${row.status}" title="Change Status">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button class="btn btn-outline-danger delete-mentor" data-id="${row.id}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[5, 'desc']],
        pageLength: 10,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search mentors...",
            lengthMenu: "_MENU_ per page"
        }
    });

    // Filter handlers
    $('#statusFilter').on('change', function() {
        table.ajax.reload();
    });

    // Change Status
    $(document).on('click', '.change-status', function() {
        let mentorId = $(this).data('id');
        let currentStatus = $(this).data('status');
        
        $('#statusMentorId').val(mentorId);
        $('#newStatus').val(currentStatus);
        $('#statusModal').modal('show');
    });

    $('#statusForm').on('submit', function(e) {
        e.preventDefault();
        
        let mentorId = $('#statusMentorId').val();
        let newStatus = $('#newStatus').val();
        
        $.ajax({
            url: `/admin/mentors/${mentorId}/status`,
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

    // Delete Mentor
    $(document).on('click', '.delete-mentor', function() {
        let mentorId = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this mentor? This action cannot be undone.')) {
            $.ajax({
                url: `/admin/mentors/${mentorId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload();
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to delete mentor');
                }
            });
        }
    });
});
</script>
@endpush