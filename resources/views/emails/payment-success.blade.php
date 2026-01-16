@extends('emails.layout')

@section('content')
<h2>Payment Successful! ✅</h2>

<p>Hi <strong>{{ $payment->user->first_name }}</strong>,</p>

<p>
    Thank you! Your payment has been received and successfully processed. 
    You're all set to begin your learning journey with us.
</p>

<div class="info-box">
    <p><strong>Payment Details:</strong></p>
    <p>
        <strong>Transaction ID:</strong> {{ $payment->transaction_id }}<br>
        <strong>Amount Paid:</strong> ₦{{ number_format($payment->amount, 2) }}<br>
        <strong>Payment Date:</strong> {{ $payment->created_at->format('M d, Y h:i A') }}<br>
        <strong>Payment Method:</strong> {{ ucfirst($payment->payment_method) }}<br>
        <strong>Status:</strong> {{ ucfirst($payment->status) }}
    </p>
</div>

@if(isset($payment->program))
<p style="margin-top: 30px;">
    <strong>Program Enrolled:</strong>
</p>

<div class="info-box">
    <p>
        <strong>{{ $payment->program->name }}</strong><br>
        Duration: {{ $payment->program->duration }}<br>
        Start Date: {{ $payment->cohort->start_date->format('M d, Y') }}
    </p>
</div>
@endif

@if($payment->payment_plan === 'installment' && $payment->installment_status !== 'completed')
<div class="credentials-box">
    <p><strong>⚠️ Installment Payment</strong></p>
    <p style="margin-top: 8px;">
        You've paid <strong>{{ $payment->installment_number }}</strong> of 2 installments.<br>
        Remaining Balance: <strong>₦{{ number_format($payment->remaining_balance, 2) }}</strong>
    </p>
</div>
@endif

<div style="text-align: center;">
    <a href="{{ url('/learner/dashboard') }}" class="btn">Go to Dashboard</a>
</div>

<p style="margin-top: 30px;">
    A receipt has been generated for your records. You can download it anytime from your dashboard.
</p>

<p>
    If you have any questions about your payment or enrollment, feel free to contact our support team.
</p>

<p style="margin-top: 30px;">
    Happy Learning!<br>
    <strong>The G-Luper Team</strong>
</p>
@endsection