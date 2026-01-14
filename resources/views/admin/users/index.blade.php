@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage all system users')

@section('content')

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Users</h5>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add New User
            </a>
        </div>

        <!-- Filters -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold small">Filter by Role</label>
                <select id="roleFilter" class="form-select">
                    <option value="">All Roles</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="mentor">Mentor</option>
                    <option value="learner">Learner</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small">Filter by Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table id="usersTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change User Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="statusUserId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Status</label>
                        <select class="form-select" id="newStatus" required>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Changing user status will affect their access to the system.
                    </div>
                </div>
                <div class="modal-footer">
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
    // Initialize DataTable
    let table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.users.data") }}',
            data: function(d) {
                d.role = $('#roleFilter').val();
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
                            <img src="${row.avatar_url}" alt="${row.name}" class="rounded" style="width: 40px; height: 40px;">
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
                render: function(data) {
                    return `<span class="text-muted">${data}</span>`;
                }
            },
            {
                data: 'phone',
                name: 'phone',
                orderable: false
            },
            {
                data: 'role',
                name: 'role',
                render: function(data) {
                    let badgeClass = '';
                    switch(data) {
                        case 'superadmin':
                        case 'admin':
                            badgeClass = 'bg-primary';
                            break;
                        case 'mentor':
                            badgeClass = 'bg-warning';
                            break;
                        case 'learner':
                            badgeClass = 'bg-info';
                            break;
                    }
                    return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    let badgeClass = '';
                    switch(data) {
                        case 'active':
                            badgeClass = 'bg-success';
                            break;
                        case 'suspended':
                            badgeClass = 'bg-danger';
                            break;
                        case 'inactive':
                            badgeClass = 'bg-secondary';
                            break;
                    }
                    return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            {
                data: 'created_at',
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
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <a href="/admin/users/${row.id}/edit" class="btn btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-outline-warning change-status" data-id="${row.id}" data-status="${row.status}" title="Change Status">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            ${row.role !== 'superadmin' ? `
                            <button class="btn btn-outline-danger delete-user" data-id="${row.id}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            ` : ''}
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
            searchPlaceholder: "Search users...",
            lengthMenu: "_MENU_ users per page"
        }
    });

    // Filter handlers
    $('#roleFilter, #statusFilter').on('change', function() {
        table.ajax.reload();
    });

    // Change Status
    $(document).on('click', '.change-status', function() {
        let userId = $(this).data('id');
        let currentStatus = $(this).data('status');
        
        $('#statusUserId').val(userId);
        $('#newStatus').val(currentStatus);
        $('#statusModal').modal('show');
    });

    $('#statusForm').on('submit', function(e) {
        e.preventDefault();
        
        let userId = $('#statusUserId').val();
        let newStatus = $('#newStatus').val();
        
        $.ajax({
            url: `/admin/users/${userId}/status`,
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

    // Delete User
    $(document).on('click', '.delete-user', function() {
        let userId = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            $.ajax({
                url: `/admin/users/${userId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload();
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to delete user');
                }
            });
        }
    });
});
</script>
@endpush

