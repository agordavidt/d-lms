@extends('layouts.admin')

@section('title', 'Programs')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Programs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">All Programs</h4>
                    <a href="{{ route('admin.programs.create') }}" class="btn btn-primary gradient-1">Add New Program</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($programs as $program)
                            <tr>
                                <td><strong>{{ $program->name }}</strong></td>
                                <td>{{ $program->duration }}</td>
                                <td>₦{{ number_format($program->price, 2) }}</td>
                                <td>
                                    @if($program->discount_percentage > 0)
                                    {{ $program->discount_percentage }}%
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $program->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($program->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <button class="btn btn-sm btn-danger delete-program" data-id="{{ $program->id }}">Delete</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No programs found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $programs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).on('click', '.delete-program', function() {
    if (confirm('Delete this program?')) {
        $.ajax({
            url: `/admin/programs/${$(this).data('id')}`,
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