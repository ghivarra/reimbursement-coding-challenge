<?php

namespace App\Http\Controllers\Panel\View;

use App\Http\Controllers\Controller;
use App\Library\RoleManagementLibrary;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Termwind\Components\Raw;

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

    public function examine(string $id): Response
    {
        // get id
        $application = Reimbursement::withTrashed()->where('id', $id)->first();

        // abort if empty
        if (empty($application))
        {
            abort(404, 'Pengajuan reimbursement tidak ditemukan');
        }

        // set endpoint
        $roleLib     = new RoleManagementLibrary();
        $access      = session('access');
        $endpoint    = $roleLib->getRoleRoute('reimbursement.main.find', $access['modules']);
        $logEndpoint = $roleLib->getRoleRoute('reimbursement.log.find', $access['modules']);

        return Inertia::render('ApplicationExamine', [
            'id'          => $id,
            'csrfHash'    => csrf_token(),
            'endpoint'    => route($endpoint),
            'logEndpoint' => route($logEndpoint),
            'access'      => $access,
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
