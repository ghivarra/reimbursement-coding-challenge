<?php

namespace App\Library;

use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementLog;
use App\Models\ReimbursementStatus;

class ReimbursementLibrary
{
    /**
     * Melakukan kalkulasi limit reimburse
     * 
     * @param int|string $categoryID
     * @param int|string $userID
     * @param int|string $date
     * 
     * @return array ['limit' => 0, 'current' => 0, 'available' => 0]
     */
    public static function calculateReimbursementLimit(int|string $categoryID, int|string $userID, string $date): array
    {
        // parse date
        $dates = explode('-', $date);

        // get id disetujui
        $status = ReimbursementStatus::select('id')->whereNotIn('name', ['Disetujui', 'Dikembalikan'])->get();

        // get category
        $cat = ReimbursementCategory::select('limit_per_month')
                                    ->where('id', $categoryID)
                                    ->limit(1)
                                    ->first();

        // get sum
        $sum = Reimbursement::where('user_id', $userID)
                            ->whereIn('reimbursement_status_id', array_column($status->toArray(), 'id'))
                            ->whereYear('date', $dates[0])
                            ->whereMonth('date', $dates[1])
                            ->sum('amount');

        $sum = intval($sum);

        // return
        return [
            'limit'     => $cat->limit_per_month,
            'current'   => $sum,
            'available' => $cat->limit_per_month - $sum
        ];
    }

    //==============================================================================================

    public static function generateReimbursementLog(ReimbursementStatus $status, string|int $reimbursementID, string|int $ownerID, string|int|null $approverID = null): void
    {
        // set log
        $logs = [
            'content' => $status->template,
            'time'    => date('c'),
        ];

        // update logs
        $log = new ReimbursementLog;
        $log->content = json_encode($logs);
        $log->reimbursement_id = $reimbursementID;
        $log->reimbursement_status_id = $status->id;
        $log->owner_id = $ownerID;
        $log->approver_id = $approverID;
        $log->save();
    }

    //==============================================================================================
}