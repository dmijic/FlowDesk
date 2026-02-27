<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ServiceRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('FlowDesk zahtjev zaprimljen')
            ->line("Zahtjev #{$this->request->id} je uspješno poslan na odobravanje.")
            ->line('Status: In Review');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'request_submitted',
            'request_id' => $this->request->id,
            'title' => $this->request->title,
            'message' => 'Zahtjev je poslan na odobravanje.',
        ];
    }
}
