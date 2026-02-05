@extends('layouts.admin')

@section('title', 'Module Details')
@section('breadcrumb-parent', 'Modules')
@section('breadcrumb-current', $module->title)

@section('content')
<div class="row">
    <!-- Module Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">{{ $module->title }}</h4>
                <div>
                    <a href="{{ route('admin.modules.index', ['program_id' => $module->program_id]) }}" 
                       class="btn btn-secondary btn-sm">
                        <i class="icon-arrow-left"></i> Back to Modules
                    </a>
                    {{-- <a href="{{ route('admin.modules.edit', $module->id) }}" 
                       class="btn btn-primary btn-sm">
                         Edit Module
                    </a> --}}
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Module Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>Program:</strong></td>
                                <td>{{ $module->program->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Order:</strong></td>
                                <td>Module {{ $module->order }}</td>
                            </tr>
                            <tr>
                                <td><strong>Duration:</strong></td>
                                <td>{{ $module->duration_weeks }} weeks</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($module->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @elseif($module->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @else
                                        <span class="badge badge-secondary">Archived</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Statistics</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>Total Weeks:</strong></td>
                                <td><span class="badge badge-info">{{ $module->weeks->count() }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Planned Weeks:</strong></td>
                                <td>{{ $module->duration_weeks }}</td>
                            </tr>
                            <tr>
                                <td><strong>Progress:</strong></td>
                                <td>
                                    @php
                                        $progress = $module->duration_weeks > 0 ? ($module->weeks->count() / $module->duration_weeks) * 100 : 0;
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: {{ min($progress, 100) }}%">
                                            {{ number_format(min($progress, 100), 0) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($module->description)
                <div class="mb-4">
                    <h6 class="text-muted">Description</h6>
                    <p>{{ $module->description }}</p>
                </div>
                @endif

                @if($module->learning_objectives && count($module->learning_objectives) > 0)
                <div class="mb-4">
                    <h6 class="text-muted">Learning Objectives</h6>
                    <ul>
                        @foreach($module->learning_objectives as $objective)
                            <li>{{ $objective }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        <!-- Weeks List -->
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Weeks in This Module</h4>
                <a href="{{ route('admin.weeks.create', ['module_id' => $module->id]) }}" 
                   class="btn btn-primary btn-sm">
                   Add Week
                </a>
            </div>
            <div class="card-body">
                @if($module->weeks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="10%">Week #</th>
                                    <th>Title</th>
                                    <th width="15%">Contents</th>
                                    <th width="12%">Status</th>
                                    <th width="20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($module->weeks->sortBy('week_number') as $week)
                                <tr>
                                    <td><strong>Week {{ $week->week_number }}</strong></td>
                                    <td>
                                        <strong>{{ $week->title }}</strong>
                                        @if($week->description)
                                            <br><small class="text-muted">{{ Str::limit($week->description, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $week->contents->count() }}</span>
                                        @if($week->contents->where('is_required', true)->count() > 0)
                                            <br><small class="text-muted">{{ $week->contents->where('is_required', true)->count() }} required</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($week->status === 'published')
                                            <span class="badge badge-success">Published</span>
                                        @elseif($week->status === 'draft')
                                            <span class="badge badge-warning">Draft</span>
                                        @else
                                            <span class="badge badge-secondary">Archived</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.weeks.show', $week->id) }}" 
                                           class="btn btn-info btn-sm" title="View Details">
                                            <i class="icon-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.weeks.edit', $week->id) }}" 
                                           class="btn btn-primary btn-sm" title="Edit">
                                            <i class="icon-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="deleteWeek({{ $week->id }})" title="Delete">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="icon-grid" style="font-size: 48px; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">No Weeks Added Yet</h5>
                        <p class="text-muted mb-3">Start building your module by adding weeks</p>
                        <a href="{{ route('admin.weeks.create', ['module_id' => $module->id]) }}" 
                           class="btn btn-primary">
                            <i class="icon-plus"></i> Add First Week
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Quick Stats</h4>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Weeks</span>
                        <h3 class="mb-0 text-primary">{{ $module->weeks->count() }}</h3>
                    </div>
                </div>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Contents</span>
                        <h3 class="mb-0 text-info">{{ $module->weeks->sum(function($week) { return $week->contents->count(); }) }}</h3>
                    </div>
                </div>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Published Weeks</span>
                        <h3 class="mb-0 text-success">{{ $module->weeks->where('status', 'published')->count() }}</h3>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Draft Weeks</span>
                        <h3 class="mb-0 text-warning">{{ $module->weeks->where('status', 'draft')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Quick Actions</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.weeks.create', ['module_id' => $module->id]) }}" 
                   class="btn btn-primary btn-block mb-2">
                     Add Week
                </a>
                {{-- <a href="{{ route('admin.modules.edit', $module->id) }}" 
                   class="btn btn-secondary btn-block mb-2">
                   Edit Module
                </a> --}}
                <a href="{{ route('admin.modules.index', ['program_id' => $module->program_id]) }}" 
                   class="btn btn-light btn-block">
                    <i class="icon-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteWeek(id) {
    if (confirm('Are you sure you want to delete this week? This will also delete all contents in this week.')) {
        fetch(`/admin/weeks/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('An error occurred. Please try again.');
        });
    }
}
</script>
@endpush