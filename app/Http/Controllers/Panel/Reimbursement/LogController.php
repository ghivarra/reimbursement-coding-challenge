<?php

namespace App\Http\Controllers\Panel\Reimbursement;

use App\Http\Controllers\Controller;
use App\Library\ReimbursementLibrary;
use App\Models\ReimbursementLog;
use App\Models\ReimbursementStatus;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LogController extends Controller
{
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
            'reimbursements_logs.content',
            'reimbursements.name',
            'reimbursements_logs.owner_id',
            'owners.name as owner_name',
            'reimbursements_logs.approver_id',
            'approvers.name as approver_name',
            'reimbursements_logs.reimbursement_id',
            'reimbursements.reimbursement_category_id as category_id',
            'reimbursements_categories.name as category_name',
            'reimbursements_logs.reimbursement_status_id as status_id',
            'reimbursements_statuses.name as status_name',
            'reimbursements_statuses.action',
            'reimbursements_logs.created_at',
            'reimbursements_logs.updated_at'
        ];

            
        $orm = ReimbursementLog::select($selectedFields);
        $orm = $orm->join('users as owners', 'reimbursements_logs.owner_id', '=', 'owners.id')
                   ->leftJoin('users as approvers', 'reimbursements_logs.approver_id', '=', 'approvers.id')
                   ->join('reimbursements_statuses', 'reimbursements_logs.reimbursement_status_id', '=', 'reimbursements_statuses.id');
                   
        switch ($option) {
            case 'approver':
                $orm = $orm->join('reimbursements', function(JoinClause $join) {
                            $join->on('reimbursements_logs.reimbursement_id', '=', 'reimbursements.id')
                                 ->whereNull('reimbursements.deleted_at');
                            });
                
                $status = ReimbursementStatus::select('id')->where('name', 'Dikembalikan')->first();
                $orm = $orm->whereNot('reimbursements_logs.reimbursement_status_id', $status->id);
                break;

            case 'self':
                $orm = $orm->join('reimbursements', function(JoinClause $join) {
                            $join->on('reimbursements_logs.reimbursement_id', '=', 'reimbursements.id')
                                 ->whereNull('reimbursements.deleted_at');
                            })
                           ->where('reimbursements_logs.owner_id', '=', Auth::id());
                break;
            
            default:
                $orm = $orm->join('reimbursements', 'reimbursements_logs.reimbursement_id', '=', 'reimbursements.id');
                break;
        }

        // get result
        $orm = $orm->join('reimbursements_categories', 'reimbursements.reimbursement_category_id', '=', 'reimbursements_categories.id')
                   ->where('reimbursements_logs.reimbursement_id', '=', $input['id'])
                   ->orderBy('reimbursements_logs.created_at', 'desc');

        // $orm->dd();

        $result = $orm->get();

        // parse result
        foreach ($result as $i => $resultItem):

            $result[$i] = ReimbursementLibrary::parseLog($resultItem->toArray());

        endforeach;

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $result,
        ], 200);
    }

    //=============================================================================================

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
}
