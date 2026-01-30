@extends('layouts.admin')

@section('title', 'Payment Details')
@section('breadcrumb-parent', 'Payments')
@section('breadcrumb-current', 'Details')

@section('content')
<div class="row">
    <!-- Payment Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Payment Information</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5 class="text-muted">Transaction Details</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Transaction ID:</strong></td>
                                <td>{{ $payment->transaction_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Reference:</strong></td>
                                <td>{{ $payment->reference }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($payment->status === 'successful')
                                        <span class="badge badge-success badge-lg">Successful</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge badge-warning badge-lg">Pending</span>
                                    @elseif($payment->status === 'failed')
                                        <span class="badge badge-danger badge-lg">Failed</span>
                                    @else
                                        <span class="badge badge-secondary badge-lg">{{ ucfirst($payment->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td>{{ $payment->payment_method ? ucfirst($payment->payment_method) : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-muted">Student Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $payment->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $payment->user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $payment->user->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Student ID:</strong></td>
                                <td>{{ $payment->user->id }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5 class="text-muted">Program Details</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Program:</strong></td>
                                <td>{{ $payment->program->name }}</td>
                            </tr>
                            @if($payment->enrollment && $payment->enrollment->cohort)
                            <tr>
                                <td><strong>Cohort:</strong></td>
                                <td>{{ $payment->enrollment->cohort->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cohort Code:</strong></td>
                                <td>{{ $payment->enrollment->cohort->code }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Enrollment Status:</strong></td>
                                <td>
                                    @if($payment->enrollment)
                                        <span class="badge badge-{{ $payment->enrollment->status === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($payment->enrollment->status) }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-muted">Payment Breakdown</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Original Amount:</strong></td>
                                <td>₦{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            @if($payment->discount_amount > 0)
                            <tr>
                                <td><strong>Discount:</strong></td>
                                <td class="text-success">-₦{{ number_format($payment->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Final Amount:</strong></td>
                                <td><h4>₦{{ number_format($payment->final_amount, 2) }}</h4></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Plan:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $payment->payment_plan === 'one-time' ? 'info' : 'primary' }}">
                                        {{ ucfirst($payment->payment_plan) }}
                                    </span>
                                    @if($payment->installment_number)
                                        - Installment {{ $payment->installment_number }}
                                    @endif
                                </td>
                            </tr>
                            @if($payment->remaining_balance > 0)
                            <tr>
                                <td><strong>Remaining Balance:</strong></td>
                                <td class="text-warning">₦{{ number_format($payment->remaining_balance, 2) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-muted">Timestamps</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Created At:</strong></td>
                                <td>{{ $payment->created_at->format('M d, Y H:i A') }}</td>
                            </tr>
                            @if($payment->paid_at)
                            <tr>
                                <td><strong>Paid At:</strong></td>
                                <td>{{ $payment->paid_at->format('M d, Y H:i A') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td>{{ $payment->updated_at->format('M d, Y H:i A') }}</td>
                            </tr>
                        </table>
                    </div>

                    @if($payment->metadata)
                    <div class="col-md-6">
                        <h5 class="text-muted">Additional Information</h5>
                        <table class="table table-borderless">
                            @foreach($payment->metadata as $key => $value)
                                @if(!in_array($key, ['program_name', 'user_name', 'user_email']))
                                <tr>
                                    <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></td>
                                    <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Flutterwave Response (if available) -->
        @if($payment->flutterwave_response)
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title">Gateway Response</h4>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3" style="max-height: 300px; overflow-y: auto;">{{ json_encode(json_decode($payment->flutterwave_response), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Quick Actions</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-block mb-2">
                    <i class="fa fa-arrow-left"></i> Back to Payments
                </a>
                
                @if($payment->enrollment)
                <a href="{{ route('admin.learners.show', $payment->user->id) }}" class="btn btn-primary btn-block mb-2">
                    <i class="fa fa-user"></i> View Student Profile
                </a>
                @endif

                <button onclick="window.print()" class="btn btn-info btn-block">
                    <i class="fa fa-print"></i> Print Receipt
                </button>
            </div>
        </div>

        <!-- Related Payments -->
        @if(count($relatedPayments) > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title">Related Payments</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($relatedPayments as $related)
                    <a href="{{ route('admin.payments.show', $related->id) }}" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $related->transaction_id }}</strong>
                                <br>
                                <small>
                                    @if($related->installment_number)
                                        Installment {{ $related->installment_number }}
                                    @else
                                        {{ ucfirst($related->payment_plan) }}
                                    @endif
                                </small>
                            </div>
                            <div>
                                <span class="badge badge-{{ $related->status === 'successful' ? 'success' : ($related->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($related->status) }}
                                </span>
                                <br>
                                <small>₦{{ number_format($related->final_amount, 2) }}</small>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Payment Summary (for installments) -->
        @if($payment->payment_plan === 'installment')
        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">Installment Summary</h4>
            </div>
            <div class="card-body">
                @php
                    $allInstallments = collect([$payment])->merge($relatedPayments)
                        ->where('payment_plan', 'installment')
                        ->sortBy('installment_number');
                    
                    $totalPaid = $allInstallments->where('status', 'successful')->sum('final_amount');
                    $totalAmount = $payment->program->price;
                @endphp

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Program Cost:</span>
                        <strong>₦{{ number_format($totalAmount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Paid:</span>
                        <strong class="text-success">₦{{ number_format($totalPaid, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Balance:</span>
                        <strong class="text-{{ $totalPaid >= $totalAmount ? 'success' : 'warning' }}">
                            ₦{{ number_format($totalAmount - $totalPaid, 2) }}
                        </strong>
                    </div>
                </div>

                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: {{ ($totalPaid / $totalAmount) * 100 }}%">
                        {{ number_format(($totalPaid / $totalAmount) * 100, 1) }}%
                    </div>
                </div>

                <div class="mt-3">
                    @foreach($allInstallments as $inst)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Installment {{ $inst->installment_number }}:</span>
                        <span class="badge badge-{{ $inst->status === 'successful' ? 'success' : 'warning' }}">
                            {{ ucfirst($inst->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .card-header, .btn, .sidebar, .header, .footer, .nk-sidebar, .breadcrumb {
        display: none !important;
    }
    
    .content-body {
        margin: 0 !important;
        padding: 20px !important;
    }
}
</style>
@endpush