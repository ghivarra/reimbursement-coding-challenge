<?php

namespace App\Jobs;

use App\Mail\NotificationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\SentMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $reimbursementName;
    public string $ownerName;
    public string $emailName;
    public int $retryNumber = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(string $reimbursementName, string $ownerName, string $emailName)
    {
        $this->reimbursementName = $reimbursementName;
        $this->ownerName = $ownerName;
        $this->emailName = $emailName;
    }

    private function sendMail(NotificationEmail $mail)
    {
        // set max retry
        $maxRetry = 5;

        if (Mail::to($this->emailName)->send($mail) instanceof SentMessage) {

            // OK
            return;

        } else {

            if ($this->retryNumber < $maxRetry)
            {
                sleep(1);
                $this->retryNumber++;
                return $this->sendMail($mail);
            }
        }

        // if max
        if ($this->retryNumber >= $maxRetry)
        {
            throw new \Exception("Failed to send email to {$this->emailName}");
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mail = new NotificationEmail($this->ownerName, $this->reimbursementName);
        $this->sendMail($mail);
    }
}
