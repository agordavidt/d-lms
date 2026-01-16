<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $oldStatus;
    public ?string $reason;

    public function __construct(User $user, string $oldStatus, ?string $reason = null)
    {
        $this->user = $user;
        $this->oldStatus = $oldStatus;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Status Update - G-Luper Learning',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-status-changed',
            with: [
                'title' => 'Account Status Update',
            ]
        );
    }
}