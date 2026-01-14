@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->first_name . '!')

@section('content')

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="mb-2 opacity-75" style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">Total Users</p>
                    <h3>{{ $stats['total_users'] }}</h3>
                    <p class="mb-0 opacity-75 small">All registered users</p>
                </div>
                <div class="p-3 bg-white bg-opacity-25 rounded-3">
                    <i class="bi bi-people fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-2 opacity-75" style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">Learners</p>
                        <h3 class="mb-2">{{ $stats['total_learners'] }}</h3>
                        <p class="mb-0 opacity-75 small">Active students</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-25 rounded-3">
                        <i class="bi bi-mortarboard fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-2 opacity-75" style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">Mentors</p>
                        <h3 class="mb-2">{{ $stats['total_mentors'] }}</h3>
                        <p class="mb-0 opacity-75 small">Teaching staff</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-25 rounded-3">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white;">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-2 opacity-75" style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">New This Month</p>
                        <h3 class="mb-2">{{ $stats['new_users_this_month'] }}</h3>
                        <p class="mb-0 opacity-75 small">Recent signups</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-25 rounded-3">
                        <i class="bi bi-graph-up-arrow fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 1px;">Active Users</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['active_users'] }}</h4>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 rounded-3">
                        <i class="bi bi-check-circle text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 1px;">Suspended</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['suspended_users'] }}</h4>
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 rounded-3">
                        <i class="bi bi-x-circle text-danger fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 1px;">Admins</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['total_admins'] }}</h4>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3">
                        <i class="bi bi-shield-check text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Users -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Recent Users</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded" style="width: 32px; height: 32px;">
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($user->role === 'admin' || $user->role === 'superadmin') bg-primary
                                        @elseif($user->role === 'mentor') bg-warning
                                        @else bg-info
                                        @endif">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($user->status === 'active') bg-success
                                        @elseif($user->status === 'suspended') bg-danger
                                        @else bg-secondary
                                        @endif">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $user->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No users found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">View All Users</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Recent Activity</h5>
                <div class="activity-list">
                    @forelse($recent_activities as $activity)
                    <div class="activity-item d-flex gap-3 mb-3 pb-3 border-bottom">
                        <div class="activity-icon">
                            @if(str_contains($activity->action, 'login'))
                                <i class="bi bi-box-arrow-in-right text-success fs-5"></i>
                            @elseif(str_contains($activity->action, 'logout'))
                                <i class="bi bi-box-arrow-right text-muted fs-5"></i>
                            @elseif(str_contains($activity->action, 'created'))
                                <i class="bi bi-plus-circle text-primary fs-5"></i>
                            @elseif(str_contains($activity->action, 'updated'))
                                <i class="bi bi-pencil text-warning fs-5"></i>
                            @elseif(str_contains($activity->action, 'deleted'))
                                <i class="bi bi-trash text-danger fs-5"></i>
                            @else
                                <i class="bi bi-info-circle text-info fs-5"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-1 fw-semibold">{{ $activity->user ? $activity->user->name : 'System' }}</p>
                            <p class="mb-1 small text-muted">{{ $activity->description }}</p>
                            <p class="mb-0 small text-muted">
                                <i class="bi bi-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted">No recent activity</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection