<?php

namespace App\Jobs;

use App\Models\AssessmentAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\AssessmentCompletedNotification;
use App\Models\User;

class SubmitToMossJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected AssessmentAttempt $attempt;

    /**
     * Create a new job instance.
     */
    public function __construct(AssessmentAttempt $attempt)
    {
        $this->attempt = $attempt;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Here we would use the interviewer's stored MOSS token 
        // to send the code to Stanford MOSS. 
        // For demonstration, we simulate the MOSS API call.
        
        $simulatedPlagiarismScore = rand(0, 100);
        $simulatedReportUrl = 'https://moss.stanford.edu/results/' . uniqid();

        $this->attempt->update([
            'plagiarism_score' => $simulatedPlagiarismScore,
            'moss_report_url' => $simulatedReportUrl,
            'status' => 'FINISHED' // Mark attempt as finished
        ]);

        // Fetch related entities for notification
        $candidate = $this->attempt->application->candidate;
        
        // Find HR Admin and Interviewer associated with this job/assessment
        $hrAdmin = User::where('role', 'HR_ADMIN')->first(); // Simplification
        $interviewer = $this->attempt->assessment->created_by; // Assuming created_by is interviewer
        $interviewerUser = User::find($interviewer);

        // Dispatch Notifications
        $notification = new AssessmentCompletedNotification($this->attempt);
        
        if ($candidate) {
            $candidate->notify($notification);
        }
        if ($hrAdmin) {
            $hrAdmin->notify($notification);
        }
        if ($interviewerUser) {
            $interviewerUser->notify($notification);
        }
    }
}
