@extends('layouts.admin')

@section('title', 'Dashboard Analytics')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Analytics Overview')

@section('content')

<!-- Key Stats Cards -->
<div class="row">
    <div class="col-lg-3 col-sm-6">
        <div class="card gradient-1">
            <div class="card-body">
                <h3 class="card-title text-white">Total Revenue</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">₦{{ number_format($stats['total_revenue'], 0) }}</h2>
                    <p class="text-white mb-0">
                        <span class="badge badge-light">{{ $stats['revenue_growth_rate'] > 0 ? '+' : '' }}{{ $stats['revenue_growth_rate'] }}%</span>
                        this month
                    </p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-wallet"></i></span>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6">
        <div class="card gradient-4">
            <div class="card-body">
                <h3 class="card-title text-white">Total Users</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ number_format($stats['total_users']) }}</h2>
                    <p class="text-white mb-0">
                        <span class="badge badge-light">+{{ $stats['new_users_this_month'] }}</span>
                        new this month
                    </p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-people"></i></span>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6">
        <div class="card gradient-7">
            <div class="card-body">
                <h3 class="card-title text-white">Active Enrollments</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ number_format($stats['active_enrollments']) }}</h2>
                    <p class="text-white mb-0">{{ $stats['pending_enrollments'] }} pending</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-graduation"></i></span>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6">
        <div class="card gradient-8">
            <div class="card-body">
                <h3 class="card-title text-white">Total Programs</h3>
                <div class="d-inline-block">
                    <h2 class="text-white">{{ number_format($stats['total_programs']) }}</h2>
                    <p class="text-white mb-0">{{ $stats['active_programs'] }} active</p>
                </div>
                <span class="float-right display-5 opacity-5"><i class="icon-book-open"></i></span>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row">
    <div class="col-lg-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-1 font-weight-bold text-primary">{{ $stats['total_learners'] }}</h2>
                    <p class="mb-0 text-muted">Total Learners</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-1 font-weight-bold text-success">{{ $stats['total_mentors'] }}</h2>
                    <p class="mb-0 text-muted">Active Mentors</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-1 font-weight-bold text-info">{{ $stats['active_cohorts'] }}</h2>
                    <p class="mb-0 text-muted">Active Cohorts</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-1 font-weight-bold text-warning">{{ $stats['upcoming_sessions'] }}</h2>
                    <p class="mb-0 text-muted">Upcoming Sessions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- User Registration Trend -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">User Registration Trend (6 Months)</h4>
                <canvas id="userRegistrationChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Revenue Trend -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Revenue Trend (6 Months)</h4>
                <canvas id="revenueTrendChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- More Charts -->
<div class="row">
    <!-- Enrollment by Program -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Enrollments by Program (Top 5)</h4>
                <canvas id="enrollmentByProgramChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- User Roles Distribution -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">User Distribution by Role</h4>
                <canvas id="userRolesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Data Tables -->
<div class="row">
    <!-- Top Programs -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Top Performing Programs</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPrograms as $program)
                            <tr>
                                <td>
                                    <strong>{{ $program->name }}</strong><br>
                                    <small class="text-muted">{{ $program->duration }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-primary badge-pill">{{ $program->enrollments_count }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $program->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($program->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No programs found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Recent Payments</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Program</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                            <tr>
                                <td>
                                    <strong>{{ $payment->user->name }}</strong><br>
                                    <small class="text-muted">{{ $payment->paid_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <small>{{ Str::limit($payment->program->name, 25) }}</small>
                                </td>
                                <td>
                                    <strong class="text-success">₦{{ number_format($payment->final_amount, 2) }}</strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No payments yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Sessions -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Upcoming Sessions</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Session</th>
                                <th>Program</th>
                                <th>Cohort</th>
                                <th>Mentor</th>
                                <th>Date & Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingSessions as $session)
                            <tr>
                                <td>
                                    <strong>{{ $session->title }}</strong><br>
                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $session->session_type)) }}</small>
                                </td>
                                <td>{{ Str::limit($session->program->name, 20) }}</td>
                                <td>{{ $session->cohort->name }}</td>
                                <td>{{ $session->mentor ? $session->mentor->name : 'TBA' }}</td>
                                <td>
                                    <strong>{{ $session->start_time->format('M d, Y') }}</strong><br>
                                    <small class="text-muted">{{ $session->start_time->format('g:i A') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-sm btn-primary">
                                        <i class="icon-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No upcoming sessions</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Quick Actions</h5>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-block">
                            <i class="icon-user-follow mr-2"></i>Add New User
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.programs.create') }}" class="btn btn-success btn-block">
                            <i class="icon-book-open mr-2"></i>Create Program
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.cohorts.create') }}" class="btn btn-info btn-block">
                            <i class="icon-layers mr-2"></i>Create Cohort
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.sessions.create') }}" class="btn btn-warning btn-block">
                            <i class="icon-calendar mr-2"></i>Schedule Session
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default settings
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6c757d';

    // Blue color palette
    const blueColors = {
        primary: '#2c7be5',
        light: '#5a9bf7',
        lighter: '#7eafff',
        lightest: '#a3c4ff',
        dark: '#1e5bb8',
        gradient: 'rgba(44, 123, 229, 0.1)'
    };

    // User Registration Chart (Line)
    const userRegCtx = document.getElementById('userRegistrationChart');
    if (userRegCtx) {
        new Chart(userRegCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($months)) !!},
                datasets: [{
                    label: 'New Users',
                    data: {!! json_encode(array_values($months)) !!},
                    borderColor: blueColors.primary,
                    backgroundColor: blueColors.gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: blueColors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Revenue Trend Chart (Bar)
    const revenueCtx = document.getElementById('revenueTrendChart');
    if (revenueCtx) {
        new Chart(revenueCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($revenueMonths)) !!},
                datasets: [{
                    label: 'Revenue (₦)',
                    data: {!! json_encode(array_values($revenueMonths)) !!},
                    backgroundColor: blueColors.light,
                    borderColor: blueColors.primary,
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: blueColors.primary
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Enrollment by Program Chart (Doughnut)
    const enrollmentCtx = document.getElementById('enrollmentByProgramChart');
    if (enrollmentCtx) {
        new Chart(enrollmentCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($enrollmentsByProgram->pluck('name')->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($enrollmentsByProgram->pluck('count')->toArray()) !!},
                    backgroundColor: [
                        blueColors.primary,
                        blueColors.light,
                        blueColors.lighter,
                        blueColors.lightest,
                        blueColors.dark
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            boxWidth: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // User Roles Chart (Pie)
    const rolesCtx = document.getElementById('userRolesChart');
    if (rolesCtx) {
        new Chart(rolesCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($userRoles->pluck('role')->map(fn($r) => ucfirst($r))->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($userRoles->pluck('count')->toArray()) !!},
                    backgroundColor: [
                        blueColors.dark,
                        blueColors.primary,
                        blueColors.light,
                        blueColors.lightest
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            boxWidth: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush