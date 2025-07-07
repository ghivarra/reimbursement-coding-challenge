<?php

namespace App\Http\Middleware;

use App\Library\RoleManagementLibrary;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response | RedirectResponse | JsonResponse
    {
        // check route now
        $routeName = $request->route()->getName();

        if (empty($routeName))
        {
            return $next($request);
        }

        // get id
        $userID = Auth::id();

        // init role management library
        $roleManagementLib = new RoleManagementLibrary();
        $valid = $roleManagementLib->validateAccess($userID, $routeName);

        // if no longer active then kick
        if (!$valid)
        {
            return response()->json([
                'status'   => 'error',
                'messages' => 'Anda tidak memiliki izin untuk mengakses halaman ini.'
            ], 403);
        }

        // next
        return $next($request);
    }
}