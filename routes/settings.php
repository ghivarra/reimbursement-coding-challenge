<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Library\RoleManagementLibrary;
use Illuminate\Support\Facades\Auth;
// use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('panel')->group(function() {
    Route::middleware('auth')->group(function () {
        Route::redirect('settings', function() {
            return redirect()->route('password.edit');
        });

        // Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        // Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
        // Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

        Route::get('settings/appearance', function () {
            $roleLib = new RoleManagementLibrary();
            return Inertia::render('settings/Appearance', [
                'access' => $roleLib->getUserAccess(Auth::id())
            ]);
        })->name('appearance');
    });
});
