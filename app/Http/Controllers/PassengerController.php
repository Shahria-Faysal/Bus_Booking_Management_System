<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PassengerController extends Controller
{
    public function index(): View
    {
        return view('pages.passengers.index');
    }

    public function show(int $id): View
    {
        return view('pages.passengers.show', ['passengerId' => $id]);
    }
}
