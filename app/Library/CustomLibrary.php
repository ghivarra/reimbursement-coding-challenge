<?php

namespace App\Library;

use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementStatus;
use Illuminate\Database\Eloquent\Builder;
use NumberFormatter;

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
        $status = ReimbursementStatus::select('id')->where('name', 'Disetujui')->limit(1)->first();

        // get category
        $cat = ReimbursementCategory::select('limit_per_month')
                                    ->where('id', $categoryID)
                                    ->limit(1)
                                    ->first();

        // get sum
        $sum = Reimbursement::where('user_id', $userID)
                            ->where('reimbursement_status_id', $status->id)
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

    /**
     * Konversi bulan ke angka romawi
     * 
     * @param int|string $monthNumber
     * 
     * @return string
     */
    public static function convertRomanMonth(int|string $monthNumber): string
    {
        $month        = strval($monthNumber);
        $monthInRoman = [
            '01' => 'I',
            '02' => 'II',
            '03' => 'III',
            '04' => 'IV',
            '05' => 'V',
            '06' => 'VI',
            '07' => 'VII',
            '08' => 'VIII',
            '09' => 'IX',
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII',
        ];

        // return
        return $monthInRoman[$month];
    }

    //==============================================================================================

    public static function localTime(string $datetime, string $param): string
    {
        // set default time
        // date_default_timezone_set(config('ENV_TIMEZONE'));
        // timezone already set to UTC in Laravel

        // parse into unix time
        $timestamp = strtotime($datetime);

        // new date time
		$DateTime = new \DateTime();
		$DateTime->setTimestamp($timestamp);

        // return
		return \IntlDateFormatter::formatObject($DateTime, $param, config('app.locale', 'en'));
    }

    //==============================================================================================

    public static function localCurrency(int|string $value, string $currency = 'IDR'): string
    {
        // set value to float
        $value  = floatval($value);

        // new number formatter
        $formatter = new \NumberFormatter(config('custom.locale', 'en_US'), \NumberFormatter::CURRENCY);
        $newValue  = $formatter->formatCurrency($value, $currency);
        $newLength = strlen($newValue) - 3;

        // return
        return substr($newValue, 0, $newLength);
    }

    //==============================================================================================
}