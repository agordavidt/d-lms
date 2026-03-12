<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $verificationUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verificationUrl = $this->buildVerificationUrl($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify your G-Luper email address',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
            with: [
                'user'            => $this->user,
                'verificationUrl' => $this->verificationUrl,
                'expiresInMinutes' => Config::get('auth.verification.expire', 60),
            ]
        );
    }

    /**
     * Build the signed verification URL — replicates what Laravel's
     * built-in VerifyEmail notification does internally.
     */
    protected function buildVerificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }
}