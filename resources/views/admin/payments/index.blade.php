@extends('layouts.admin')

@section('title', 'Payments')
@section('breadcrumb-parent', 'Finance')
@section('breadcrumb-current', 'Payments')

@section('content')
<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-sm-6">
        <div class="card border-primary">
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-1 font-weight-bold text-primary">₦{{ number_format($stats['total_revenue'], 2) }}</h2>
                    <p class="mb-0 text-muted">Total Revenue</p>
                    <small class="text-muted">{{ $stats['total_payments'] }} successful payments</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6">
        <div class="card border-info">
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-1 font-weight-bold text-info">₦{{ number_format($stats['pending_amount'], 2) }}</h2>
                    <p class="mb-0 text-muted">Pending Payments</p>
                    <small class="text-muted">Awaiting confirmation</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-1 font-weight-bold text-warning">₦{{ number_format($stats['installment_pending'], 2) }}</h2>
                    <p class="mb-0 text-muted">Installments Due</p>
                    <small class="text-muted">Second installment balance</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6">
        <div class="card border-danger">
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-1 font-weight-bold text-danger">{{ $stats['failed_payments'] }}</h2>
                    <p class="mb-0 text-muted">Failed Payments</p>
                    <small class="text-muted">Requires attention</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Chart -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Revenue Trend (Last 6 Months)</h4>
                <div style="position: relative; height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Payment Transactions</h4>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportPayments()">
                            <i class="fa fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <form method="GET" action="{{ route('admin.payments.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" name="status">
                                <option value="">All Status</option>
                                <option value="successful" {{ request('status') == 'successful' ? 'selected' : '' }}>Successful</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select class="form-control form-control-sm" name="payment_plan">
                                <option value="">All Plans</option>
                                <option value="one-time" {{ request('payment_plan') == 'one-time' ? 'selected' : '' }}>One-time</option>
                                <option value="installment" {{ request('payment_plan') == 'installment' ? 'selected' : '' }}>Installment</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select class="form-control form-control-sm" name="program_id">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" name="date_from" 
                                   value="{{ request('date_from') }}" placeholder="From Date">
                        </div>

                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" name="date_to" 
                                   value="{{ request('date_to') }}" placeholder="To Date">
                        </div>

                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" name="search" 
                                   value="{{ request('search') }}" placeholder="Search...">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                        </div>
                    </div>
                </form>

                <!-- Payments Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Student</th>
                                <th>Program</th>
                                <th>Amount</th>
                                <th>Payment Plan</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <strong>{{ $payment->transaction_id }}</strong>
                                    <br><small class="text-muted">{{ $payment->reference }}</small>
                                </td>
                                <td>
                                    {{ $payment->user->name }}
                                    <br><small class="text-muted">{{ $payment->user->email }}</small>
                                </td>
                                <td>
                                    {{ $payment->program->name }}
                                    @if($payment->enrollment && $payment->enrollment->cohort)
                                        <br><small class="text-muted">{{ $payment->enrollment->cohort->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>₦{{ number_format($payment->final_amount, 2) }}</strong>
                                    @if($payment->discount_amount > 0)
                                        <br><small class="text-success">-₦{{ number_format($payment->discount_amount, 2) }} discount</small>
                                    @endif
                                    @if($payment->remaining_balance > 0)
                                        <br><small class="text-warning">₦{{ number_format($payment->remaining_balance, 2) }} remaining</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $payment->payment_plan === 'one-time' ? 'info' : 'primary' }}">
                                        {{ ucfirst($payment->payment_plan) }}
                                    </span>
                                    @if($payment->installment_number)
                                        <br><small>Installment {{ $payment->installment_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->status === 'successful')
                                        <span class="badge badge-success">Successful</span>
                                        @if($payment->paid_at)
                                            <br><small class="text-muted">{{ $payment->paid_at->format('M d, Y H:i') }}</small>
                                        @endif
                                    @elseif($payment->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($payment->status === 'failed')
                                        <span class="badge badge-danger">Failed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($payment->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $payment->created_at->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $payment->created_at->format('H:i A') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.payments.show', $payment->id) }}" 
                                       class="btn btn-sm btn-primary" title="View Details">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No payments found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $payments->appends(request()->query())->links() }}
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
        gradient: 'rgba(44, 123, 229, 0.1)'
    };

    // Revenue Chart
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        const monthlyData = @json($monthlyRevenue);
        
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: monthlyData.map(item => {
                    const [year, month] = item.month.split('-');
                    return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Revenue (₦)',
                    data: monthlyData.map(item => item.total),
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
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₦' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            },
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
});

function exportPayments() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.payments.export") }}?' + params.toString();
}
</script>
@endpush