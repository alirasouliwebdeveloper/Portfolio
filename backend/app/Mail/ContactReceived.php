<?php

namespace App\Mail;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notification to the site owner. Files are linked from the admin, never attached. */
class ContactReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New message from '.$this->contactMessage->name,
            replyTo: [new Address($this->contactMessage->email, $this->contactMessage->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.contact-received', with: [
            'message' => $this->contactMessage,
            'adminUrl' => ContactMessageResource::getUrl('view', ['record' => $this->contactMessage]),
            'fileCount' => $this->contactMessage->uploads()->count(),
        ]);
    }
}
