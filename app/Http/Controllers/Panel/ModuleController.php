<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function findAll(): JsonResponse
    {
        $data = Module::all();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $data,
        ], 200);
    }

    //=============================================================================================
}
