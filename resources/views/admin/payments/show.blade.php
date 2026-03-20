@extends('layouts.admin')
@section('title', 'Payment · ' . $payment->reference)

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.payments.index') }}">Payments</a></div>
        <h1>Payment Detail</h1>
    </div>
    <span class="badge {{ match($payment->status) {
        'successful' => 'badge-green',
        'failed'     => 'badge-red',
        'pending'    => 'badge-yellow',
        default      => 'badge-gray',
    } }}" style="padding: 0.4rem 1rem; font-size: 0.8rem;">{{ ucfirst($payment->status) }}</span>
</div>

<div class="container section">
<div style="display: grid; grid-template-columns: 1fr 280px; gap: 2rem; align-items: start;">

    {{-- Main --}}
    <div>

        {{-- Core details --}}
        <div class="card" style="margin-bottom: 1rem;">
            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); font-weight: 600; font-family: 'Source Serif 4', serif;">
                Transaction
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    @foreach([
                        'Reference'      => $payment->reference,
                        'Transaction ID' => $payment->transaction_id ?? '—',
                        'Program'        => $payment->program->name,
                        'Payment Method' => ucfirst($payment->payment_method ?? '—'),
                        'Plan'           => $payment->payment_plan === 'installment'
                                            ? 'Installment ' . $payment->installment_number . '/2'
                                            : 'One-time',
                        'Paid At'        => $payment->paid_at?->format('M j, Y · g:i A') ?? '—',
                    ] as $label => $value)
                    <div>
                        <div class="text-muted text-small" style="margin-bottom: 0.2rem;">{{ $label }}</div>
                        <div style="font-weight: 500; font-size: 0.875rem;">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Amounts --}}
        <div class="card" style="margin-bottom: 1rem;">
            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); font-weight: 600; font-family: 'Source Serif 4', serif;">
                Amount Breakdown
            </div>
            <div class="card-body">
                <div style="display: grid; gap: 0.75rem; font-size: 0.875rem; max-width: 300px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span class="text-muted">Original amount</span>
                        <span>₦{{ number_format($payment->amount, 2) }}</span>
                    </div>
                    @if($payment->discount_amount > 0)
                    <div style="display: flex; justify-content: space-between;">
                        <span class="text-muted">Discount</span>
                        <span style="color: var(--success);">–₦{{ number_format($payment->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1rem; padding-top: 0.5rem; border-top: 1px solid var(--border);">
                        <span>Amount Paid</span>
                        <span>₦{{ number_format($payment->final_amount, 2) }}</span>
                    </div>
                    @if($payment->payment_plan === 'installment' && $payment->remaining_balance > 0)
                    <div style="display: flex; justify-content: space-between; color: var(--warning);">
                        <span>Remaining balance</span>
                        <span style="font-weight: 600;">₦{{ number_format($payment->remaining_balance, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Related payments (other installments) --}}
        @if($relatedPayments->isNotEmpty())
        <div class="card">
            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); font-weight: 600; font-family: 'Source Serif 4', serif;">
                Related Payments
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Installment</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($relatedPayments as $related)
                    <tr>
                        <td><code style="font-size: 0.75rem;">{{ $related->reference }}</code></td>
                        <td class="text-small">{{ $related->installment_number ? '#' . $related->installment_number : '—' }}</td>
                        <td style="font-weight: 600; font-size: 0.875rem;">₦{{ number_format($related->final_amount, 0) }}</td>
                        <td>
                            <span class="badge {{ match($related->status) {
                                'successful' => 'badge-green',
                                'failed'     => 'badge-red',
                                'pending'    => 'badge-yellow',
                                default      => 'badge-gray',
                            } }}">{{ ucfirst($related->status) }}</span>
                        </td>
                        <td class="text-muted text-small">{{ $related->created_at->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    {{-- Sidebar --}}
    <div>
        <div class="card card-body" style="margin-bottom: 1rem;">
            <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif; font-size: 0.95rem;">Learner</div>
            <div style="font-weight: 500; margin-bottom: 0.2rem;">
                {{ $payment->user->first_name }} {{ $payment->user->last_name }}
            </div>
            <div class="text-muted text-small">{{ $payment->user->email }}</div>
            @if($payment->user->phone)
            <div class="text-muted text-small">{{ $payment->user->phone }}</div>
            @endif
            <div style="margin-top: 0.75rem;">
                <a href="{{ route('admin.learners.show', $payment->user_id) }}" class="btn btn-sm btn-ghost">View Learner</a>
            </div>
        </div>

        @if($payment->enrollment)
        <div class="card card-body">
            <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif; font-size: 0.95rem;">Enrollment</div>
            <div style="display: grid; gap: 0.5rem; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Status</span>
                    <span class="badge {{ $payment->enrollment->status === 'active' ? 'badge-green' : 'badge-gray' }}">
                        {{ ucfirst($payment->enrollment->status) }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Progress</span>
                    <span style="font-weight: 600;">{{ number_format($payment->enrollment->progress_percentage, 0) }}%</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Enrolled</span>
                    <span class="text-small">{{ \Carbon\Carbon::parse($payment->enrollment->enrolled_at)->format('M j, Y') }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
</div>
@endsection