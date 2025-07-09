<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Library\RoleManagementLibrary;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ViewController extends Controller
{
    public function dashboard(): Response
    {
        $roleLib = new RoleManagementLibrary();
        $userID  = Auth::id();
        $user    = User::select('users.id', 'users.name', 'users.email', 'users.role_id', 'roles.name as role_name')
                       ->join('roles', 'role_id', '=', 'roles.id')
                       ->where('users.id', $userID)
                       ->first();

        return Inertia::render('Dashboard', [
            'userData' => $user,
            'csrfHash' => csrf_token(),
            'access'   => $roleLib->getUserAccess($userID)
        ]);
    }

    //=====================================================================================================

    public function archive(): Response
    {
        return Inertia::render('Archive', [
            'csrfHash' => csrf_token(),
            'access'   => session('access')
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