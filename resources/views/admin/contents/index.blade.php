@extends('layouts.admin')

@section('title', 'Learning Contents')
@section('breadcrumb-parent', 'Curriculum')
@section('breadcrumb-current', 'Contents')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Learning Contents</h4>
                    <div class="text-muted">
                        Total: {{ $contents->total() }} contents
                    </div>
                </div>

                <!-- Filters Form -->
                <form method="GET" action="{{ route('admin.contents.index') }}" id="filterForm">
                    <div class="row mb-4">
                        <!-- Program Filter -->
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" name="program_id" id="programFilter" onchange="this.form.submit()">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Module Filter -->
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" name="module_id" id="moduleFilter" onchange="this.form.submit()">
                                <option value="">All Modules</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>
                                        {{ Str::limit($module->title, 25) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Week Filter -->
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" name="week_id" id="weekFilter" onchange="this.form.submit()">
                                <option value="">All Weeks</option>
                                @foreach($weeks as $week)
                                    <option value="{{ $week->id }}" {{ request('week_id') == $week->id ? 'selected' : '' }}>
                                        Week {{ $week->week_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Type Filter -->
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" name="content_type" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="video" {{ request('content_type') == 'video' ? 'selected' : '' }}>Video</option>
                                <option value="pdf" {{ request('content_type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="link" {{ request('content_type') == 'link' ? 'selected' : '' }}>Link</option>
                                <option value="text" {{ request('content_type') == 'text' ? 'selected' : '' }}>Text</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="col-md-2">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   name="search" 
                                   placeholder="Search..." 
                                   value="{{ request('search') }}"
                                   id="searchInput">
                        </div>
                    </div>

                    <!-- Active Filters & Clear -->
                    @if(request()->hasAny(['program_id', 'module_id', 'week_id', 'content_type', 'status', 'search']))
                    <div class="mb-3">
                        <a href="{{ route('admin.contents.index') }}" class="btn btn-sm btn-outline-secondary">
                            Clear All Filters
                        </a>
                    </div>
                    @endif
                </form>

                <!-- Contents Table -->
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%;">Type</th>
                                <th style="width: 30%;">Title</th>
                                <th style="width: 15%;">Program</th>
                                <th style="width: 15%;">Module</th>
                                <th style="width: 10%;">Week</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Created</th>
                                <th style="width: 5%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contents as $content)
                            <tr>
                                <!-- Type Icon -->
                                <td class="text-center">
                                    @switch($content->content_type)
                                        @case('video')
                                            <span title="Video">📹</span>
                                            @break
                                        @case('pdf')
                                            <span title="PDF">📄</span>
                                            @break
                                        @case('link')
                                            <span title="Link">🔗</span>
                                            @break
                                        @case('text')
                                            <span title="Text">📝</span>
                                            @break
                                    @endswitch
                                </td>

                                <!-- Title -->
                                <td>
                                    <div class="font-weight-bold">{{ Str::limit($content->title, 50) }}</div>
                                    {{-- @if($content->is_required)
                                        <small class="text-primary">Required</small>
                                    @endif --}}
                                </td>

                                <!-- Program -->
                                <td>
                                    <small>{{ Str::limit($content->moduleWeek->programModule->program->name, 25) }}</small>
                                </td>

                                <!-- Module -->
                                <td>
                                    <small>{{ Str::limit($content->moduleWeek->programModule->title, 25) }}</small>
                                </td>

                                <!-- Week -->
                                <td>
                                    <small>Week {{ $content->moduleWeek->week_number }}</small>
                                </td>

                                <!-- Status -->
                                <td>
                                    @if($content->status === 'published')
                                        <span class="badge badge-sm badge-success">Published</span>
                                    @else
                                        <span class="badge badge-sm badge-warning">Draft</span>
                                    @endif
                                </td>

                                <!-- Created Date -->
                                <td>
                                    <small class="text-muted">{{ $content->created_at->format('M d, Y') }}</small>
                                </td>

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-light" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.contents.edit', $content->id) }}" class="dropdown-item">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            <a href="{{ route('admin.weeks.show', $content->module_week_id) }}" class="dropdown-item">
                                                <i class="fa fa-eye"></i> View Week
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item text-danger" onclick="deleteContent({{ $content->id }})">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa fa-inbox fa-3x mb-3"></i>
                                        <p>No contents found</p>
                                        @if(request()->hasAny(['program_id', 'module_id', 'week_id', 'content_type', 'status', 'search']))
                                            <a href="{{ route('admin.contents.index') }}" class="btn btn-sm btn-outline-primary">Clear Filters</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($contents->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $contents->firstItem() }} to {{ $contents->lastItem() }} of {{ $contents->total() }} entries
                    </div>
                    <div>
                        {{ $contents->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Delete content with confirmation
function deleteContent(id) {
    if (!confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/admin/contents/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            toastr.error(data.message || 'Failed to delete content');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('An error occurred while deleting the content');
    });
}

// Debounced search
let searchTimeout;
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
});

// Reset module and week filters when program changes
document.getElementById('programFilter').addEventListener('change', function() {
    document.getElementById('moduleFilter').value = '';
    document.getElementById('weekFilter').value = '';
});

// Reset week filter when module changes
document.getElementById('moduleFilter').addEventListener('change', function() {
    document.getElementById('weekFilter').value = '';
});
</script>
@endpush