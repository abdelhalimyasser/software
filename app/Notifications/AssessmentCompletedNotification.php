<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AssessmentAttempt;

class AssessmentCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected AssessmentAttempt $attempt;

    /**
     * Create a new notification instance.
     */
    public function __construct(AssessmentAttempt $attempt)
    {
        $this->attempt = $attempt;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Assessment Completed')
                    ->line('An assessment attempt has been completed.')
                    ->line('Score: ' . $this->attempt->score)
                    ->line('Plagiarism Score: ' . $this->attempt->plagiarism_score . '%')
                    ->action('View Report', $this->attempt->moss_report_url ?? url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'attempt_id' => $this->attempt->id,
            'score' => $this->attempt->score,
            'plagiarism_score' => $this->attempt->plagiarism_score,
            'moss_report_url' => $this->attempt->moss_report_url
        ];
    }
}
