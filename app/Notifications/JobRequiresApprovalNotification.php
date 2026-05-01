<?php

namespace App\Notifications;

use App\Models\JobPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobRequiresApprovalNotification extends Notification
{
    use Queueable;

    public function __construct(public JobPost $job) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Job Post Requires Your Approval')
                    ->greeting('Hello Manager,')
                    ->line('A new job post titled "' . $this->job->title . '" has been created by HR.')
                    ->action('Review Job', url('/api/v1/jobs/' . $this->job->id))
                    ->line('Please approve or reject it according to company staff and your department needs.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'title' => $this->job->title,
            'message' => 'New job requires approval: ' . $this->job->title,
            'type' => 'APPROVAL_REQUEST'
        ];
    }
}
