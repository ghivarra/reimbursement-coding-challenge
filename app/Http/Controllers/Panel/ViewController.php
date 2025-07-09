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
            'access' => $roleLib->getUserAccess(Auth::id())
        ]);
    }

    //=====================================================================================================
}