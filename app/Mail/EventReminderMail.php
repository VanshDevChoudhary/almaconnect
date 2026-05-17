<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: '.$this->event->title.' is tomorrow',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p>This is a reminder that <strong>'
                .e($this->event->title)
                .'</strong> starts on '
                .$this->event->starts_at->format('l, F j, Y \a\t g:i A')
                .'.</p><p>'
                .e($this->event->location ?: $this->event->online_url ?: 'Details on AlmaConnect')
                .'</p>',
        );
    }
}
