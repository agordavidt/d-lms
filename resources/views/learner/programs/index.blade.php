@extends('layouts.admin')

@section('title', 'Programs')
@section('breadcrumb-parent', 'Home')
@section('breadcrumb-current', 'Programs')

@push('styles')
<style>
    /* Programs Grid */
    .programs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .program-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 24px;
        background: #fff;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    .program-card:hover {
        border-color: #7571f9;
        box-shadow: 0 2px 8px rgba(117, 113, 249, 0.1);
    }
    
    .program-name {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }
    
    .program-details {
        display: flex;
        gap: 32px;
        margin-bottom: 16px;
        color: #666;
        font-size: 14px;
    }
    
    .program-detail-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .program-price {
        font-size: 24px;
        font-weight: 700;
        color: #7571f9;
        margin-bottom: 16px;
        margin-top: auto;
    }
    
    .enroll-btn {
        background: #7571f9;
        color: white;
        border: none;
        padding: 12px 32px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 100%;
    }
    
    .enroll-btn:hover {
        background: #5f5bd1;
    }
    
    .modal-content {
        border-radius: 8px;
        border: none;
    }
    
    .payment-option {
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .payment-option:hover {
        border-color: #7571f9;
    }
    
    .payment-option.selected {
        border-color: #7571f9;
        background: #f8f8ff;
    }
    
    .payment-option-title {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 4px;
    }
    
    .payment-option-desc {
        font-size: 13px;
        color: #666;
        margin-bottom: 8px;
    }
    
    .payment-option-amount {
        font-size: 18px;
        font-weight: 700;
        color: #7571f9;
    }
    
    .discount-badge {
        background: #4caf50;
        color: white;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .programs-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (min-width: 769px) and (max-width: 1024px) {
        .programs-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Available Programs</h4>
                
                @if($programs->count() > 0)
                    <div class="programs-grid">
                        @foreach($programs as $program)
                            <div class="program-card">
                                <div class="program-name">{{ $program->name }}</div>
                                
                                <div class="program-details">
                                    <div class="program-detail-item">
                                        <i class="icon-clock"></i>
                                        <span>{{ $program->duration }}</span>
                                    </div>
                                    <div class="program-detail-item">
                                        <i class="icon-calendar"></i>
                                        <span>Starts: {{ $program->cohorts->first()->start_date->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                
                                <div class="program-price">₦{{ number_format($program->price, 2) }}</div>
                                
                                <button class="enroll-btn" onclick="openEnrollModal({{ $program->id }}, '{{ addslashes($program->name) }}', {{ $program->price }})">
                                    Enroll Now
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="icon-book-open"></i>
                        <h5>No Programs Available</h5>
                        <p>Check back soon for new programs.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Enrollment Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #e0e0e0;">
                <h5 class="modal-title" id="programName"></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <p style="margin-bottom: 20px; color: #666;">Choose your payment plan:</p>
                
                <div class="payment-option" onclick="selectPaymentPlan('one-time')" id="fullPayment">
                    <div class="payment-option-title">Full Payment</div>
                    <div class="payment-option-desc">Pay once and get started immediately</div>
                    <div class="payment-option-amount">
                        <span id="fullAmount"></span>
                        <span class="discount-badge">Save 10%</span>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selectPaymentPlan('installment')" id="installmentPayment">
                    <div class="payment-option-title">Installment Payment</div>
                    <div class="payment-option-desc">Split into 2 payments</div>
                    <div class="payment-option-amount">
                        <span id="installmentAmount"></span> <span style="font-size: 14px; color: #666;">× 2</span>
                    </div>
                </div>
                
                <input type="hidden" id="selectedPlan" value="">
                <input type="hidden" id="selectedProgramId" value="">
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e0e0e0; padding: 16px 24px;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="proceedToPayment()" style="background: #7571f9; border: none;">
                    Proceed to Payment
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedProgramPrice = 0;
let selectedCohortId = null;

function openEnrollModal(programId, programName, price) {
    selectedProgramPrice = price;
    
    // Calculate amounts
    const discountedPrice = price * 0.9; // 10% discount
    const installmentAmount = price / 2;
    
    // Update modal content
    document.getElementById('programName').textContent = programName;
    document.getElementById('fullAmount').textContent = '₦' + discountedPrice.toLocaleString('en-NG', {minimumFractionDigits: 2});
    document.getElementById('installmentAmount').textContent = '₦' + installmentAmount.toLocaleString('en-NG', {minimumFractionDigits: 2});
    document.getElementById('selectedProgramId').value = programId;
    
    // Reset selection
    document.getElementById('selectedPlan').value = '';
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    
    // Show modal
    $('#enrollModal').modal('show');
}

function selectPaymentPlan(plan) {
    document.getElementById('selectedPlan').value = plan;
    
    // Update UI
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    
    if (plan === 'one-time') {
        document.getElementById('fullPayment').classList.add('selected');
    } else {
        document.getElementById('installmentPayment').classList.add('selected');
    }
}

function proceedToPayment() {
    const plan = document.getElementById('selectedPlan').value;
    const programId = document.getElementById('selectedProgramId').value;
    
    if (!plan) {
        toastr.warning('Please select a payment plan');
        return;
    }
    
    // Show loading
    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Processing...';
    
    // Submit enrollment
    fetch(`/learner/programs/${programId}/enroll`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            payment_plan: plan
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            
            // Create a form and submit it to payment route
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/payment/initiate';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);
            
            // Add program_id
            const programInput = document.createElement('input');
            programInput.type = 'hidden';
            programInput.name = 'program_id';
            programInput.value = programId;
            form.appendChild(programInput);
            
            // Add cohort_id from the enrollment response
            const cohortInput = document.createElement('input');
            cohortInput.type = 'hidden';
            cohortInput.name = 'cohort_id';
            cohortInput.value = data.cohort_id;
            form.appendChild(cohortInput);
            
            // Add payment_plan
            const planInput = document.createElement('input');
            planInput.type = 'hidden';
            planInput.name = 'payment_plan';
            planInput.value = plan;
            form.appendChild(planInput);
            
            document.body.appendChild(form);
            form.submit();
        } else {
            toastr.error(data.message);
            btn.disabled = false;
            btn.textContent = originalText;
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
        btn.disabled = false;
        btn.textContent = originalText;
    });
}
</script>
@endpush