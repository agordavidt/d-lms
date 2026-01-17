@extends('layouts.admin')

@section('title', 'Enroll in ' . $program->name)
@section('breadcrumb-parent', 'Programs')
@section('breadcrumb-current', 'Enrollment')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Program Summary Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <a href="{{ route('learner.programs.show', $program->slug) }}" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="icon-arrow-left mr-1"></i> Back to Program
                </a>
                
                <h2 class="h4 font-weight-bold mb-3">Complete Your Enrollment</h2>
                <p class="text-muted mb-4">You're enrolling in <strong>{{ $program->name }}</strong></p>

                <div class="alert alert-info border-0">
                    <div class="d-flex">
                        <i class="icon-info-alt mr-3 mt-1"></i>
                        <div>
                            <strong>What happens next?</strong>
                            <ul class="mb-0 mt-2">
                                <li>Select your preferred cohort and payment plan</li>
                                <li>Complete payment securely</li>
                                <li>Get instant access to program materials and WhatsApp community</li>
                                <li>Join live sessions starting on your cohort's start date</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollment Form -->
        <form action="{{ route('payment.initiate') }}" method="POST" id="enrollmentForm">
            @csrf
            <input type="hidden" name="program_id" value="{{ $program->id }}">

            <!-- Select Cohort -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="font-weight-bold mb-4">
                        <i class="icon-calendar text-primary mr-2"></i>Select Your Cohort
                    </h5>

                    @if($program->cohorts->count() > 0)
                    <div class="row">
                        @foreach($program->cohorts as $cohort)
                        <div class="col-md-12 mb-3">
                            <div class="custom-control custom-radio border rounded p-3 cohort-option">
                                <input type="radio" 
                                    class="custom-control-input" 
                                    id="cohort{{ $cohort->id }}" 
                                    name="cohort_id" 
                                    value="{{ $cohort->id }}"
                                    @if(!$cohort->canEnroll()) disabled @endif
                                    required>
                                <label class="custom-control-label w-100" for="cohort{{ $cohort->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong class="d-block mb-2">{{ $cohort->name }}</strong>
                                            <span class="text-muted small d-block mb-1">
                                                <i class="icon-calendar mr-1"></i>
                                                Starts: {{ $cohort->start_date->format('M d, Y') }}
                                            </span>
                                            <span class="text-muted small d-block">
                                                <i class="icon-people mr-1"></i>
                                                {{ $cohort->spots_remaining }} spots remaining
                                            </span>
                                        </div>
                                        <div>
                                            @if($cohort->canEnroll())
                                            <span class="badge badge-success">Available</span>
                                            @else
                                            <span class="badge badge-secondary">Full</span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="icon-info mr-2"></i>
                        No cohorts available for enrollment at this time. Please check back later.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Plan Selection -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="font-weight-bold mb-4">
                        <i class="icon-credit-card text-primary mr-2"></i>Choose Payment Plan
                    </h5>

                    <div class="row">
                        <!-- One-Time Payment -->
                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-radio border rounded p-4 payment-option position-relative">
                                <input type="radio" 
                                    class="custom-control-input" 
                                    id="oneTime" 
                                    name="payment_plan" 
                                    value="one-time"
                                    required>
                                <label class="custom-control-label w-100" for="oneTime">
                                    @if($program->discount_percentage > 0)
                                    <span class="badge badge-success position-absolute" style="top: 10px; right: 10px;">
                                        Save {{ $program->discount_percentage }}%
                                    </span>
                                    @endif
                                    
                                    <strong class="d-block mb-3">One-Time Payment</strong>
                                    
                                    <div class="mb-3">
                                        @if($program->discount_percentage > 0)
                                        <small class="text-muted text-decoration-line-through d-block">
                                            ₦{{ number_format($program->price, 2) }}
                                        </small>
                                        <h3 class="text-success mb-0">
                                            ₦{{ number_format($program->discounted_price, 2) }}
                                        </h3>
                                        @else
                                        <h3 class="text-primary mb-0">
                                            ₦{{ number_format($program->price, 2) }}
                                        </h3>
                                        @endif
                                    </div>

                                    <ul class="list-unstyled text-muted small mb-0">
                                        <li class="mb-2">
                                            <i class="icon-check text-success mr-2"></i>Pay once, access everything
                                        </li>
                                        @if($program->discount_percentage > 0)
                                        <li class="mb-2">
                                            <i class="icon-check text-success mr-2"></i>
                                            Save ₦{{ number_format($program->price - $program->discounted_price, 2) }}
                                        </li>
                                        @endif
                                        <li>
                                            <i class="icon-check text-success mr-2"></i>Recommended option
                                        </li>
                                    </ul>
                                </label>
                            </div>
                        </div>

                        <!-- Installment Payment -->
                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-radio border rounded p-4 payment-option">
                                <input type="radio" 
                                    class="custom-control-input" 
                                    id="installment" 
                                    name="payment_plan" 
                                    value="installment"
                                    required>
                                <label class="custom-control-label w-100" for="installment">
                                    <strong class="d-block mb-3">Installment Plan (50/50)</strong>
                                    
                                    <div class="mb-3">
                                        <h3 class="text-primary mb-0">
                                            ₦{{ number_format($program->installment_amount, 2) }}
                                        </h3>
                                        <small class="text-muted">x 2 payments</small>
                                    </div>

                                    <ul class="list-unstyled text-muted small mb-0">
                                        <li class="mb-2">
                                            <i class="icon-check text-success mr-2"></i>Pay 50% now
                                        </li>
                                        <li class="mb-2">
                                            <i class="icon-check text-success mr-2"></i>Pay 50% later (before completion)
                                        </li>
                                        <li>
                                            <i class="icon-info text-primary mr-2"></i>Total: ₦{{ number_format($program->price, 2) }}
                                        </li>
                                    </ul>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="card border-0 shadow-sm mb-4" id="paymentSummary" style="display: none;">
                <div class="card-body p-4 bg-light">
                    <h5 class="font-weight-bold mb-4">Payment Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Program Fee:</span>
                        <span class="font-weight-semibold" id="programFee">₦{{ number_format($program->price, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-3" id="discountRow" style="display: none;">
                        <span class="text-success">Discount ({{ $program->discount_percentage }}%):</span>
                        <span class="text-success font-weight-semibold" id="discountAmount">
                            -₦{{ number_format($program->price - $program->discounted_price, 2) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3" id="installmentNote" style="display: none;">
                        <span class="text-muted small">First Installment (50%):</span>
                        <span class="font-weight-semibold">₦{{ number_format($program->installment_amount, 2) }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 mb-0">Amount Due Now:</span>
                        <span class="h4 text-primary font-weight-bold mb-0" id="totalAmount">₦0.00</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submitBtn" disabled>
                        <i class="icon-lock mr-2"></i>Proceed to Secure Payment
                    </button>
                    
                    <p class="text-center text-muted small mt-3 mb-0">
                        <i class="icon-shield mr-1"></i>
                        Your payment is secure and encrypted
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('enrollmentForm');
    const cohortRadios = document.querySelectorAll('input[name="cohort_id"]');
    const paymentRadios = document.querySelectorAll('input[name="payment_plan"]');
    const submitBtn = document.getElementById('submitBtn');
    const paymentSummary = document.getElementById('paymentSummary');
    const discountRow = document.getElementById('discountRow');
    const installmentNote = document.getElementById('installmentNote');
    const totalAmount = document.getElementById('totalAmount');

    const programPrice = {{ $program->price }};
    const discountedPrice = {{ $program->discounted_price }};
    const installmentAmount = {{ $program->installment_amount }};

    function updatePaymentSummary() {
        const cohortSelected = document.querySelector('input[name="cohort_id"]:checked');
        const paymentSelected = document.querySelector('input[name="payment_plan"]:checked');

        if (cohortSelected && paymentSelected) {
            paymentSummary.style.display = 'block';
            submitBtn.disabled = false;

            if (paymentSelected.value === 'one-time') {
                discountRow.style.display = 'flex';
                installmentNote.style.display = 'none';
                totalAmount.textContent = '₦' + discountedPrice.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                discountRow.style.display = 'none';
                installmentNote.style.display = 'flex';
                totalAmount.textContent = '₦' + installmentAmount.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        } else {
            paymentSummary.style.display = 'none';
            submitBtn.disabled = true;
        }
    }

    cohortRadios.forEach(radio => radio.addEventListener('change', updatePaymentSummary));
    paymentRadios.forEach(radio => radio.addEventListener('change', updatePaymentSummary));

    // Add visual feedback for selected options
    document.querySelectorAll('.cohort-option, .payment-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (!radio.disabled) {
                radio.checked = true;
                updatePaymentSummary();
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .cohort-option:hover:not(:has(input:disabled)),
    .payment-option:hover {
        background-color: #f8f9fa;
        cursor: pointer;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }

    .cohort-option:has(input:checked),
    .payment-option:has(input:checked) {
        border-color: #7571f9 !important;
        background-color: #f0f0ff;
    }

    .cohort-option:has(input:disabled) {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
@endpush