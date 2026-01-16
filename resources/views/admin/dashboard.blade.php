@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Home')

@section('content')

<!-- Stats Cards -->
<div class="row">
    <div class="col-lg-3 col-sm-6">
        <div class="card gradient-1">
            <div class="card-body">
                <h3 class="card-title text-white">Total Users</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ $stats['total_users'] }}</h2>
                    <p class="text-white mb-0">All Registered</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-people"></i></span>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6">
        <div class="card gradient-4">
            <div class="card-body">
                <h3 class="card-title text-white">Learners</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ $stats['total_learners'] }}</h2>
                    <p class="text-white mb-0">Active Students</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-graduation"></i></span>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6">
        <div class="card gradient-7">
            <div class="card-body">
                <h3 class="card-title text-white">Mentors</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ $stats['total_mentors'] }}</h2>
                    <p class="text-white mb-0">Teaching Staff</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-user"></i></span>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6">
        <div class="card gradient-8">
            <div class="card-body">
                <h3 class="card-title text-white">New This Month</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ $stats['new_users_this_month'] }}</h2>
                    <p class="text-white mb-0">Recent Signups</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-graph"></i></span>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Active Users</h4>
                <div class="d-flex align-items-center">
                    <h2 class="mb-0">{{ $stats['active_users'] }}</h2>
                    <span class="ml-auto badge badge-success badge-pill">Active</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Suspended</h4>
                <div class="d-flex align-items-center">
                    <h2 class="mb-0">{{ $stats['suspended_users'] }}</h2>
                    <span class="ml-auto badge badge-danger badge-pill">Suspended</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Admins</h4>
                <div class="d-flex align-items-center">
                    <h2 class="mb-0">{{ $stats['total_admins'] }}</h2>
                    <span class="ml-auto badge badge-primary badge-pill">Staff</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Users -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Recent Users</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
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
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->avatar_url }}" class="rounded-circle mr-2" width="30" height="30" alt="">
                                        <div>
                                            <strong>{{ $user->name }}</strong><br>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($user->role === 'admin' || $user->role === 'superadmin') badge-primary
                                        @elseif($user->role === 'mentor') badge-info
                                        @else badge-success
                                        @endif">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($user->status === 'active') badge-success
                                        @elseif($user->status === 'suspended') badge-danger
                                        @else badge-secondary
                                        @endif">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td><small>{{ $user->created_at->diffForHumans() }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No users found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">View All Users</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Recent Activity</h4>
                <div id="activity">
                    @forelse($recent_activities as $activity)
                    <div class="media border-bottom-1 pt-3 pb-3">
                        @if($activity->user)
                        <img width="35" src="{{ $activity->user->avatar_url }}" class="mr-3 rounded-circle">
                        @endif
                        <div class="media-body">
                            <h6>{{ $activity->user ? $activity->user->name : 'System' }}</h6>
                            <p class="mb-0">{{ $activity->description }}</p>
                            <span class="text-muted small">{{ $activity->created_at->diffForHumans() }}</span>
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