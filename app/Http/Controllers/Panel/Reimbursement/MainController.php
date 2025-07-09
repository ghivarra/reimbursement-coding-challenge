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
use Illuminate\Support\Facades\Storage;
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
            'category_id' => ['required', 'exists:reimbursements_categories,id'],
            'file'        => ['required', 'mimetypes:image/jpg,image/jpeg,application/pdf', 'max:2000'],
        ];

        // description
        $description = $request->input('description');

        if (!empty($description))
        {
            $rules['description'] = ['string'];
        }

        // get input query
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'name'        => 'Nama Pengajuan',
            'amount'      => 'Jumlah Pengajuan',
            'date'        => 'Tanggal',
            'category_id' => 'Kategori Reimbursement',
            'file'        => 'Berkas',
            'description' => 'Deskripsi Pengajuan',
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
            $cat       = ReimbursementCategory::select('name')->where('id', '=', $input['category_id'])->first();
            $times     = CustomLibrary::localTime($time, "MMMM YYYY");
            $total     = CustomLibrary::localCurrency($calculation['current']);
            $limit     = CustomLibrary::localCurrency($calculation['limit']);
            $amount    = CustomLibrary::localCurrency($input['amount']);
            $available = CustomLibrary::localCurrency($calculation['available']);

            // return error
            return response()->json([
                'status'  => 'error',
                'message' => "Total nilai reimbursement {$amount} melebihi sisa anggaran reimbursement bulan {$times} untuk kategori {$cat->name} yaitu {$available}. Anda sudah mengajukan reimbursement kategori {$cat->name} pada bulan {$times} sejumlah {$total} dari maksimal limit {$limit}.",
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
        $reimbursement->owner_id = $userID;
        $reimbursement->reimbursement_category_id = $input['category_id'];
        $reimbursement->reimbursement_status_id = $status->id;
        $reimbursement->description = $description;

        // save
        $reimbursement->save();

        // dispatch queue worker
        GenerateReimbursementNumber::dispatch($reimbursement->id, $reimbursement->reimbursement_category_id, $reimbursement->date);

        // create log
        ReimbursementLibrary::generateReimbursementLog($status, $reimbursement->id, $userID);

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil dibuat',
            'data'    => $reimbursement,
        ], 200);
    }

    //====================================================================================================

    public function delete(Request $request): JsonResponse
    {
        // find
        $id            = $request->input('id');
        $reimbursement = Reimbursement::where('id', $id)->first();
        
        // if empty
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

        // only the owner can delete
        $userID = Auth::id();

        if (intval($reimbursement['owner_id']) !== $userID)
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya pemilik atau yang mengajukan reimbursement yang bisa menghapus',
            ], 403);
        }

        // cannot delete if already been responded
        $statuses = ReimbursementStatus::whereIn('name', ['Dihapus', 'Ditolak', 'Disetujui'])->get();
        $statuses = empty($statuses) ? [] : $statuses->toArray();

        if (in_array($reimbursement->reimbursement_status_id, array_column($statuses, 'id')))
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pengajuan tidak bisa dihapus karena sudah direspon',
            ], 403);
        }

        $status = [];

        foreach ($statuses as $item):

            if ($item['name'] === 'Dihapus')
            {
                $status = $item;
                break;
            }

        endforeach;

        // update log first
        $reimbursement->reimbursement_status_id = $status['id'];
        $reimbursement->save();
        
        // delete
        $reimbursement->delete();

        // generate logs
        ReimbursementLibrary::generateReimbursementLog($status, $reimbursement->id, $reimbursement->owner_id, $reimbursement->approver_id);

        // return
        return response()->json([
            'status'  => 'success',
            'message' => "Kategori {$reimbursement->name} berhasil dihapus",
        ], 200);
    }

    //====================================================================================================

    public function find(Request $request, string $option): JsonResponse
    {
        // input
        // get input query
        $validator = Validator::make($request->all(), [
            'id' => ['exists:reimbursements,id']
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
        $selectedFields = [
            'reimbursements.id',
            'reimbursements.number',
            'reimbursements.name',
            'reimbursements.file',
            'reimbursements.amount',
            'reimbursements.description',
            'reimbursements.date',
            'reimbursements.owner_id',
            'owners.name as owner_name',
            'reimbursements.approver_id',
            'approvers.name as approver_name',
            'reimbursements.reimbursement_category_id as category_id',
            'reimbursements_categories.name as category_name',
            'reimbursements.reimbursement_status_id as status_id',
            'reimbursements_statuses.name as status_name',
            'reimbursements.created_at',
            'reimbursements.updated_at',
            'reimbursements.deleted_at',
        ];

        if ($option === 'with-removed')
        {
            $orm = Reimbursement::withTrashed()->select($selectedFields);

        } else {
            
            $orm = Reimbursement::select($selectedFields);
        }

        $orm = $orm->join('users as owners', 'reimbursements.owner_id', '=', 'owners.id')
                   ->leftJoin('users as approvers', 'reimbursements.approver_id', '=', 'approvers.id')
                   ->join('reimbursements_categories', 'reimbursement_category_id', '=', 'reimbursements_categories.id')
                   ->join('reimbursements_statuses', 'reimbursement_status_id', '=', 'reimbursements_statuses.id')
                   ->where('reimbursements.id', '=', $input['id']);

        switch ($option) {
            case 'approver':
                $status = ReimbursementStatus::select('id')->where('name', 'Dikembalikan')->first();
                $orm->whereNot('reimbursement_status_id', $status->id);
                break;

            case 'self':
                $orm->where('owner_id', '=', Auth::id());
                break;
            
            default:
                // default is do nothing
                break;
        }

        // get result
        $result = $orm->first();

        // set
        if (!empty($result->name))
        {
            $result->file = url('assets/' . $result->file);
        }

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $result,
        ], 200);
    }

    //====================================================================================================

    public function findApprover(Request $request): JsonResponse
    {
        return $this->find($request, 'approver');
    }

    //====================================================================================================

    public function findSelf(Request $request): JsonResponse
    {
        return $this->find($request, 'self');
    }

    //====================================================================================================

    public function findWithRemoved(Request $request): JsonResponse
    {
        return $this->find($request, 'with-removed');
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

    public function index(Request $request, string $option): JsonResponse
    {
        // columns
        $columns = [
            'reimbursements.id',
            'reimbursements.number',
            'reimbursements.name',
            //'reimbursements.file',
            'reimbursements.amount',
            //'reimbursements.description',
            'reimbursements.date',
            'reimbursements.owner_id',
            'owners.name as owner_name',
            'reimbursements.approver_id',
            'approvers.name as approver_name',
            'reimbursements.reimbursement_category_id as category_id',
            'reimbursements_categories.name as category_name',
            'reimbursements.reimbursement_status_id as status_id',
            'reimbursements_statuses.name as status_name',
            'reimbursements.created_at',
            'reimbursements.updated_at',
            'reimbursements.deleted_at',
        ];

        $allowedQuery = [
            'created_at',
            'updated_at',
            'date',
            'approver_name',
            'category_name',
            'status_name',
        ];

        $changedQuery = [
            'id' => 'reimbursements.id',
            'number' => 'reimbursements.number',
            'name' => 'reimbursements.name',
            'file' => 'reimbursements.file',
            'amount' => 'reimbursements.amount',
            'description' => 'reimbursements.description',
            'date' => 'reimbursements.date',
            'owner_id' => 'reimbursements.owner_id',
            'owner_name' => 'owners.name',
            'approver_id' => 'reimbursements.approver_id',
            'approver_name' => 'approvers.name',
            'category_id' => 'reimbursements.reimbursement_category_id',
            'category_name' => 'reimbursements_categories.name',
            'status_id' => 'reimbursements.reimbursement_status_id',
            'status_name' => 'reimbursements_statuses.name',
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
        if ($option === 'archive' || $option === 'with-removed')
        {
            $orm = Reimbursement::withTrashed()->select(...$columns);
            

        } else {
            
            $orm = Reimbursement::select(...$columns);
        }

        $orm = $orm->join('users as owners', 'reimbursements.owner_id', '=', 'owners.id')
                   ->leftJoin('users as approvers', 'reimbursements.approver_id', '=', 'approvers.id')
                   ->join('reimbursements_categories', 'reimbursement_category_id', '=', 'reimbursements_categories.id')
                   ->join('reimbursements_statuses', 'reimbursement_status_id', '=', 'reimbursements_statuses.id');

        switch ($option) {
            case 'approver':
                $orm->where('reimbursements.approver_id', '=', Auth::id());
                break;

            case 'archive':
                $orm->whereNotNull('reimbursements.deleted_at');
                break;

            case 'self':
                $orm->where('reimbursements.owner_id', '=', Auth::id());
                break;

            case 'submitted':
                $statuses = ReimbursementStatus::select('id')->whereIn('name', ['Diajukan', 'Revisi'])->get();

                if (!empty($statuses))
                {
                    $statuses = $statuses->toArray();
                }

                $orm->whereIn('reimbursements.reimbursement_status_id', array_column($statuses, 'id'));
                break;
            
            default:
                // default is do nothing
                break;
        }

        if (count($query) > 0)
        {
            // init library
            $orm = CustomLibrary::parseQuery($orm, $query, $changedQuery);
        }

        // set limit, order, etc
        $orm = $orm->orderBy($input['order']['column'], $input['order']['dir'])
                   ->offset($input['offset'])
                   ->limit($input['limit']);
        
        $result = $orm->get();

        // set
        if (!empty($result))
        {
            foreach ($result as $key => $item):

                $result[$key]->file = url('assets/' . $item->file);

            endforeach;
        }

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => empty($result) ? [] : $result
        ], 200);
    }

    //====================================================================================================

    public function indexAll(Request $request): JsonResponse
    {
        return $this->index($request, 'all');
    }

    //====================================================================================================

    public function indexApprover(Request $request): JsonResponse
    {
        return $this->index($request, 'approver');
    }

    //====================================================================================================

    public function indexArchive(Request $request): JsonResponse
    {
        return $this->index($request, 'archive');
    }

    //====================================================================================================

    public function indexSelf(Request $request): JsonResponse
    {
        return $this->index($request, 'self');
    }

    //====================================================================================================

    public function indexSubmitted(Request $request): JsonResponse
    {
        return $this->index($request, 'submitted');
    }

    //====================================================================================================

    public function indexWithRemoved(Request $request): JsonResponse
    {
        return $this->index($request, 'with-removed');
    }

    //====================================================================================================

    public function respond(Request $request): JsonResponse
    {
        // get status id
        $allowedStatuses = ReimbursementStatus::whereIn('name', ['Dikembalikan', 'Disetujui', 'Ditolak'])
                                              ->get();

        $allowedStatuses = empty($allowedStatuses) ? [] : $allowedStatuses->toArray();
        $statusIDs       = array_column($allowedStatuses, 'id');
        
        // get input query
        $validator = Validator::make($request->all(), [
            'id'        => ['required', 'exists:reimbursements,id'],
            'status_id' => ['required', 'in:'.implode(',', $statusIDs)],
            'note'      => ['max:200']
        ]);

        $validator->setAttributeNames([
            'id'        => 'Pengajuan reimbursement',
            'status_id' => 'Status pengajuan',
            'note'      => 'Catatan',
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
        $notes = $request->input('note');

        // get data
        $reimbursement = Reimbursement::where('id', $input['id'])->first();

        // pastikan status bukan yang diatas
        if (in_array($reimbursement->reimbursement_status_id, $statusIDs))
        {
            // return
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda hanya bisa memberikan respons kepada pengajuan yang statusnya "Diajukan" atau "Revisi". Bisa jadi pihak lain sudah memberikan respons.',
            ], 403);
        }

        // set & save
        $userID = Auth::id();
        $reimbursement->reimbursement_status_id = $input['status_id'];
        $reimbursement->approver_id = $userID;
        $reimbursement->save();

        // status get
        $status = [];

        foreach ($allowedStatuses as $item):

            if (intval($item['id']) === intval($input['status_id']))
            {
                $status = $item;
                break;
            }

        endforeach;

        // send log
        ReimbursementLibrary::generateReimbursementLog($status, $input['id'], $reimbursement->owner_id, $userID, $notes);

        // return
        return response()->json([
            'status'  => 'success',
            'message' => "Pengajuan berhasil {$status['action']}"
        ], 200);
    }

    //====================================================================================================

    public function update(Request $request): JsonResponse
    {
        // user id
        $userID        = Auth::id();
        $reimbursement = Reimbursement::where('id', $request->input('id'))->first();

        // get status diajukan
        $statuses = ReimbursementStatus::select('id', 'name', 'template')
                                       ->whereIn('name', ['Revisi', 'Dikembalikan'])
                                       ->get();

        if (!empty($statuses))
        {
            $statuses = $statuses->toArray();
        }

        $status = [];

        foreach ($statuses as $item):

            if ($item['name'] === 'Revisi')
            {
                $status['revisi'] = $item;

            } elseif ($item['name'] === 'Dikembalikan') {

                $status['dikembalikan'] = $item;
            }
            
        endforeach;

        // checking first
        if (empty($reimbursement))
        {
            // return
            return response()->json([
                'status'  => 'error',
                'message' => 'Pengajuan tidak ditemukan',
            ], 404);
        }

        if ($userID !== intval($reimbursement->owner_id))
        {
            // return
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki izin untuk merevisi pengajuan ini',
            ], 403);
        }
        
        if (intval($reimbursement->reimbursement_status_id) !== intval($status['dikembalikan']['id']))
        {
            // return
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak bisa merevisi permohonan ini karena permohonan ini sudah dikirim/diajukan',
            ], 403);
        }

        // rules
        $rules = [
            'id'          => ['required', 'exists:reimbursements,id'],
            'name'        => ['required', 'max:200'],
            'amount'      => ['required', 'numeric', 'min:0'],
            'date'        => ['required', 'date', 'date_format:Y-m-d'],
            'category_id' => ['required', 'exists:reimbursements_categories,id']
        ];

        // file & note optional
        $optional = [
            'file' => ['required', 'mimetypes:image/jpg,image/jpeg,application/pdf', 'max:2000'],
            'note' => ['required', 'max:200'],
        ];

        foreach ($optional as $key => $rule):

            if ($key === 'file')
            {
                $file = $request->file($key);

                if ($file->isValid())
                {
                    $rules[$key] = $rule;
                }

            } else {

                $item = $request->input($key);

                if (!empty($item))
                {
                    $rules[$key] = $rule;
                }
            }
            
        endforeach;

        // get input query
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'id'          => 'ID Pengajuan',
            'name'        => 'Nama Pengajuan',
            'amount'      => 'Jumlah Pengajuan',
            'date'        => 'Tanggal',
            'category_id' => 'Kategori Reimbursement',
            'file'        => 'Berkas',
            'note'        => 'Catatan',
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

        // update
        $reimbursement->name = $input['name'];
        $reimbursement->amount = $input['amount'];
        $reimbursement->date = $input['date'];
        $reimbursement->reimbursement_category_id = $input['category_id'];
        $reimbursement->reimbursement_status_id = $status['revisi']['id'];

        // upload file
        $uploadFile = $request->file('file');

        if ($uploadFile->isValid())
        {
            // directory
            $dates     = explode('-', $input['date']);
            $uploadDir = "reimbursements/file/{$dates[0]}/{$dates[1]}";
    
            // name
            $extension  = $uploadFile->getClientOriginalExtension();
            $randomName = Str::random(36) . '.' . $extension;
    
            // move
            $uploadFile->storeAs($uploadDir, $randomName);

            // create
            $reimbursement->file = "{$uploadDir}/{$randomName}";
        }

        // save
        $reimbursement->save();

        // send log
        ReimbursementLibrary::generateReimbursementLog($status['revisi'], $reimbursement->id, $reimbursement->owner_id, $reimbursement->approver_id, $request->input('note', ''));

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diperbaharui',
            'data'    => $reimbursement,
        ], 200);
    }

    //====================================================================================================

    public function restore(Request $request): JsonResponse
    {
        // get input query
        $validator = Validator::make($request->all(), [
            'id' => ['exists:reimbursments,id']
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

        // get instance
        $reimbursement = Reimbursement::withTrashed()->where('id', $input['id'])->get();
        $reimbursement->restore();

        // get status
        $status = ReimbursementStatus::where('name', 'Dihapus')->first()->toArray();

        // send logs      
        ReimbursementLibrary::generateReimbursementLog($status, $reimbursement->id, $reimbursement->owner_id, Auth::id());

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diperbaharui'
        ], 200);
    }

    //====================================================================================================
}
