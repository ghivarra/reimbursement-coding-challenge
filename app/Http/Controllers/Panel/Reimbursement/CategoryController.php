<?php

namespace App\Http\Controllers\Panel\Reimbursement;

use App\Http\Controllers\Controller;
use App\Library\CustomLibrary;
use App\Library\ReimbursementLibrary;
use App\Models\ReimbursementCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function checkLimit(Request $request): JsonResponse
    {
        // input
        // get input query
        $validator = Validator::make($request->all(), [
            'category_id' => ['numeric', 'exists:reimbursements_categories,id'],
            'date'        => ['date', 'date_format:Y-m-d'],
        ]);

        $validator->setAttributeNames([
            'category_id' => 'Kategori Reimbursement',
            'date'       => 'Tanggal',
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => $validator->errors()
            ], 422);
        }

        // parse input
        $input = $validator->validated();
        $dates = explode('-', $input['date']);

        // parameter
        $month  = $dates[1];
        $userID = Auth::id();

        // calculate it
        // and return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => ReimbursementLibrary::calculateReimbursementLimit($input['category_id'], $userID, $month),
        ], 200);
    }

    //====================================================================================================

    public function create(Request $request): JsonResponse
    {
        // get input query
        $validator = Validator::make($request->all(), [
            'name'            => ['required', 'max:100'],
            'code'            => ['required', 'unique:reimbursements_categories,code', 'max:4'],
            'limit_per_month' => ['required', 'numeric'],
        ]);

        $validator->setAttributeNames([
            'name'            => 'Nama Kategori Reimbursement',
            'code'            => 'Kode',
            'limit_per_month' => 'Batas reimbursement per bulan',
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
        $id  = $request->input('id');
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
            'message' => "Kategori {$cat->name} berhasil dihapus",
        ], 200);
    }

    //====================================================================================================

    public function find(Request $request): JsonResponse
    {
        // input
        // get input query
        $validator = Validator::make($request->all(), [
            'id' => ['numeric']
        ]);

        $validator->setAttributeNames([
            'id' => 'Kategori Reimbursement'
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
        $result = ReimbursementCategory::select(['id', 'name', 'code', 'limit_per_month'])
                                       ->where('id', $input['id'])
                                       ->first();

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
            'data'    => empty($data) ? [] : $data,
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

        // set attributes
        $validator->setAttributeNames([
            'limit'        => 'Limit',
            'offset'       => 'Offset',
            'order.column' => 'Order Column',
            'order.dir'    => 'Order Dir'
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
            $orm = CustomLibrary::parseQuery($orm, $query);
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
            'data'    => empty($result) ? [] : $result
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
            'id'              => ['required'],
            'name'            => ['required', 'max:100'],
            'code'            => ['required', "unique:reimbursements_categories,code,{$id},id", 'max:4'],
            'limit_per_month' => ['required', 'numeric'],
        ];

         // get input query
        $validator = Validator::make($request->all(), $rules);

        $validator->setAttributeNames([
            'id'              => 'Kategori Reimbursement',
            'name'            => 'Nama Kategori Reimbursement',
            'code'            => 'Kode',
            'limit_per_month' => 'Batas reimbursement per bulan',
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
        $cat->name = $input['name'];
        $cat->code = $input['code'];
        $cat->limit_per_month = $input['limit_per_month'];
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
