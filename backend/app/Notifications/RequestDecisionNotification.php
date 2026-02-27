<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ServiceRequest $request,
        private readonly string $decision,
        private readonly ?string $comment
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("FlowDesk zahtjev {$this->decision}")
            ->line("Zahtjev #{$this->request->id} je {$this->decision}.");

        if ($this->comment !== null) {
            $mail->line("Komentar: {$this->comment}");
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'request_decision',
            'request_id' => $this->request->id,
            'decision' => $this->decision,
            'comment' => $this->comment,
            'message' => "Zahtjev je {$this->decision}.",
        ];
    }
}
