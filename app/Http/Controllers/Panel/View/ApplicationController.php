<?php

namespace App\Http\Controllers\Panel\View;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('ApplicationCreateForm', [
            'access' => session('access')
        ]);
    }

    //===================================================================================================

    public function index(): Response
    {
        return Inertia::render('Application', [
            'access' => session('access')
        ]);
    }

    //===================================================================================================



    //===================================================================================================
}
