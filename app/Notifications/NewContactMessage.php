<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails the owner when someone uses the contact form.
 */
class NewContactMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $contactMessage) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->contactMessage;

        return (new MailMessage)
            ->subject("New message from {$message->name} · {$message->topic->getLabel()}")
            ->replyTo($message->email, $message->name)
            ->greeting("{$message->name} wrote:")
            ->line($message->message)
            ->line("Reply to: {$message->email}")
            ->action('Open in the panel', url("/admin/contact-messages/{$message->id}"));
    }
}
