@extends('emails.layout')

@section('content')
<h2>Account Status Update</h2>

<p>Hi <strong>{{ $user->first_name }}</strong>,</p>

<p>
    Your account status has been updated by an administrator.
</p>

<div class="info-box">
    <p>
        <strong>Previous Status:</strong> {{ ucfirst($oldStatus) }}<br>
        <strong>New Status:</strong> {{ ucfirst($user->status) }}<br>
        <strong>Updated On:</strong> {{ now()->format('M d, Y h:i A') }}
    </p>
</div>

@if($user->status === 'active')
<p>
    <strong>Great news!</strong> Your account is now active. You have full access to all platform features.
</p>

<div style="text-align: center;">
    <a href="{{ url('/login') }}" class="btn">Login to Your Account</a>
</div>

@elseif($user->status === 'suspended')
<div class="credentials-box">
    <p><strong>⚠️ Account Suspended</strong></p>
    <p style="margin-top: 8px;">
        Your account has been temporarily suspended. You will not be able to access the platform until this is resolved.
    </p>
</div>

<p>
    <strong>Reason:</strong> {{ $reason ?? 'Please contact support for more information.' }}
</p>

<p>
    If you believe this is a mistake or would like to discuss this further, please contact our support team.
</p>

@elseif($user->status === 'inactive')
<p>
    Your account has been set to inactive. To reactivate your account, please contact our support team.
</p>
@endif

<p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
    If you have any questions or concerns about this change, please don't hesitate to reach out to us at 
    <a href="mailto:support@gluper.com" style="color: #4f46e5;">support@gluper.com</a>
</p>

<p style="margin-top: 30px;">
    Best regards,<br>
    <strong>The G-Luper Team</strong>
</p>
@endsection