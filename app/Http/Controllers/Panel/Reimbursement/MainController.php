<?php

namespace App\Http\Controllers\Panel\Reimbursement;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReimbursementNumber;
use App\Library\CustomLibrary;
use App\Library\ReimbursementLibrary;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MainController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        // user id
        $userID = Auth::id();

        // rules
        $rules = [
            'name'        => ['required', 'max:200'],
            'amount'      => ['required', 'numeric', 'min:0'],
            'date'        => ['required', 'date', 'date_format:Y-m-d'],
            'user_id'     => ['required', 'exists:users,id'],
            'category_id' => ['required', 'exists:reimbursements_categories,id'],
            'file'        => ['required', 'mimetypes:image/jpg,image/jpeg,application/pdf', 'max:2000'],
        ];

        // get input query
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'name'        => 'Nama Pengajuan',
            'amount'      => 'Jumlah Pengajuan',
            'date'        => 'Tanggal',
            'user_id'     => 'User',
            'category_id' => 'Kategori Reimbursement',
            'file'        => 'Berkas',
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

        // check limit
        $calculation = ReimbursementLibrary::calculateReimbursementLimit(
            $input['category_id'], 
            $userID, 
            $input['date'],
        );

        if ($calculation['available'] < $input['amount'])
        {
            // set to 12.00 PM so locale inconsistency won't affect it
            $time = "{$input['date']} 12:00:00";

            // get cat name
            $cat   = ReimbursementCategory::select('name')->where('id', '=', $input['category_id'])->first();
            $month = CustomLibrary::localTime($time, "MMMM");
            $total = CustomLibrary::localCurrency($calculation['current']);
            $limit = CustomLibrary::localCurrency($calculation['limit']);

            // return error
            return response()->json([
                'status'  => 'error',
                'message' => "Total nilai reimbursement melebihi kalkulasi batas anggaran reimbursement bulan {$month} untuk kategori {$cat->name}. Anda sudah mengajukan reimbursement pada bulan {$month} sejumlah {$total} dari maksimal limit {$limit}.",
                'errors'  => $validator->errors()
            ], 422);
        }

        // get status diajukan
        $status = ReimbursementStatus::select('id', 'template')->where('name', 'Diajukan')->first();

        // upload file
        $uploadFile = $request->file('file');

        // directory
        $dates     = explode('-', $input['date']);
        $uploadDir = "reimbursements/file/{$dates[0]}/{$dates[1]}";

        // name
        $extension  = $uploadFile->getClientOriginalExtension();
        $randomName = Str::random(36) . '.' . $extension;

        // move
        $uploadFile->storeAs($uploadDir, $randomName);

        // create
        $reimbursement = new Reimbursement();
        $reimbursement->id = Str::uuid();
        $reimbursement->name = $input['name'];
        $reimbursement->file = "{$uploadDir}/{$randomName}";
        $reimbursement->amount = $input['amount'];
        $reimbursement->date = $input['date'];
        $reimbursement->user_id = $userID;
        $reimbursement->reimbursement_category_id = $input['category_id'];
        $reimbursement->reimbursement_status_id = $status->id;

        // save
        $reimbursement->save();

        // dispatch queue worker
        GenerateReimbursementNumber::dispatch($reimbursement->id, $reimbursement->reimbursement_category_id, $reimbursement->date);

        // create log
        ReimbursementLibrary::generateReimbursementLog($status, $reimbursement->id, $userID);

        // return
        return response()->json([
            'message' => 'Data berhasil dibuat',
            'data'    => $reimbursement,
        ], 200);
    }

    //====================================================================================================

    public function delete(Request $request): JsonResponse
    {
        // find
        $id  = $request->input('id');
        $reimbursement = Reimbursement::find($id);
        
        if (empty($reimbursement))
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
        $reimbursement->delete();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => "Kategori {$reimbursement->name} berhasil dihapus",
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
        $result = Reimbursement::select(['id', 'name', 'code', 'limit_per_month'])
                               ->where('id', '=', $input['id'])
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
        $data = Reimbursement::orderBy('name', 'asc')->get();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => empty($data) ? [] : $data,
        ], 200);
    }

    //====================================================================================================

    public function generateLog(): void
    {

    }

    //====================================================================================================

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
        $orm = Reimbursement::select(...$columns);

        if (count($query) > 0)
        {
            // init library
            $filter = CustomLibrary::parseQuery($orm, $query);
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
        // user id
        $userID = Auth::id();

        // rules
        $rules = [
            'name'        => ['required', 'max:200'],
            'amount'      => ['required', 'numeric', 'min:0'],
            'date'        => ['required', 'date', 'date_format:Y-m-d'],
            'user_id'     => ['required', 'exists:users,id'],
            'category_id' => ['required', 'exists:reimbursements_categories,id'],
            'file'        => ['required', 'mimetypes:image/jpg,image/jpeg,application/pdf', 'max:2000'],
        ];

        // get input query
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'name'        => 'Nama Pengajuan',
            'amount'      => 'Jumlah Pengajuan',
            'date'        => 'Tanggal',
            'user_id'     => 'User',
            'category_id' => 'Kategori Reimbursement',
            'file'        => 'Berkas',
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

        // check limit
        $calculation = ReimbursementLibrary::calculateReimbursementLimit($input['category_id'], $userID, $input['date']);

        if ($calculation['available'] < $input['amount'])
        {
            // set to 12.00 PM so locale inconsistency won't affect it
            $time = "{$input['date']} 12:00:00";

            // get cat name
            $cat   = ReimbursementCategory::select('name')->where('id', '=', $input['category_id'])->first();
            $month = CustomLibrary::localTime($time, "MMMM");
            $total = CustomLibrary::localCurrency($calculation['current']);
            $limit = CustomLibrary::localCurrency($calculation['limit']);

            // return error
            return response()->json([
                'status'  => 'error',
                'message' => "Total nilai reimbursement yang anda masukkan melebihi kalkulasi batas anggaran reimbursement bulan {$month} untuk kategori {$cat->name}. Anda sudah melakukan reimbursement pada bulan {$month} sejumlah {$total} dari limit {$limit}.",
                'errors'  => $validator->errors()
            ], 422);
        }

        // get status diajukan
        $status = ReimbursementStatus::select('id')->where('name', 'Diajukan')->first();

        // upload file
        $uploadFile = $request->file('file');

        // directory
        $dates     = explode('-', $input['date']);
        $uploadDir = "reimbursements/file/{$dates[0]}/{$dates[1]}";

        // name
        $extension  = $uploadFile->getClientOriginalExtension();
        $randomName = Str::random(36) . '.' . $extension;

        // move
        $uploadFile->storeAs($uploadDir, $randomName);

        // create
        $reimbursement = new Reimbursement();
        $reimbursement->name = $input['name'];
        $reimbursement->file = "{$uploadDir}/{$randomName}";
        $reimbursement->amount = $input['amount'];
        $reimbursement->date = $input['date'];
        $reimbursement->reimbursement_category_id = $input['category_id'];
        $reimbursement->reimbursement_status_id = $status->id;

        // save
        $reimbursement->save();

        // return
        return response()->json([
            'message' => 'Data berhasil dibuat',
            'data'    => $reimbursement,
        ], 200);
    }

    //====================================================================================================
}
