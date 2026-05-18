<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class BusController extends Controller
{
    public function index(): View
    {
        return view('pages.buses.index');
    }
}
