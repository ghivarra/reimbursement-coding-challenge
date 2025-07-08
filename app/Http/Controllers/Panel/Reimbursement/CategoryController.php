<?php

namespace App\Http\Controllers\Panel\Reimbursement;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function findAll(): JsonResponse
    {
        $data = Menu::all();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $data,
        ], 200);
    }

    //=============================================================================================
}
