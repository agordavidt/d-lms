@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('learner.programs.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left mr-1"></i> Back to Programs
            </a>
            
            <div class="row align-items-center">
                <div class="col-lg-8">
                    @if($enrollment)
                    <span class="badge badge-success mb-3">
                        <i class="bi bi-check-circle mr-1"></i> You are enrolled
                    </span>
                    @endif
                    <h1 class="h2 font-weight-bold mb-3">{{ $program->name }}</h1>
                    <p class="lead text-muted mb-4">{{ $program->description }}</p>
                </div>
                
                <div class="col-lg-4 text-lg-right">
                    @if(!$enrollment)
                        <!-- Show enrollment form button -->
                        <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#enrollModal">
                            <i class="bi bi-plus-circle mr-2"></i> Enroll Now
                        </button>
                    @else
                        <!-- Already enrolled - show dashboard link -->
                        <a href="{{ route('learner.learning.index') }}" class="btn btn-success btn-lg">
                            <i class="bi bi-book mr-2"></i> Go to Learning
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Program Details -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Overview Card -->
            @if($program->overview)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="font-weight-bold mb-3">Program Overview</h4>
                    <p class="text-muted">{{ $program->overview }}</p>
                </div>
            </div>
            @endif

            <!-- What You'll Learn -->
            @if($program->features && count($program->features) > 0)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="font-weight-bold mb-4">What You'll Learn</h4>
                    <div class="row">
                        @foreach($program->features as $feature)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success mr-2" style="font-size: 1.2rem;"></i>
                                <p class="mb-0">{{ $feature }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Requirements -->
            @if($program->requirements && count($program->requirements) > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="font-weight-bold mb-4">Requirements</h4>
                    <ul class="pl-3">
                        @foreach($program->requirements as $requirement)
                            <li class="mb-2 text-muted">{{ $requirement }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 20px;">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-4">Program Info</h5>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block mb-1">Duration</small>
                        <strong>{{ $program->duration }}</strong>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block mb-1">Price</small>
                        <strong>₦{{ number_format($program->price, 2) }}</strong>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block mb-1">Students Enrolled</small>
                        <strong>{{ $program->enrollments_count }}</strong>
                    </div>
                    
                    @if($program->cohorts->count() > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Available Cohorts</small>
                        @foreach($program->cohorts as $cohort)
                        <div class="badge badge-primary mb-2 d-block text-left p-2">
                            {{ $cohort->name }}<br>
                            <small>Starts: {{ $cohort->start_date->format('M d, Y') }}</small>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enrollment Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" role="dialog" aria-labelledby="enrollModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="enrollModalLabel">
                    <i class="bi bi-clipboard-check mr-2"></i> Enroll in {{ $program->name }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form action="{{ route('payment.initiate') }}" method="POST" id="enrollmentForm">
                @csrf
                <input type="hidden" name="program_id" value="{{ $program->id }}">
                
                <div class="modal-body">
                    <!-- Cohort Selection -->
                    <div class="form-group">
                        <label class="font-weight-bold">Select Your Cohort <span class="text-danger">*</span></label>
                        <select class="form-control form-control-lg" name="cohort_id" required>
                            <option value="">Choose when you want to start...</option>
                            @foreach($program->cohorts as $cohort)
                                <option value="{{ $cohort->id }}">
                                    {{ $cohort->name }} - 
                                    Starts {{ $cohort->start_date->format('M d, Y') }}
                                    @if($cohort->max_students - $cohort->enrolled_count > 0)
                                        ({{ $cohort->max_students - $cohort->enrolled_count }} spots left)
                                    @else
                                        (Full)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="my-4">

                    <!-- Payment Plan Selection -->
                    <div class="form-group">
                        <label class="font-weight-bold mb-3">Choose Payment Option <span class="text-danger">*</span></label>
                        
                        <!-- Full Payment Option -->
                        <div class="card mb-3 payment-option" onclick="selectPaymentPlan('one-time')" style="cursor: pointer; border: 2px solid #e0e0e0;">
                            <div class="card-body">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="oneTime" 
                                           name="payment_plan" value="one-time" required>
                                    <label class="custom-control-label font-weight-bold" for="oneTime">
                                        Pay in Full
                                        @if($program->discount_percentage > 0)
                                            <span class="badge badge-success ml-2">{{ $program->discount_percentage }}% OFF</span>
                                        @endif
                                    </label>
                                </div>
                                <div class="mt-2">
                                    @if($program->discount_percentage > 0)
                                        <del class="text-muted">₦{{ number_format($program->price, 2) }}</del>
                                    @endif
                                    <h3 class="mb-0 text-primary">₦{{ number_format($program->discounted_price, 2) }}</h3>
                                    <small class="text-muted">One-time payment</small>
                                </div>
                            </div>
                        </div>

                        <!-- Installment Payment Option -->
                        <div class="card payment-option" onclick="selectPaymentPlan('installment')" style="cursor: pointer; border: 2px solid #e0e0e0;">
                            <div class="card-body">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="installment" 
                                           name="payment_plan" value="installment" required>
                                    <label class="custom-control-label font-weight-bold" for="installment">
                                        Pay in 2 Installments (50/50)
                                    </label>
                                </div>
                                <div class="mt-2">
                                    <h3 class="mb-0 text-primary">₦{{ number_format($program->installment_amount, 2) }}</h3>
                                    <small class="text-muted">First payment, then ₦{{ number_format($program->installment_amount, 2) }} later</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div class="alert alert-light border mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Program Price:</span>
                            <strong>₦{{ number_format($program->price, 2) }}</strong>
                        </div>
                        <div id="discountRow" style="display: none;">
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Discount ({{ $program->discount_percentage }}%):</span>
                                <strong>-₦{{ number_format(($program->price * $program->discount_percentage) / 100, 2) }}</strong>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong class="h5 mb-0">You Pay Today:</strong>
                            <strong id="totalAmount" class="h4 mb-0 text-primary">₦0.00</strong>
                        </div>
                    </div>

                    <div class="text-center text-muted mt-3">
                        <small><i class="bi bi-shield-check mr-1"></i> Secure payment simulation enabled</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-credit-card mr-2"></i> Proceed to Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectPaymentPlan(plan) {
    // Update radio selection
    if (plan === 'one-time') {
        document.getElementById('oneTime').checked = true;
    } else {
        document.getElementById('installment').checked = true;
    }
    
    // Update visual selection
    document.querySelectorAll('.payment-option').forEach(card => {
        card.style.borderColor = '#e0e0e0';
        card.style.backgroundColor = 'white';
    });
    event.currentTarget.style.borderColor = '#007bff';
    event.currentTarget.style.backgroundColor = '#f0f8ff';
    
    // Update price display
    updatePriceSummary(plan);
}

function updatePriceSummary(plan) {
    const fullPrice = {{ $program->price }};
    const discountedPrice = {{ $program->discounted_price }};
    const installmentAmount = {{ $program->installment_amount }};
    const discountRow = document.getElementById('discountRow');
    const totalAmount = document.getElementById('totalAmount');
    
    if (plan === 'one-time') {
        discountRow.style.display = 'block';
        totalAmount.textContent = '₦' + discountedPrice.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    } else {
        discountRow.style.display = 'none';
        totalAmount.textContent = '₦' + installmentAmount.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

// Update price when payment plan changes
document.addEventListener('DOMContentLoaded', function() {
    const paymentPlanRadios = document.querySelectorAll('input[name="payment_plan"]');
    paymentPlanRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            updatePriceSummary(this.value);
        });
    });
});
</script>
@endpush