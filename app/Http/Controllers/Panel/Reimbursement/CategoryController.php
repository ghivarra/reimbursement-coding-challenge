<?php

namespace App\Http\Controllers\Panel\Reimbursement;

use App\Http\Controllers\Controller;
use App\Models\ReimbursementCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Library\FilterLibrary;

class CategoryController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        // get input query
        $validator = Validator::make($request->all(), [
            'name'            => ['required', 'unique:reimbursements_categories,code', 'max:100'],
            'code'            => ['required', 'unique:reimbursements_categories,code', 'max:4'],
            'limit_per_month' => ['required', 'numeric'],
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
        $cat = new ReimbursementCategory();
        $cat->name = $input['name'];
        $cat->code = $input['code'];
        $cat->limit_per_month = $input['limit_per_month'];
        $cat->save();

        // return
        return response()->json([
            'message' => 'Data berhasil dibuat',
            'data'    => $cat,
        ], 200);
    }

    //====================================================================================================

    public function delete(Request $request): JsonResponse
    {
        // find
        $id   = $request->input('id');
        $cat = ReimbursementCategory::find($id);
        
        if (empty($cat))
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => [
                    'Data tidak ditemukan'
                ],
            ], 422);
        }

        // delete
        $cat->delete();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => "User {$cat->name} berhasil dihapus",
        ], 200);
    }

    //====================================================================================================

    public function find(Request $request): JsonResponse
    {
        // input
        $input = $request->validate([
            'id' => ['numeric']
        ]);
        
        // get based on roles
        $result = ReimbursementCategory::select(['id', 'name', 'code', 'limit_per_month'])
                                       ->find($input['id']);

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $result,
        ], 200);
    }

    //====================================================================================================

    public function findAll(): JsonResponse
    {
        $data = ReimbursementCategory::orderBy('name', 'asc')->get();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $data,
        ], 200);
    }

    //=============================================================================================

    public function index(Request $request): JsonResponse
    {
        // columns
        $columns = [
            'id', 'name', 'code', 'limit_per_month'
        ];

        $allowedQuery = [
            'name', 'code'
        ];

        // get input query
        $validator = Validator::make($request->all(), [
            'limit'        => ['numeric', 'max:10'],
            'offset'       => ['numeric'],
            'order.column' => ['in:'.implode(',', $allowedQuery)],
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
        $query = $request->input('query', []);

        // orm
        $orm = ReimbursementCategory::select(...$columns);

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
        $cat = ReimbursementCategory::find($id);
        
        if (empty($cat))
        {
             return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => [
                    'Data tidak ditemukan'
                ],
            ], 422);
        }

        // set rules
        $rules = [
            'name'     => ['required', 'max:100'],
            'email'    => ['required', 'email', "unique:reimbursements_categories,email,{$id},id"],
            'role_id'  => ['required', 'exists:roles,id'],
        ];

        // check if password is supplied, but optional
        $password = $request->input('password', null);
        if (!is_null($password))
        {
            $rules['password'] = ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()->symbols()];
        }

        // get form
        $validator = Validator::make($request->all(), $rules);

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
        $cat->name = $input['name'];
        $cat->email = $input['email'];
        $cat->role_id = $input['role_id'];

        if (!is_null($password))
        {
            $cat->password = $input['password'];
        }

        // save
        $cat->save();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diperbaharui',
            'data'    => $cat,
        ], 200);
    }

    //====================================================================================================
}
