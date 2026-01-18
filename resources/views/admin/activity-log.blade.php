@extends('layouts.admin')

@section('title', 'Activity Log')
@section('breadcrumb-parent', 'System')
@section('breadcrumb-current', 'Activity Log')

@section('content')

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Filter Activities</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.activity-log') }}" method="GET" class="row">
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-semibold small">Action Type</label>
                        <select name="action" class="form-control">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-semibold small">User</label>
                        <select name="user_id" class="form-control">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-semibold small">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-semibold small">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-semibold small">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="icon-magnifier mr-1"></i>Filter
                        </button>
                    </div>
                </form>

                @if(request()->hasAny(['action', 'user_id', 'date_from', 'date_to', 'search']))
                <div class="mt-2">
                    <a href="{{ route('admin.activity-log') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="icon-refresh mr-1"></i>Clear Filters
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Activity Log Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    System Activity Log 
                    <span class="badge badge-primary">{{ $activities->total() }} records</span>
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover verticle-middle">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $activity)
                            <tr>
                                <td>
                                    <strong>{{ $activity->created_at->format('M d, Y') }}</strong><br>
                                    <small class="text-muted">{{ $activity->created_at->format('g:i A') }}</small>
                                </td>
                                <td>
                                    @if($activity->user)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $activity->user->avatar_url }}" 
                                            class="rounded-circle mr-2" 
                                            width="35" 
                                            height="35" 
                                            alt="">
                                        <div>
                                            <strong>{{ $activity->user->name }}</strong><br>
                                            <small class="text-muted">{{ ucfirst($activity->user->role) }}</small>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ 
                                        $activity->action === 'user_login' ? 'success' : 
                                        ($activity->action === 'user_logout' ? 'secondary' : 
                                        (str_contains($activity->action, 'unauthorized') ? 'danger' : 
                                        (str_contains($activity->action, 'created') ? 'primary' : 
                                        (str_contains($activity->action, 'updated') ? 'info' : 
                                        (str_contains($activity->action, 'deleted') ? 'warning' : 'dark')))))
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                    </span>
                                </td>
                                <td>
                                    <p class="mb-0">{{ Str::limit($activity->description, 60) }}</p>
                                </td>
                                <td>
                                    <code class="small">{{ $activity->ip_address }}</code>
                                </td>
                                <td>
                                    @if($activity->old_values || $activity->new_values)
                                    <button class="btn btn-sm btn-outline-primary" 
                                        data-toggle="modal" 
                                        data-target="#activityModal{{ $activity->id }}">
                                        <i class="icon-eye"></i> View
                                    </button>
                                    @else
                                    <span class="text-muted small">No data</span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Activity Detail Modal -->
                            @if($activity->old_values || $activity->new_values)
                            <div class="modal fade" id="activityModal{{ $activity->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Activity Details</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <h6 class="font-weight-bold mb-3">{{ $activity->description }}</h6>
                                            
                                            <div class="row">
                                                @if($activity->old_values)
                                                <div class="col-md-6">
                                                    <h6 class="text-danger">Old Values</h6>
                                                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($activity->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                </div>
                                                @endif

                                                @if($activity->new_values)
                                                <div class="col-md-6">
                                                    <h6 class="text-success">New Values</h6>
                                                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($activity->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                </div>
                                                @endif
                                            </div>

                                            <hr>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>IP Address:</strong> {{ $activity->ip_address }}</p>
                                                    <p class="mb-1"><strong>Time:</strong> {{ $activity->created_at->format('F d, Y @ g:i A') }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>User Agent:</strong></p>
                                                    <code class="small">{{ Str::limit($activity->user_agent, 100) }}</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="icon-docs display-4 text-muted mb-3"></i>
                                    <p class="text-muted">No activity found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $activities->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .table code {
        background: #f4f4f4;
        padding: 2px 6px;
        border-radius: 3px;
    }
    pre code {
        font-size: 12px;
    }
</style>
@endpush