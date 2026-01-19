@extends('layouts.admin')

@section('title', 'My Content')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Content')

@push('styles')
<style>
.content-icon {
    font-size: 24px;
    width: 40px;
    text-align: center;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">My Content</h4>
                    <a href="{{ route('mentor.contents.create') }}" class="btn btn-primary">
                        Add New Content
                    </a>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-control" id="filterType" onchange="filterContents()">
                            <option value="">All Types</option>
                            <option value="video" {{ request('content_type') == 'video' ? 'selected' : '' }}>Video</option>
                            <option value="pdf" {{ request('content_type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                            <option value="link" {{ request('content_type') == 'link' ? 'selected' : '' }}>Link</option>
                            <option value="text" {{ request('content_type') == 'text' ? 'selected' : '' }}>Text</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filterStatus" onchange="filterContents()">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchContents" 
                               placeholder="Search content..." value="{{ request('search') }}" 
                               onkeyup="debounceSearch()">
                    </div>
                </div>

                <!-- Contents Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Title</th>
                                <th>Week</th>
                                <th>Program</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contents as $content)
                            <tr>
                                <td class="content-icon">{{ $content->icon }}</td>
                                <td>
                                    <strong>{{ $content->title }}</strong>
                                    @if($content->is_required)
                                        <span class="badge badge-primary">Required</span>
                                    @endif
                                </td>
                                <td>Week {{ $content->moduleWeek->week_number }}</td>
                                <td>{{ Str::limit($content->moduleWeek->programModule->program->name, 30) }}</td>
                                <td><span class="badge badge-light">{{ $content->type_display }}</span></td>
                                <td>
                                    @if($content->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('mentor.contents.edit', $content->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteContent({{ $content->id }})">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-3">You haven't created any content yet.</p>
                                    <a href="{{ route('mentor.contents.create') }}" class="btn btn-primary">
                                        Create Your First Content
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $contents->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchTimeout;

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterContents, 500);
}

function filterContents() {
    const contentType = document.getElementById('filterType').value;
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('searchContents').value;
    
    const params = new URLSearchParams();
    if (contentType) params.append('content_type', contentType);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    window.location.href = '{{ route("mentor.contents.index") }}?' + params.toString();
}

function deleteContent(id) {
    if (confirm('Are you sure you want to delete this content?')) {
        fetch(`/mentor/contents/${id}`, {
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
        });
    }
}
</script>
@endpush