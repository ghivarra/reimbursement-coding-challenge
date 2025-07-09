<?php

namespace App\Http\Controllers\Panel\View;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Approval', [
            'csrfHash' => csrf_token(),
            'access'   => session('access')
        ]);
    }

    //===========================================================================================
}
