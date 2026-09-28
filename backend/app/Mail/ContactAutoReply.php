<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactAutoReply extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Thanks for your message');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.contact-auto-reply', with: [
            'message' => $this->contactMessage,
            'responseTime' => Setting::current()->response_time ?: 'within one working day',
        ]);
    }
}
