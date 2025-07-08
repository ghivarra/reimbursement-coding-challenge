<?php

namespace App\Library;

use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementStatus;
use Illuminate\Database\Eloquent\Builder;

class CustomLibrary
{
    /**
     * Melakukan parsing terhadap filter yang direquest dari frontend
     * 
     * @param Builder $orm
     * @param array $queries
     * @return Builder $orm
     */
    public static function parseQuery(Builder $orm, array $queries, array $switchParams = []): Builder
    {
        foreach ($queries as $query):

            // if not set or empty just continue
            if (!isset($query['command'], $query['value'], $query['param']) || strlen($query['value']) < 1)
            {
                continue;
            }

            // change param
            // if supplied
            if (!empty($switchParams))
            {
                $keyParams = array_keys($switchParams);
                if (in_array($query['param'], $keyParams))
                {
                    $query['param'] = $switchParams[$query['param']];
                }
            }

            // switch
            switch ($query['command']) {
                case 'contain':
                    $orm->whereLike($query['param'], "%{$query['value']}%");
                    break;

                case 'is_not':
                    $orm->where($query['param'], '!=', $query['value']);
                    break;

                case 'is_not_contain':
                    $orm->whereNotLike($query['param'], "%{$query['value']}%");
                    break;
                
                default:
                    $orm->where($query['param'], '=', $query['value']);
                    break;
            }

        endforeach;

        // return
        return $orm;
    }

    //==============================================================================================

    public static function calculateReimbursementLimit(int|string $categoryID, int|string $userID, string $month): array
    {
        // get id disetujui
        $status = ReimbursementStatus::select('id')->where('name', 'Disetujui')->limit(1)->first();

        // get category
        $cat = ReimbursementCategory::select('limit_per_month')
                                    ->where('id', $categoryID)
                                    ->limit(1)
                                    ->first();

        // get sum
        $sum = Reimbursement::where('user_id', $userID)
                            ->where('reimbursement_status_id', $status->id)
                            ->whereMonth('date', $month)
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
}