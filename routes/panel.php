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
use App\Http\Controllers\Panel\View\ApplicationController;
use App\Http\Controllers\Panel\View\ApprovalController;
use App\Http\Controllers\Panel\View\RoleController as ViewRoleController;
use App\Http\Controllers\Panel\ViewController;
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

        //========================================= VIEWS ======================================================//

        Route::prefix('approval')->group(function() {
            Route::get('/', [ApprovalController::class, 'index'])->name('view.approval');
            Route::get('/examine', [ApprovalController::class, 'examine'])->name('view.approval.examine');
        });

        Route::prefix('application')->group(function() {
            Route::get('/', [ApplicationController::class, 'index'])->name('view.application');
            Route::get('/create', [ApplicationController::class, 'create'])->name('view.application.create');
            Route::get('/update', [ApplicationController::class, 'update'])->name('view.application.update');
        });

        Route::prefix('role-view')->group(function() {
            Route::get('/', [ViewRoleController::class, 'role'])->name('view.role');
            Route::get('/update-module', [ViewRoleController::class, 'role'])->name('view.role.update.module');
            Route::get('/update-menu', [ViewRoleController::class, 'role'])->name('view.role.update.menu');
        });

        Route::get('/archive', [ViewController::class, 'archive'])->name('view.archive');
        Route::get('/user-view', [ViewController::class, 'user'])->name('view.user');

        

        //========================================= ENDPOINT ======================================================//

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
            Route::post('/update-module', [RoleController::class, 'updateModules'])->name('role.update.modules');
            Route::post('/update-menu', [RoleController::class, 'updateMenus'])->name('role.update.menus');
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
                Route::get('/check-limit', [CategoryController::class, 'checkLimit'])->name('reimbursement.category.check.limit');
            });

            // main
            Route::prefix('main')->group(function() {

                // different indexes for different roles
                Route::prefix('index')->group(function() {
                    Route::post('/with-removed', [MainController::class, 'indexWithRemoved'])->name('reimbursement.main.index.with.removed');
                    Route::post('/self', [MainController::class, 'indexSelf'])->name('reimbursement.main.index.self');
                    Route::post('/approver', [MainController::class, 'indexApprover'])->name('reimbursement.main.index.approver');
                });

                // different find for different roles
                Route::prefix('find')->group(function() {
                    Route::get('/with-removed', [MainController::class, 'findWithRemoved'])->name('reimbursement.main.find.with.removed');
                    Route::get('/self', [MainController::class, 'findSelf'])->name('reimbursement.main.find.self');
                    Route::get('/approver', [MainController::class, 'findApprover'])->name('reimbursement.main.find.approver');
                });

                Route::post('/create', [MainController::class, 'create'])->name('reimbursement.main.create');
                Route::patch('/update', [MainController::class, 'update'])->name('reimbursement.main.update');
                Route::delete('/delete', [MainController::class, 'delete'])->name('reimbursement.main.delete');
                Route::post('/respond', [MainController::class, 'respond'])->name('reimbursement.main.respond');
            });

            // different log for different roles
            Route::prefix('log/find')->group(function() {
                Route::get('/with-removed', [LogController::class, 'findWithRemoved'])->name('reimbursement.log.find.with.removed');
                Route::get('/self', [LogController::class, 'findSelf'])->name('reimbursement.log.find.self');
                Route::get('/approver', [LogController::class, 'findApprover'])->name('reimbursement.log.find.approver');
            });
        });
    });


});