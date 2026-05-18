<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('pages.schedules.index');
    }

    // public function show(int $id): View
    // {
    //     return view('pages.schedules.show', ['scheduleId' => $id]);
    // }

    public function seatSummary(): View
    {
        return view('pages.schedules.seat-summary-page');
    }
}
