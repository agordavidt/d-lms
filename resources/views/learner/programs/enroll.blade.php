@extends('layouts.admin')

@section('title', $program->name)
@section('breadcrumb-parent', 'Programs')
@section('breadcrumb-current', $program->name)

@push('styles')
<style>
.program-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 0;
    border-radius: 8px;
    margin-bottom: 2rem;
}
.feature-icon {
    width: 50px;
    height: 50px;
    background-color: #f0f4ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 1rem;
}
.price-card {
    border: 2px solid #7571f9;
    transition: all 0.3s ease;
}
.price-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.price-card.selected {
    border-color: #7571f9;
    background-color: #f0f4ff;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Program Hero Section -->
        <div class="program-hero text-center">
            <div class="container">
                <h1 class="mb-3">{{ $program->name }}</h1>
                <p class="lead mb-0">{{ $program->description }}</p>
                <div class="mt-3">
                    <span class="badge badge-light mr-2">⏱️ {{ $program->duration }}</span>
                    <span class="badge badge-light mr-2">👥 {{ $program->enrollments_count }} enrolled</span>
                    @if($program->discount_percentage > 0)
                        <span class="badge badge-warning">💰 {{ $program->discount_percentage }}% off one-time payment</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Program Details (60%) -->
    <div class="col-lg-7">
        <!-- Overview -->
        @if($program->overview)
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-3">Program Overview</h4>
                <p>{{ $program->overview }}</p>
            </div>
        </div>
        @endif

        <!-- What You'll Learn -->
        @if($program->features && count($program->features) > 0)
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-4">What You'll Learn</h4>
                <div class="row">
                    @foreach($program->features as $feature)
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <span style="color: #28a745; font-size: 18px; margin-right: 10px;">✓</span>
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
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-4">Requirements</h4>
                <ul class="pl-3">
                    @foreach($program->requirements as $requirement)
                        <li class="mb-2">{{ $requirement }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <!-- Enrollment Form (40%) -->
    <div class="col-lg-5">
        <div class="card" style="position: sticky; top: 20px;">
            <div class="card-body">
                <h4 class="mb-4">Enroll in This Program</h4>

                <form action="{{ route('payment.initiate') }}" method="POST" id="enrollmentForm">
                    @csrf
                    <input type="hidden" name="program_id" value="{{ $program->id }}">

                    <!-- Cohort Selection -->
                    <div class="form-group">
                        <label>Select Your Cohort <span class="text-danger">*</span></label>
                        <select class="form-control" name="cohort_id" required>
                            <option value="">Choose a cohort...</option>
                            @foreach($program->cohorts as $cohort)
                                <option value="{{ $cohort->id }}">
                                    {{ $cohort->name }} - 
                                    Starts {{ $cohort->start_date->format('M d, Y') }}
                                    ({{ $cohort->spots_remaining }} spots left)
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Choose when you'd like to start</small>
                    </div>

                    <hr>

                    <!-- Payment Options -->
                    <div class="form-group">
                        <label class="mb-3">Choose Payment Option <span class="text-danger">*</span></label>
                        
                        <!-- One-Time Payment -->
                        <div class="price-card p-3 mb-3 rounded" onclick="selectPaymentPlan('one-time')" style="cursor: pointer;">
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="oneTime" 
                                       name="payment_plan" value="one-time" required>
                                <label class="custom-control-label" for="oneTime">
                                    <strong>Pay in Full</strong>
                                    @if($program->discount_percentage > 0)
                                        <span class="badge badge-success ml-2">{{ $program->discount_percentage }}% OFF</span>
                                    @endif
                                </label>
                            </div>
                            <div class="mt-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($program->discount_percentage > 0)
                                            <del class="text-muted">₦{{ number_format($program->price, 2) }}</del>
                                        @endif
                                        <h3 class="mb-0" style="color: #7571f9;">₦{{ number_format($program->discounted_price, 2) }}</h3>
                                        <small class="text-muted">One-time payment</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Installment Payment -->
                        <div class="price-card p-3 rounded" onclick="selectPaymentPlan('installment')" style="cursor: pointer;">
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="installment" 
                                       name="payment_plan" value="installment" required>
                                <label class="custom-control-label" for="installment">
                                    <strong>Pay in 2 Installments</strong>
                                </label>
                            </div>
                            <div class="mt-2">
                                <h3 class="mb-0" style="color: #7571f9;">₦{{ number_format($program->installment_amount, 2) }}</h3>
                                <small class="text-muted">First payment, then ₦{{ number_format($program->installment_amount, 2) }} later</small>
                            </div>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div class="p-3 mb-3" style="background-color: #f8f9fa; border-radius: 4px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Program Price:</span>
                            <strong>₦{{ number_format($program->price, 2) }}</strong>
                        </div>
                        <div id="discountRow" style="display: none;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-success">Discount ({{ $program->discount_percentage }}%):</span>
                                <strong class="text-success">-₦{{ number_format(($program->price * $program->discount_percentage) / 100, 2) }}</strong>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>You Pay Today:</strong>
                            <strong id="totalAmount" style="color: #7571f9; font-size: 24px;">₦0.00</strong>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Proceed to Payment
                    </button>

                    <small class="text-muted d-block text-center mt-3">
                        Secure payment powered by Flutterwave
                    </small>
                </form>
            </div>
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
    document.querySelectorAll('.price-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    
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