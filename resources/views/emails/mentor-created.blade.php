@extends('emails.layout')

@section('content')
<h2>Your Mentor Account Has Been Created 🎓</h2>

<p>Hi <strong>{{ $user->first_name }}</strong>,</p>

<p>
    An administrator has created a mentor account for you on G-Luper Learning Management System. 
    We're thrilled to have you join our team of expert instructors!
</p>

<div class="info-box">
    <p><strong>Your Account Details:</strong></p>
    <p>
        <strong>Email:</strong> {{ $user->email }}<br>
        <strong>Role:</strong> Mentor<br>
        <strong>Status:</strong> {{ ucfirst($user->status) }}
    </p>
</div>

<p>
    For security reasons, you need to set up your password before you can access your account.
</p>

<div style="text-align: center;">
    <a href="{{ $resetUrl }}" class="btn">Set Your Password</a>
</div>

<p style="margin-top: 30px;">
    <strong>As a mentor, you'll be able to:</strong>
</p>

<div class="info-box">
    <p>
        ✓ Schedule and conduct live sessions via Google Meet<br>
        ✓ Manage your cohorts and students<br>
        ✓ Upload course materials and resources<br>
        ✓ Track student progress and grades<br>
        ✓ Communicate with students through our platform
    </p>
</div>

<p>
    If you didn't expect this email or believe this is a mistake, please contact our support team immediately.
</p>

<p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px;">
    <strong>Note:</strong> This password reset link will expire in {{ config('auth.passwords.users.expire') }} minutes. 
    If the link expires, you can request a new one from the login page.
</p>

<p style="margin-top: 30px;">
    Looking forward to working with you!<br>
    <strong>The G-Luper Team</strong>
</p>
@endsection