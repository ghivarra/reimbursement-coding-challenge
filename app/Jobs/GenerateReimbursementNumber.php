<?php

namespace App\Jobs;

use App\Library\CustomLibrary;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GenerateReimbursementNumber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $reimbursementID;
    public int $reimbursementCategoryID;
    public string $date;
    public int $retryNumber = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(string $reimbursementID, int $reimbursementCategoryID, string $date)
    {
        $this->reimbursementID = $reimbursementID;
        $this->reimbursementCategoryID = $reimbursementCategoryID;
        $this->date = $date;
    }

    private function updateNumber(string $number): void
    {
        $reimbursement = Reimbursement::find($this->reimbursementID);
        $reimbursement->number = $number;

        // attempt and add 
        $saveAttempt = $reimbursement->save();

        // set max retry
        $maxRetry = 5;

        // if false, retry until succesful
        if ($saveAttempt === false && $this->retryNumber < $maxRetry)
        {
            sleep(1);
            $this->retryNumber++;
            $this->updateNumber($number);
        }

        // if max
        if ($this->retryNumber >= $maxRetry)
        {
            throw new \Exception("Failed to set the number");
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // get code
        $cat = ReimbursementCategory::select('code')
                                    ->where('id', $this->reimbursementCategoryID)
                                    ->first();

        // parse year and month from date
        $dates = explode('-', $this->date);

        // count
        $total = Reimbursement::withTrashed()
                              ->whereYear('date', $dates[0])
                              ->whereMonth('date', $dates[1])
                              ->whereNotNull('number')
                              ->count();

        // build number
        $number = $total + 1;
        $number = sprintf('%05d', $number);

        // concat
        $completeNumber = sprintf('%s/%s/%s/%s/%s', $number, $cat->code, config('custom.reimbursement_code', 'REIMBURSE'), CustomLibrary::convertRomanMonth($dates[1]), $dates[0]);

        // assign number
        $this->updateNumber($completeNumber);
    }
}
