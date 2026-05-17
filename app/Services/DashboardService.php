<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function summary(): array
    {
        return [
            'active_buses'             => DB::table('buses')->where('status', 'Active')->count(),
            'upcoming_trips'           => DB::table('schedules')->where('schedule_status', 'Scheduled')->count(),
            'active_passengers'        => DB::table('users')->where('account_status', 'Active')->count(),
            'confirmed_bookings'       => DB::table('bookings')->where('booking_status', 'Confirmed')->count(),
            'cancelled_bookings'       => DB::table('bookings')->where('booking_status', 'Cancelled')->count(),
            'total_revenue_collected'  => DB::table('payments')->where('payment_status', 'Paid')->sum('amount_paid'),
            'outstanding_balance'      => DB::table('payments')
                ->whereIn('payment_status', ['Pending', 'Partial'])
                ->selectRaw('COALESCE(SUM(amount_due - amount_paid), 0) AS balance')
                ->value('balance') ?? 0,
        ];
    }

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
