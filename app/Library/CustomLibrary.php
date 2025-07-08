<?php

namespace App\Library;

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
}