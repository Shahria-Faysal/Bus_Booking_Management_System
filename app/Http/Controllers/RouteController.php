<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RouteController extends Controller
{
    public function index(): View
    {
        return view('pages.routes.index');
    }
}
