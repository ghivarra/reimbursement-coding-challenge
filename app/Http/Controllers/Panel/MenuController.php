<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function findAll(): JsonResponse
    {
        $data = Menu::orderBy('sort_order', 'asc')->get();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $data,
        ], 200);
    }

    //=============================================================================================
}
