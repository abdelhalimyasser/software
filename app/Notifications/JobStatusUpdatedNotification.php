<?php

namespace App\Notifications;

use App\Models\JobPost;
use App\Models\Enums\JobStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class JobStatusUpdatedNotification
 *
 * This notification is sent to the HR team when a job post's status is updated by the Department Manager.
 * It provides details about the job post, the new status, and the reason for the status change.
 *
 * @package App\Notifications
 * @version 1.0
 * @since 01-05-2026
 * @author Ali Samy
 */
class JobStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public JobPost $job) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusText = $this->job->status === JobStatus::APPROVED ? 'APPROVED' : 'REJECTED';

        return (new MailMessage)
            ->subject('Job Post Status: ' . $statusText)
            ->greeting('Hello HR Team,')
            ->line('Your job post "' . $this->job->title . '" has been ' . $this->job->status->value . ' by the Department Manager.')
            ->line('Manager\'s Reason: ' . $this->job->status_reason)
            ->action('View Job Post', url('/api/v1/jobs/' . $this->job->id));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'title' => $this->job->title,
            'status' => $this->job->status->value,
            'reason' => $this->job->status_reason,
            'message' => 'Job "' . $this->job->title . '" is now ' . $this->job->status->value,
            'type' => 'JOB_STATUS_UPDATE'
        ];
    }
}
