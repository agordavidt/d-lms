@extends('emails.layout')

@section('content')
<h2>Welcome to G-Luper Learning! 🎉</h2>

<p>Hi <strong>{{ $user->first_name }}</strong>,</p>

<p>
    Thank you for joining G-Luper Learning! We're excited to have you as part of our growing community of learners 
    who are committed to mastering tech skills and advancing their careers.
</p>

<p>Your account has been successfully created and is ready to use.</p>

<div class="info-box">
    <p><strong>What's Next?</strong></p>
    <p>
        • Browse our available programs and cohorts<br>
        • Complete your profile to personalize your experience<br>
        • Join our WhatsApp community for updates<br>
        • Attend live sessions via Google Meet
    </p>
</div>

<div style="text-align: center;">
    <a href="{{ url('/login') }}" class="btn">Get Started</a>
</div>

<p style="margin-top: 30px;">
    <strong>Your Login Credentials:</strong>
</p>

<div class="credentials-box">
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p style="margin-top: 8px;"><strong>Password:</strong> The one you created during registration</p>
</div>

<p>
    If you have any questions or need assistance, don't hesitate to reach out to our support team.
</p>

<p style="margin-top: 30px;">
    Welcome aboard!<br>
    <strong>The G-Luper Team</strong>
</p>
@endsection