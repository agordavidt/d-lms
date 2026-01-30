@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Learner Management</h4>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="font-weight-bold small text-uppercase">Filter by Status</label>
                            <select id="statusFilter" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="font-weight-bold small text-uppercase">Filter by Program</label>
                            <select id="programFilter" class="form-control">
                                <option value="">All Programs</option>
                                @foreach(\App\Models\Program::all() as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table id="learnersTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Learner</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Program</th>
                                    <th>Cohort</th>
                                    <th>Mentor</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Learner</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Program</th>
                                    <th>Cohort</th>
                                    <th>Mentor</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
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
// Debug logging
console.log('jQuery loaded:', typeof $ !== 'undefined');
console.log('DataTables loaded:', typeof $.fn.DataTable !== 'undefined');

$(document).ready(function() {
    console.log('Initializing learners table...');
    
    let table = $('#learnersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.learners.data") }}',
            type: 'GET',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.program = $('#programFilter').val();
                console.log('AJAX request data:', d);
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX Error:', xhr.responseText);
                toastr.error('Error loading data: ' + xhr.statusText);
            },
            dataSrc: function(json) {
                console.log('Response received:', json);
                return json.data;
            }
        },
        columns: [
            {
                data: 'name',
                name: 'first_name',
                render: function(data, type, row) {
                    return '<div class="d-flex align-items-center">' +
                           '<img src="' + row.avatar_url + '" class="rounded mr-2" style="width: 40px; height: 40px;" alt="' + row.name + '">' +
                           '<div><strong>' + row.name + '</strong></div>' +
                           '</div>';
                }
            },
            {
                data: 'email',
                name: 'email',
                render: function(data) {
                    return '<span class="text-muted">' + data + '</span>';
                }
            },
            {
                data: 'phone',
                name: 'phone',
                orderable: false
            },
            {
                data: 'program',
                name: 'program',
                render: function(data) {
                    if (data === 'Not Enrolled') {
                        return '<span class="badge badge-secondary">Not Enrolled</span>';
                    }
                    return '<span class="font-weight-bold">' + data + '</span>';
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
                    switch(data) {
                        case 'active':
                            badgeClass = 'badge-success';
                            break;
                        case 'suspended':
                            badgeClass = 'badge-danger';
                            break;
                        case 'inactive':
                            badgeClass = 'badge-secondary';
                            break;
                    }
                    
                    let html = '<span class="badge ' + badgeClass + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                    
                    if (row.enrollment_status === 'pending') {
                        html += '<br><span class="badge badge-warning mt-1">Payment Pending</span>';
                    }
                    
                    return html;
                }
            },
            {
                data: 'joined_at',
                name: 'created_at',
                render: function(data) {
                    return '<span class="text-muted">' + data + '</span>';
                }
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<div class="btn-group btn-group-sm">' +
                           '<a href="/admin/learners/' + row.id + '" class="btn btn-primary btn-sm" title="View Details">' +
                           '<i class="fa fa-eye"></i>' +
                           '</a>' +
                           '<button class="btn btn-warning btn-sm change-status" data-id="' + row.id + '" data-status="' + row.status + '" title="Change Status">' +
                           '<i class="fa fa-refresh"></i>' +
                           '</button>' +
                           '</div>';
                }
            }
        ],
        order: [[7, 'desc']],
        pageLength: 10,
        responsive: true
    });

    // Filter handlers
    $('#statusFilter, #programFilter').on('change', function() {
        console.log('Filter changed');
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
            url: '/admin/learners/' + learnerId + '/status',
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