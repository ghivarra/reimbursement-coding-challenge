<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CsrfController extends Controller
{
    public function get(): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'berhasil menarik data',
            'data'    => [
                'token' => csrf_token(),
            ],
        ], 200);
    }

    //================================================================================================
}