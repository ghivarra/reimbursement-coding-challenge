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
        $cat = ReimbursementCategory::select('id', 'limit_per_month')
                                    ->where('id', $categoryID)
                                    ->limit(1)
                                    ->first();

        // get sum
        $sum = Reimbursement::where('owner_id', $userID)
                            ->where('reimbursement_category_id', $cat->id)
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

    public static function generateReimbursementLog(ReimbursementStatus|array $status, string|int $reimbursementID, string|int $ownerID, string|int|null $approverID = null, string|null $note = null): void
    {
        // set log
        $logs = [
            'content' => is_array($status) ? $status['template'] : $status->template,
            'time'    => date('c'),
            'note'    => $note,
        ];

        // update logs
        $log = new ReimbursementLog;
        $log->content = json_encode($logs);
        $log->reimbursement_id = $reimbursementID;
        $log->reimbursement_status_id = is_array($status) ? $status['id'] : $status->id;
        $log->owner_id = $ownerID;
        $log->approver_id = $approverID;
        $log->save();
    }

    //==============================================================================================

    public static function parseLog(array $log): array
    {
        // decode content
        $content = json_decode($log['content'], true);

        // replaced array
        $srcString = [
            '{$name}',
            '{$owner}',
            '{$approver}',
            '{$action}',
        ];

        $repString = [
            $log['name'],
            $log['owner_name'],
            $log['approver_name'],
            $log['action']
        ];

        // parse replace
        $content['content']     = str_ireplace($srcString, $repString, $content['content']);
        $content['status_name'] = $log['status_name'];
        // $content['status_id']   =

        // return
        return $content;
    }

    //==============================================================================================
}