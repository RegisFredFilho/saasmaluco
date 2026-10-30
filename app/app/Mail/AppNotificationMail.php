<?php

namespace App\Mail;

use App\Models\AppNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AppNotification $notification)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.config('app.name').'] '.$this->notification->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.app-notification',
            with: [
                'title' => $this->notification->title,
                'body' => $this->notification->body,
                'actionUrl' => $this->notification->url(),
            ],
        );
    }
}
