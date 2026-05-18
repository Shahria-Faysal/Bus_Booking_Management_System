<?php

namespace App\Services;

use App\Models\Bus;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardService
{
   public function summary(): array
{
    return [
        'active_buses'            => Bus::where('status', 'Active')->count(),
        'upcoming_trips'          => Schedule::where('schedule_status', 'Scheduled')->count(),
        'active_passengers'       => User::where('account_status', 'Active')->count(),
        'confirmed_bookings'      => Booking::where('booking_status', 'Confirmed')->count(),
        'cancelled_bookings'      => Booking::where('booking_status', 'Cancelled')->count(),
        'total_revenue_collected' => Payment::where('payment_status', 'Paid')->sum('amount_paid'),

        // Kept as raw — COALESCE(SUM(col - col)) has no clean Eloquent equivalent
        'outstanding_balance' => DB::table('payments')
            ->whereIn('payment_status', ['Pending', 'Partial'])
            ->selectRaw('COALESCE(SUM(amount_due - amount_paid), 0) AS balance')
            ->value('balance') ?? 0,
    ];
}

// raw — complex join + groupBy + selectRaw adds noise with Eloquent, no real benefit
public function topTravelers(): \Illuminate\Support\Collection
{
    return DB::table('users')
        ->leftJoin('bookings', 'users.id', '=', 'bookings.passenger_id')
        ->selectRaw('
            users.name AS full_name,
            users.passenger_type,
            COUNT(bookings.booking_id) AS booking_count
        ')
        ->groupBy('users.id', 'users.name', 'users.passenger_type')
        ->orderByDesc('booking_count')
        ->limit(5)
        ->get();
}
}
