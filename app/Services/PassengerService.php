<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Passenger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PassengerService
{
    public function paginate(string $search = '', string $type = '', string $status = '', int $perPage = 15): LengthAwarePaginator
    {
        return Passenger::query()
            ->when($search, fn($q) => $q->search($search))
            ->when($type,   fn($q) => $q->byType($type))
            ->when($status, fn($q) => $q->where('account_status', $status))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $id): Passenger
    {
        return Passenger::with(['bookings.schedule.route', 'payments'])->findOrFail($id);
    }

    public function create(array $data): Passenger
    {
        $passenger = Passenger::create($data);

        AuditLog::write(
            'NEW_PASSENGER',
            'passengers',
            $passenger->id,
            "New passenger registered: {$passenger->full_name} ({$passenger->passenger_type})"
        );

        return $passenger;
    }

    public function update(int $id, array $data): Passenger
    {
        $passenger = Passenger::findOrFail($id);
        $passenger->update($data);
        return $passenger->fresh();
    }

    public function reactivate(int $id): Passenger
    {
        $passenger = Passenger::findOrFail($id);
        $passenger->update(['account_status' => 'Active']);

        AuditLog::write(
            'REACTIVATE', 'passengers', $passenger->passenger_id,
            "Passenger {$passenger->full_name} reactivated."
        );

        return $passenger->fresh();
    }

    public function delete(int $id): void
    {
        Passenger::findOrFail($id)->delete();
    }

    public function reportCard(): \Illuminate\Support\Collection
    {
        return Passenger::query()
            ->leftJoin('bookings', 'passengers.passenger_id', '=', 'bookings.passenger_id')
            ->leftJoin('payments', 'bookings.booking_id', '=', 'payments.booking_id')
            ->selectRaw('
                passengers.passenger_id,
                passengers.full_name,
                passengers.passenger_type,
                passengers.account_status,
                COUNT(DISTINCT bookings.booking_id)                                    AS total_bookings,
                SUM(IF(bookings.booking_status = "Completed", 1, 0))                  AS completed_trips,
                SUM(IF(bookings.booking_status = "Cancelled", 1, 0))                  AS cancellations,
                COALESCE(SUM(payments.amount_paid), 0)                                AS total_spent,
                IF(passengers.account_status = "Active", "Eligible", "Not Eligible")  AS booking_eligibility,
                CASE
                    WHEN COUNT(DISTINCT bookings.booking_id) >= 10 THEN "Platinum Traveler"
                    WHEN COUNT(DISTINCT bookings.booking_id) >= 5  THEN "Gold Traveler"
                    WHEN COUNT(DISTINCT bookings.booking_id) >= 2  THEN "Silver Traveler"
                    WHEN COUNT(DISTINCT bookings.booking_id) >= 1  THEN "New Traveler"
                    ELSE "Inactive"
                END AS loyalty_tier
            ')
            ->groupBy('passengers.passenger_id', 'passengers.full_name', 'passengers.passenger_type', 'passengers.account_status')
            ->orderByDesc('total_spent')
            ->get();
    }
}
