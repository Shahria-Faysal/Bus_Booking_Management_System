<?php

namespace App\Http\Controllers;

// use App\Models\Passenger;
// use App\Models\Schedule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        return view('pages.bookings.index');
    }

    public function create(): View
    {
        // Pass dropdown data needed before Livewire takes over
        // $schedules  = Schedule::with('route')
        //     ->where('schedule_status', 'Scheduled')
        //     ->where('available_seats', '>', 0)
        //     ->orderBy('departure_time')
        //     ->get();

        // $passengers = Passenger::active()->orderBy('name')->get();

        return view('pages.bookings.create');
    }

    public function show(int $id): View
    {
        return view('pages.bookings.show', ['bookingId' => $id]);
    }
}
