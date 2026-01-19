@extends('layouts.admin')

@section('title', 'Payment Pending')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Pending Enrollment')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size: 80px; margin-bottom: 2rem;">⏳</div>
                <h3 class="mb-3">Payment Pending</h3>
                <p class="text-muted mb-4">
                    You're almost there! Complete your payment to start learning.
                </p>
            </div>
        </div>

        <!-- Enrollment Details -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-4">Enrollment Details</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Program</p>
                        <h6>{{ $pendingEnrollment->program->name }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Cohort</p>
                        <h6>{{ $pendingEnrollment->cohort->name }}</h6>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Enrollment Number</p>
                        <h6>{{ $pendingEnrollment->enrollment_number }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Enrolled On</p>
                        <h6>{{ $pendingEnrollment->enrolled_at->format('M d, Y') }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-4">Payment Information</h5>
                
                @php
                    $lastPayment = $pendingEnrollment->payments()->latest()->first();
                @endphp

                @if($lastPayment)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Program Price:</span>
                            <strong>₦{{ number_format($pendingEnrollment->program->price, 2) }}</strong>
                        </div>
                        
                        @if($lastPayment->payment_plan === 'installment')
                            <div class="d-flex justify-content-between mb-2">
                                <span>Payment Plan:</span>
                                <strong>2 Installments</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Amount to Pay Now:</span>
                                <strong style="color: #7571f9;">₦{{ number_format($lastPayment->final_amount, 2) }}</strong>
                            </div>
                        @else
                            @if($lastPayment->discount_amount > 0)
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <strong>-₦{{ number_format($lastPayment->discount_amount, 2) }}</strong>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Amount:</span>
                                <strong style="color: #7571f9;">₦{{ number_format($lastPayment->final_amount, 2) }}</strong>
                            </div>
                        @endif
                    </div>

                    <hr>

                    @if($lastPayment->status === 'pending')
                        <div class="alert alert-warning">
                            <strong>Payment Status:</strong> Pending<br>
                            <small>Your payment is being processed. This may take a few minutes.</small>
                        </div>
                    @elseif($lastPayment->status === 'failed')
                        <div class="alert alert-danger">
                            <strong>Payment Failed</strong><br>
                            <small>There was an issue processing your payment. Please try again.</small>
                        </div>
                    @endif

                    <form action="{{ route('payment.initiate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="program_id" value="{{ $pendingEnrollment->program_id }}">
                        <input type="hidden" name="cohort_id" value="{{ $pendingEnrollment->cohort_id }}">
                        <input type="hidden" name="payment_plan" value="{{ $lastPayment->payment_plan }}">
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            Complete Payment
                        </button>
                    </form>
                @else
                    <div class="alert alert-info">
                        <strong>No payment record found.</strong><br>
                        Please contact support for assistance.
                    </div>
                @endif
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('learner.dashboard') }}" class="btn btn-outline-primary">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection