<?php

namespace App\Http\Controllers\Panel\Reimbursement;

use App\Http\Controllers\Controller;
use App\Models\ReimbursementStatus;
use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function findAll(): JsonResponse
    {
        $data = ReimbursementStatus::orderBy('name', 'asc')->get();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $data,
        ], 200);
    }

    //=============================================================================================
}
