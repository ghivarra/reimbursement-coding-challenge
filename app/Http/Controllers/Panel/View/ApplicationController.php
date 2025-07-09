<?php

namespace App\Http\Controllers\Panel\View;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Application', [
            'access' => session('access')
        ]);
    }

    //===================================================================================================
}
