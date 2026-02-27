<?php

namespace App\Notifications;

use App\Models\ApprovalTask;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalTaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ApprovalTask $task,
        private readonly ServiceRequest $request
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('FlowDesk odobrenje čeka tvoju odluku')
            ->line("Dodijeljen ti je task odobravanja za zahtjev #{$this->request->id}.")
            ->line("Korak: {$this->task->step_name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval_assigned',
            'request_id' => $this->request->id,
            'task_id' => $this->task->id,
            'step_name' => $this->task->step_name,
            'message' => 'Dodijeljen ti je novi approval task.',
        ];
    }
}
