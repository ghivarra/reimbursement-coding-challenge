<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Library\FilterLibrary;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // columns
        $columns = [
            'id', 'name', 'is_superadmin'
        ];

        // get input query
        $input = $request->validate([
            'limit'        => ['numeric', 'max:10'],
            'offset'       => ['numeric'],
            'order.column' => ['in:' . implode(',', $columns)],
            'order.dir'    => ['in:asc,desc'],
        ]);

        // query
        // format: ['query']['status']
        $query = $request->input('query');

        // orm
        $orm = Role::select(...$columns);

        if (count($query) > 0)
        {
            // init library
            $filter = new FilterLibrary();
            $orm    = $filter->parse($orm, $query);
        }

        // set limit, order, etc
        $result = $orm->orderBy($input['order']['column'], $input['order']['dir'])
                      ->offset($input['offset'])
                      ->limit($input['limit'])
                      ->get();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $result
        ], 200);
    }

    //====================================================================================================
}
