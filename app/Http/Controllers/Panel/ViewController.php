<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Library\RoleManagementLibrary;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ViewController extends Controller
{
    public function dashboard(): Response
    {
        $roleLib = new RoleManagementLibrary();

        return Inertia::render('Dashboard', [
            'csrfHash' => csrf_token(),
            'access'   => $roleLib->getUserAccess(Auth::id())
        ]);
    }

    //=====================================================================================================

    public function category(): Response
    {
        return Inertia::render('Category', [
            'csrfHash' => csrf_token(),
            'access'   => session('access')
        ]);
    }

    //=====================================================================================================
}