<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\RoleController;
use Illuminate\Http\JsonResponse;

Route::prefix('/panel')->group(function() {

    // get csrf
    Route::get('csrf', function(): JsonResponse {
        return response()->json([
            'status'  => 'success',
            'message' => 'berhasil menarik data',
            'data'    => [
                'token' => csrf_token(),
            ],
        ], 200);
    });

    // roles
    Route::prefix('role')->group(function() {
        
        // index
        Route::post('/', [RoleController::class, 'index'])->name('role.index');
    });

})->middleware('auth');