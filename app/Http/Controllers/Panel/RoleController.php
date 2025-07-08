<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Library\FilterLibrary;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        // get input query
        $validator = Validator::make($request->all(), [
            'name'          => ['required', 'unique:roles,name', 'max:100', 'alpha_dash'],
            'is_superadmin' => ['required', 'numeric', 'in:0,1'],
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => $validator->errors()
            ], 422);
        }

        // input
        $input = $validator->validated();

        // create
        $role = new Role;
        $role->name = $input['name'];
        $role->is_superadmin = $input['is_superadmin'];
        $role->save();

        // return
        return response()->json([
            'message' => 'Data berhasil dibuat',
            'data'    => [
                'id'            => $role->id,
                'name'          => $role->name,
                'is_superadmin' => $role->is_superadmin,
            ],
        ], 200);
    }

    //====================================================================================================

    public function delete(Request $request): JsonResponse
    {
        // find
        $id   = $request->input('id');
        $role = Role::find($id);
        
        if (empty($role))
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => [
                    'Data tidak ditemukan'
                ],
            ], 422);
        }

        // delete ID
        $role->delete();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => "Role {$role->name} berhasil dihapus",
        ], 200);
    }

    //====================================================================================================

    public function find(Request $request): JsonResponse
    {
        // get input query
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric']
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => $validator->errors()
            ], 422);
        }

        // input
        $input = $validator->validated();
        
        // get based on roles
        $result = Role::select(['id', 'name', 'is_superadmin'])->find($input['id']);

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $result,
        ], 200);
    }

    //====================================================================================================

    public function index(Request $request): JsonResponse
    {
        // columns
        $columns = [
            'id', 'name', 'is_superadmin'
        ];

        // get input query
        $validator = Validator::make($request->all(), [
            'limit'        => ['numeric', 'max:10'],
            'offset'       => ['numeric'],
            'order.column' => ['in:' . implode(',', $columns)],
            'order.dir'    => ['in:asc,desc'],
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => $validator->errors()
            ], 422);
        }

        // input
        $input = $validator->validated();

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

    public function update(Request $request): JsonResponse
    {
        // find
        $id   = $request->input('id');
        $role = Role::find($id);
        
        if (empty($role))
        {
             return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => [
                    'Data tidak ditemukan'
                ],
            ], 422);
        }

        // get form
        $validator = Validator::make($request->all(), [
            'name'          => ['required', 'alpha_dash', "unique:roles,name,{$id},id", "max:100"],
            'is_superadmin' => ['required', 'in:0,1'],
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => $validator->errors()
            ], 422);
        }

        // input
        $input = $validator->validated();
        
        // save
        $role->name = $input['name'];
        $role->is_superadmin = $input['is_superadmin'];
        $role->save();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diperbaharui',
            'data'    => $role,
        ], 200);
    }

    //====================================================================================================
}
