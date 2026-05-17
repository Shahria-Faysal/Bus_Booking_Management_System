<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function paginate(string $status = '', int $passengerId = 0, int $perPage = 15): LengthAwarePaginator
    {
        return Booking::with(['passenger', 'schedule.bus', 'schedule.route', 'payment'])
            ->when($status,      fn($q) => $q->where('booking_status', $status))
            ->when($passengerId, fn($q) => $q->where('passenger_id', $passengerId))
            ->orderByDesc('journey_date')
            ->paginate($perPage);
    }

    public function find(int $id): Booking
    {
        return Booking::with(['passenger', 'schedule.bus', 'schedule.route', 'payment'])->findOrFail($id);
    }

    /**
     * Create a booking with all business rules applied:
     * - Block suspended / blacklisted passengers
     * - Block if no seats available
     * - Apply passenger-type discount automatically
     * - Decrement available seats
     * - Auto-create pending payment record
     * - Write audit log
     */
    public function create(array $data): Booking
    {
        $passenger = Passenger::findOrFail($data['passenger_id']);
        $schedule  = Schedule::with('route')->findOrFail($data['schedule_id']);

        if ($passenger->account_status === 'Suspended') {
            throw new \Exception('Passenger account is suspended. Cannot book.');
        }
        if ($passenger->account_status === 'Blacklisted') {
            throw new \Exception('Passenger is blacklisted. Cannot book.');
        }
        if ($schedule->available_seats <= 0) {
            throw new \Exception('No seats available on this schedule.');
        }

        $discountPct = match ($passenger->passenger_type) {
            'Student' => 10.00,
            'Senior'  => 15.00,
            'VIP'     => 20.00,
            default   => 0.00,
        };

        $baseFare = (float) ($schedule->fare_override ?? $schedule->route->base_fare);
        $farePaid = round($baseFare * (1 - $discountPct / 100), 2);

        return DB::transaction(function () use ($data, $schedule, $passenger, $farePaid, $discountPct) {
            $booking = Booking::create([
                'schedule_id'    => $data['schedule_id'],
                'passenger_id'   => $data['passenger_id'],
                'seat_number'    => $data['seat_number'],
                'journey_date'   => $data['journey_date'],
                'notes'          => $data['notes'] ?? null,
                'fare_paid'      => $farePaid,
                'discount_pct'   => $discountPct,
                'booking_status' => 'Confirmed',
            ]);

            $schedule->decrement('available_seats');

            Payment::create([
                'booking_id'     => $booking->booking_id,
                'passenger_id'   => $passenger->passenger_id,
                'amount_due'     => $farePaid,
                'amount_paid'    => 0,
                'payment_status' => 'Pending',
            ]);

            AuditLog::write(
                'BOOKING', 'bookings', $booking->booking_id,
                "Passenger ID {$passenger->passenger_id} booked Seat {$booking->seat_number} on Schedule ID {$schedule->schedule_id}"
            );

            return $booking->load(['passenger', 'schedule.route', 'payment']);
        });
    }

    public function cancel(int $id, string $reason = 'Passenger request'): Booking
    {
        $booking = Booking::with('schedule')->findOrFail($id);

        if ($booking->booking_status === 'Cancelled') {
            throw new \Exception('Booking is already cancelled.');
        }

        return DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'booking_status'      => 'Cancelled',
                'cancellation_reason' => $reason,
            ]);

            $booking->schedule->increment('available_seats');

            AuditLog::write(
                'CANCEL', 'bookings', $booking->booking_id,
                "Booking ID {$booking->booking_id} cancelled. Seat {$booking->seat_number} restored."
            );

            return $booking->fresh();
        });
    }

    public function complete(int $id): Booking
    {
        $booking = Booking::with('passenger')->findOrFail($id);

        if ($booking->booking_status !== 'Confirmed') {
            throw new \Exception('Only confirmed bookings can be completed.');
        }

        return DB::transaction(function () use ($booking) {
            $booking->update(['booking_status' => 'Completed']);
            $booking->passenger->increment('total_trips');

            AuditLog::write(
                'TRIP_COMPLETE', 'bookings', $booking->booking_id,
                "Passenger ID {$booking->passenger_id} completed trip. Booking ID {$booking->booking_id}"
            );

            return $booking->fresh();
        });
    }

    public function bulkNoShow(): int
    {
        return Booking::where('journey_date', '<', now()->toDateString())
            ->where('booking_status', 'Confirmed')
            ->update(['booking_status' => 'No-Show']);
    }

    public function monthlySummary(): \Illuminate\Support\Collection
    {
        return Booking::query()
            ->leftJoin('payments', 'bookings.booking_id', '=', 'payments.booking_id')
            ->selectRaw('
                YEAR(bookings.journey_date)                              AS journey_year,
                MONTH(bookings.journey_date)                             AS journey_month,
                COUNT(bookings.booking_id)                               AS total_bookings,
                SUM(IF(bookings.booking_status = "Completed", 1, 0))    AS completed,
                SUM(IF(bookings.booking_status = "Cancelled", 1, 0))    AS cancelled,
                ROUND(SUM(bookings.fare_paid), 2)                       AS gross_revenue,
                ROUND(COALESCE(SUM(payments.refund_amount), 0), 2)      AS total_refunds,
                ROUND(SUM(bookings.fare_paid) - COALESCE(SUM(payments.refund_amount), 0), 2) AS net_revenue
            ')
            ->groupByRaw('YEAR(bookings.journey_date), MONTH(bookings.journey_date)')
            ->orderByRaw('journey_year, journey_month')
            ->get();
    }
}
