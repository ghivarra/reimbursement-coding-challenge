<?php

use App\Http\Controllers\CsrfController;
use App\Http\Controllers\Panel\MenuController;
use App\Http\Controllers\Panel\ModuleController;
use App\Http\Controllers\Panel\Reimbursement\CategoryController;
use App\Http\Controllers\Panel\Reimbursement\LogController;
use App\Http\Controllers\Panel\Reimbursement\MainController;
use App\Http\Controllers\Panel\Reimbursement\StatusController;
use App\Http\Controllers\Panel\RoleController;
use App\Http\Controllers\Panel\UserController;
use App\Http\Middleware\RoleCheck;
use Illuminate\Support\Facades\Route;

// get csrf
Route::get('csrf', [CsrfController::class, 'get'])->name('panel.csrf');

// guarded auth
Route::middleware(['auth'])->prefix('/panel')->group(function() {    

    // Public group
    Route::get('/reimbursement/status/find-all', [StatusController::class, 'findAll'])->name('reimbursement.status.find.all');
    Route::get('/reimbursement/category/find-all', [CategoryController::class, 'findAll'])->name('reimbursement.category.find.all');

    // Guarded Group
    Route::middleware(RoleCheck::class)->group(function() {

        // get all menu / modules
        Route::get('/modules/find-all', [ModuleController::class, 'findAll'])->name('module.find.all');
        Route::get('/menus/find-all', [MenuController::class, 'findAll'])->name('menu.find.all');

        // roles
        Route::prefix('role')->group(function() {
            Route::post('/', [RoleController::class, 'index'])->name('role.index');
            Route::get('/find', [RoleController::class, 'find'])->name('role.find');
            Route::post('/create', [RoleController::class, 'create'])->name('role.create');
            Route::patch('/update', [RoleController::class, 'update'])->name('role.update');
            Route::delete('/delete', [RoleController::class, 'delete'])->name('role.delete');
            Route::post('/update-modules', [RoleController::class, 'create'])->name('role.update.modules');
            Route::post('/update-roles', [RoleController::class, 'create'])->name('role.update.menus');
        });

        // user
        Route::prefix('user')->group(function() {
            Route::post('/', [UserController::class, 'index'])->name('user.index');
            Route::get('/find', [UserController::class, 'find'])->name('user.find');
            Route::post('/create', [UserController::class, 'create'])->name('user.create');
            Route::patch('/update', [UserController::class, 'update'])->name('user.update');
            Route::delete('/delete', [UserController::class, 'delete'])->name('user.delete');
        });

        // reimbursement
        Route::prefix('reimbursement')->group(function() {

            // category
            Route::prefix('category')->group(function() {
                Route::post('/', [CategoryController::class, 'index'])->name('reimbursement.category.index');
                Route::get('/find', [CategoryController::class, 'find'])->name('reimbursement.category.find');
                Route::post('/create', [CategoryController::class, 'create'])->name('reimbursement.category.create');
                Route::patch('/update', [CategoryController::class, 'update'])->name('reimbursement.category.update');
                Route::delete('/delete', [CategoryController::class, 'delete'])->name('reimbursement.category.delete');
                Route::delete('/check-limit', [CategoryController::class, 'checkLimit'])->name('reimbursement.category.check.limit');
            });

            // main
            Route::prefix('main')->group(function() {

                // different indexes for different roles
                Route::prefix('index')->group(function() {
                    Route::post('/with-removed', [MainController::class, 'indexWithRemoved'])->name('reimbursement.main.index.with.removed');
                    Route::post('/self', [MainController::class, 'indexOwn'])->name('reimbursement.main.index.self');
                    Route::post('/all', [MainController::class, 'indexAll'])->name('reimbursement.main.index.all');
                });

                // different find for different roles
                Route::prefix('find')->group(function() {
                    Route::get('/with-removed', [MainController::class, 'findWithRemoved'])->name('reimbursement.main.find.with.removed');
                    Route::get('/self', [MainController::class, 'findOwn'])->name('reimbursement.main.find.self');
                    Route::get('/all', [MainController::class, 'findAll'])->name('reimbursement.main.find.all');
                });

                Route::post('/create', [MainController::class, 'create'])->name('reimbursement.main.create');
                Route::patch('/update', [MainController::class, 'update'])->name('reimbursement.main.update');
                Route::delete('/delete', [MainController::class, 'delete'])->name('reimbursement.main.delete');
                Route::delete('/respond', [MainController::class, 'respond'])->name('reimbursement.main.respond');
            });

            // different log for different roles
            Route::prefix('log/find')->group(function() {
                Route::get('/with-removed', [LogController::class, 'findWithRemoved'])->name('reimbursement.log.find.with.removed');
                Route::get('/self', [LogController::class, 'findOwn'])->name('reimbursement.log.find.self');
                Route::get('/all', [LogController::class, 'findAll'])->name('reimbursement.log.find.all');
            });
        });
    });


});