<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('pages.payments.index');
    }
}
