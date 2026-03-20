@extends('layouts.admin')
@section('title', 'Payments')

@section('content')
<div class="page-header">
    <div><h1>Payments</h1></div>
    <a href="{{ route('admin.payments.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
       class="btn btn-ghost btn-sm">Export CSV</a>
</div>

<div class="container section">

    {{-- Stats --}}
    <div class="stats-row" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 1.5rem;">
        <div class="stat-box highlight">
            <div class="stat-value">₦{{ number_format($stats['total_revenue'], 0) }}</div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['total_payments'] }}</div>
            <div class="stat-label">Successful</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">₦{{ number_format($stats['pending_amount'], 0) }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">₦{{ number_format($stats['installment_pending'], 0) }}</div>
            <div class="stat-label">Installments Due</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: var(--error);">{{ $stats['failed_payments'] }}</div>
            <div class="stat-label">Failed</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <input type="text" name="search" class="form-control" style="width: 220px;"
                   placeholder="Reference or name…" value="{{ request('search') }}">
        </div>
        <div>
            <select name="status" class="form-control" style="width: 140px;">
                <option value="">Any Status</option>
                <option value="successful" {{ request('status') === 'successful' ? 'selected' : '' }}>Successful</option>
                <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>Pending</option>
                <option value="failed"     {{ request('status') === 'failed'     ? 'selected' : '' }}>Failed</option>
                <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <select name="payment_plan" class="form-control" style="width: 160px;">
                <option value="">Any Plan</option>
                <option value="one-time"    {{ request('payment_plan') === 'one-time'    ? 'selected' : '' }}>One-time</option>
                <option value="installment" {{ request('payment_plan') === 'installment' ? 'selected' : '' }}>Installment</option>
            </select>
        </div>
        <div>
            <select name="program_id" class="form-control" style="width: 200px;">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <input type="date" name="date_from" class="form-control" style="width: 145px;" value="{{ request('date_from') }}" placeholder="From">
            <input type="date" name="date_to"   class="form-control" style="width: 145px;" value="{{ request('date_to') }}"   placeholder="To">
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        @if(request()->hasAny(['search','status','payment_plan','program_id','date_from','date_to']))
            <a href="{{ route('admin.payments.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 0.75rem;">
        {{ $payments->total() }} payment{{ $payments->total() !== 1 ? 's' : '' }}
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Learner</th>
                    <th>Program</th>
                    <th>Amount</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>
                        <code style="font-size: 0.75rem; background: var(--bg); padding: 0.15rem 0.4rem; border-radius: 4px;">
                            {{ $payment->reference }}
                        </code>
                    </td>
                    <td>
                        <div style="font-weight: 500; font-size: 0.875rem;">
                            {{ $payment->user->first_name }} {{ $payment->user->last_name }}
                        </div>
                        <div class="text-muted text-small">{{ $payment->user->email }}</div>
                    </td>
                    <td class="text-small" style="max-width: 160px;">{{ $payment->program->name }}</td>
                    <td>
                        <div style="font-weight: 600; font-size: 0.875rem;">₦{{ number_format($payment->final_amount, 0) }}</div>
                        @if($payment->discount_amount > 0)
                        <div class="text-muted text-small">–₦{{ number_format($payment->discount_amount, 0) }} disc.</div>
                        @endif
                    </td>
                    <td class="text-small">
                        {{ $payment->payment_plan === 'installment'
                            ? 'Installment ' . $payment->installment_number . '/2'
                            : 'One-time' }}
                        @if($payment->payment_plan === 'installment' && $payment->remaining_balance > 0)
                        <div class="text-muted text-small">₦{{ number_format($payment->remaining_balance, 0) }} due</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ match($payment->status) {
                            'successful' => 'badge-green',
                            'failed'     => 'badge-red',
                            'pending'    => 'badge-yellow',
                            default      => 'badge-gray',
                        } }}">{{ ucfirst($payment->status) }}</span>
                    </td>
                    <td class="text-muted text-small">
                        {{ $payment->created_at->format('M j, Y') }}
                        @if($payment->paid_at)
                        <div style="color: var(--success); font-size: 0.75rem;">Paid {{ $payment->paid_at->format('g:i A') }}</div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-ghost">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--muted); padding: 3rem;">No payments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div class="text-muted text-small">
            @if($payments->total() > 0)
                Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}
            @endif
        </div>
        {{ $payments->withQueryString()->links() }}
    </div>

</div>
@endsection