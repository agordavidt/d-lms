@extends('layouts.admin')

@section('title', 'Week Details')
@section('breadcrumb-parent', 'Weeks')
@section('breadcrumb-current', $week->title)

@section('content')
<div class="row">
    <!-- Week Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Week {{ $week->week_number }}: {{ $week->title }}</h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-muted mb-3">Week Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Program:</strong></td>
                                <td>{{ $week->programModule->program->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Module:</strong></td>
                                <td>{{ $week->programModule->title }}</td>
                            </tr>
                            <tr>
                                <td><strong>Week Number:</strong></td>
                                <td>{{ $week->week_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($week->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @elseif($week->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @else
                                        <span class="badge badge-secondary">Archived</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-muted mb-3">Statistics</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Total Contents:</strong></td>
                                <td><span class="badge badge-info">{{ $week->total_contents_count }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Required Contents:</strong></td>
                                <td><span class="badge badge-primary">{{ $week->required_contents_count }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Has Assessment:</strong></td>
                                <td>{{ $week->has_assessment ? 'Yes' : 'No' }}</td>
                            </tr>
                            @if($week->has_assessment)
                            <tr>
                                <td><strong>Pass Percentage:</strong></td>
                                <td>{{ $week->assessment_pass_percentage }}%</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($week->description)
                <div class="mb-4">
                    <h5 class="text-muted">Description</h5>
                    <p>{{ $week->description }}</p>
                </div>
                @endif

                @if($week->learning_outcomes && count($week->learning_outcomes) > 0)
                <div class="mb-4">
                    <h5 class="text-muted">Learning Outcomes</h5>
                    <ul>
                        @foreach($week->learning_outcomes as $outcome)
                            <li>{{ $outcome }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        <!-- Week Contents -->
        {{-- <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Week Contents</h4>
                <a href="{{ route('admin.contents.create', ['week_id' => $week->id, 'module_id' => $week->program_module_id, 'program_id' => $week->programModule->program_id]) }}" 
                   class="btn btn-sm btn-primary">
                    <i class="icon-plus"></i> Add Content
                </a>
            </div>
            <div class="card-body">
                @if($week->contents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Order</th>
                                    <th style="width: 50px;"></th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($week->contents as $content)
                                <tr>
                                    <td>{{ $content->order }}</td>
                                    <td style="font-size: 24px;">{{ $content->icon }}</td>
                                    <td>
                                        <strong>{{ $content->title }}</strong>
                                        @if($content->is_required)
                                            <span class="badge badge-primary badge-sm">Required</span>
                                        @endif
                                        @if($content->description)
                                            <br><small class="text-muted">{{ Str::limit($content->description, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light">{{ $content->type_display }}</span>
                                        @if($content->content_type === 'video' && $content->video_duration_minutes)
                                            <br><small class="text-muted">{{ $content->video_duration_minutes }} min</small>
                                        @elseif($content->content_type === 'pdf' && $content->file_size)
                                            <br><small class="text-muted">{{ $content->file_size }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($content->status === 'published')
                                            <span class="badge badge-success">Published</span>
                                        @else
                                            <span class="badge badge-warning">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.contents.edit', $content->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteContent({{ $content->id }})">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-3">No contents added yet</p>
                        <a href="{{ route('admin.contents.create', ['week_id' => $week->id, 'module_id' => $week->program_module_id, 'program_id' => $week->programModule->program_id]) }}" 
                           class="btn btn-primary">
                            Add First Content
                        </a>
                    </div>
                @endif
            </div>
        </div> --}}
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Actions</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.weeks.index', ['module_id' => $week->program_module_id]) }}" 
                   class="btn btn-secondary btn-block mb-2">
                    <i class="icon-arrow-left"></i> Back to Weeks
                </a>
                
                <a href="{{ route('admin.weeks.edit', $week->id) }}" 
                   class="btn btn-primary btn-block mb-2">
                    <i class="icon-pencil"></i> Edit Week
                </a>

                <a href="{{ route('admin.contents.create', ['week_id' => $week->id, 'module_id' => $week->program_module_id, 'program_id' => $week->programModule->program_id]) }}" 
                   class="btn btn-success btn-block mb-2">
                    <i class="icon-plus"></i> Add Content
                </a>

                <button type="button" class="btn btn-danger btn-block" onclick="deleteWeek()">
                    <i class="icon-trash"></i> Delete Week
                </button>
            </div>
        </div>

        <!-- Week Timeline -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Timestamps</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $week->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $week->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteContent(id) {
    if (confirm('Are you sure you want to delete this content?')) {
        fetch(`/admin/contents/${id}`, {
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

function deleteWeek() {
    if (confirm('Are you sure you want to delete this week? This will also delete all contents in this week.')) {
        fetch(`/admin/weeks/{{ $week->id }}`, {
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
                setTimeout(() => {
                    window.location.href = '{{ route("admin.weeks.index", ["module_id" => $week->program_module_id]) }}';
                }, 1000);
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