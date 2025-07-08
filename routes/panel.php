<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\JsonResponse;
use App\Http\Middleware\RoleCheck;
use App\Http\Controllers\Panel\RoleController;
use App\Http\Controllers\Panel\UserController;

// get csrf
Route::get('csrf', function(): JsonResponse {
    return response()->json([
        'status'  => 'success',
        'message' => 'berhasil menarik data',
        'data'    => [
            'token' => csrf_token(),
        ],
    ], 200);
})->name('panel.csrf');

Route::middleware(['auth'])->prefix('/panel')->group(function() {

    // roles
    Route::middleware(RoleCheck::class)->prefix('role')->group(function() {
        Route::post('/', [RoleController::class, 'index'])->name('role.index');
        Route::get('/find', [RoleController::class, 'find'])->name('role.find');
        Route::post('/create', [RoleController::class, 'create'])->name('role.create');
        Route::patch('/update', [RoleController::class, 'update'])->name('role.update');
        Route::delete('/delete', [RoleController::class, 'delete'])->name('role.delete');
    });

    // user
    Route::middleware(RoleCheck::class)->prefix('user')->group(function() {
        Route::post('/', [UserController::class, 'index'])->name('user.index');
        Route::get('/find', [UserController::class, 'find'])->name('user.find');
        Route::post('/create', [UserController::class, 'create'])->name('user.create');
        Route::patch('/update', [UserController::class, 'update'])->name('user.update');
        Route::delete('/delete', [UserController::class, 'delete'])->name('user.delete');
    });

});