@extends('layouts.admin')

@section('title', 'Cohorts')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Cohorts')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">All Cohorts</h4>
                    <a href="{{ route('admin.cohorts.create') }}" class="btn btn-primary gradient-4">Add New Cohort</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cohort Name</th>
                                <th>Program</th>
                                <th>Start Date</th>
                                <th>Status</th>
                                <th>Enrolled/Max</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cohorts as $cohort)
                            <tr>
                                <td><strong>{{ $cohort->name }}</strong><br><small>{{ $cohort->code }}</small></td>
                                <td>{{ $cohort->program->name }}</td>
                                <td>{{ $cohort->start_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ $cohort->status === 'upcoming' ? 'info' : ($cohort->status === 'ongoing' ? 'success' : 'secondary') }}">
                                        {{ ucfirst($cohort->status) }}
                                    </span>
                                </td>
                                <td>{{ $cohort->enrolled_count }} / {{ $cohort->max_students }}</td>
                                <td>
                                    <a href="{{ route('admin.cohorts.edit', $cohort) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <button class="btn btn-sm btn-danger delete-cohort" data-id="{{ $cohort->id }}">Delete</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No cohorts found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $cohorts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).on('click', '.delete-cohort', function() {
    if (confirm('Delete this cohort?')) {
        $.ajax({
            url: `/admin/cohorts/${$(this).data('id')}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                toastr.success(response.message);
                location.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to delete');
            }
        });
    }
});
</script>
@endpush