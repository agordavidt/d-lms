<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class MentorAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $resetUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        
        // Generate password reset token and URL
        $token = Password::broker()->createToken($user);
        $this->resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Mentor Account Has Been Created',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mentor-created',
            with: [
                'title' => 'Mentor Account Created',
            ]
        );
    }
}