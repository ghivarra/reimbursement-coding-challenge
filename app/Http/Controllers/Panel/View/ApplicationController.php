<?php

namespace App\Http\Controllers\Panel\View;

use App\Http\Controllers\Controller;
use App\Library\RoleManagementLibrary;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('ApplicationCreateForm', [
            'csrfHash' => csrf_token(),
            'access'   => session('access')
        ]);
    }

    //===================================================================================================

    public function index(): Response
    {
        // find only correct index route
        $roleLib = new RoleManagementLibrary();
        $access  = $roleLib->getUserAccess(Auth::id());
        $route   = $roleLib->getRoleRoute('reimbursement.main.index', $access['modules']);

        return Inertia::render('Application', [
            'endpoint' => route($route),
            'csrfHash' => csrf_token(),
            'access'   => session('access')
        ]);
    }

    //===================================================================================================

    

    //===================================================================================================
}
