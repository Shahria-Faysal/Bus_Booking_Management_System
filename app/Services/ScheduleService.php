<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    /**
     * All schedules with optional status + date range filters.
     */
    public function getAll(string $status = '', string $dateFrom = '', string $dateTo = ''): Collection
    {
        return Schedule::with(['bus', 'route'])
            ->when($status, fn($q) => $q->where('schedule_status', $status))
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween(
                DB::raw('DATE(departure_time)'), [$dateFrom, $dateTo]
            ))
            ->when($dateFrom && !$dateTo, fn($q) => $q->whereDate('departure_time', '>=', $dateFrom))
            ->when(!$dateFrom && $dateTo, fn($q) => $q->whereDate('departure_time', '<=', $dateTo))
            ->orderBy('departure_time')
            ->get();
    }

    /**
     * Available schedules only (for booking dropdown).
     */
    public function getAvailable(): Collection
    {
        return Schedule::with(['bus', 'route'])
            ->where('available_seats', '>', 0)
            ->where('schedule_status', 'Scheduled')
            ->orderBy('departure_time')
            ->get();
    }

    public function find(int $id): Schedule
    {
        return Schedule::with(['bus', 'route', 'bookings.passenger'])->findOrFail($id);
    }

    public function create(array $data): Schedule
    {
        return Schedule::create($data);
    }

    public function update(int $id, array $data): Schedule
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update($data);
        return $schedule->fresh(['bus', 'route']);
    }

    public function cancel(int $id): void
    {
        \DB::transaction(function () use ($id) {
            $schedule = Schedule::findOrFail($id);
            $schedule->update(['schedule_status' => 'Cancelled']);
            $schedule->bookings()
                ->where('booking_status', 'Confirmed')
                ->update([
                    'booking_status'      => 'Cancelled',
                    'cancellation_reason' => 'Schedule cancelled by operator',
                ]);
        });
    }

    public function delete(int $id): void
    {
        $schedule  = Schedule::findOrFail($id);
        $activeCount = $schedule->bookings()->where('booking_status', 'Confirmed')->count();

        if ($activeCount > 0) {
            throw new \Exception('Cannot delete a schedule with confirmed bookings.');
        }

        $schedule->delete();
    }

    public function seatSummary(): Collection
    {
        return Schedule::query()
            ->join('buses', 'schedules.bus_id', '=', 'buses.bus_id')
            ->join('routes', 'schedules.route_id', '=', 'routes.route_id')
            ->leftJoin('bookings', function ($join) {
                $join->on('schedules.schedule_id', '=', 'bookings.schedule_id')
                     ->where('bookings.booking_status', '=', 'Confirmed');
            })
            ->selectRaw('
                schedules.schedule_id,
                buses.bus_name,
                routes.origin,
                routes.destination,
                schedules.departure_time,
                buses.total_seats,
                COUNT(bookings.booking_id)                          AS confirmed_bookings,
                (buses.total_seats - COUNT(bookings.booking_id))    AS remaining_seats,
                COALESCE(schedules.fare_override, routes.base_fare) AS current_fare
            ')
            ->groupBy(
                'schedules.schedule_id', 'buses.bus_name', 'buses.total_seats',
                'routes.origin', 'routes.destination', 'schedules.departure_time',
                'schedules.fare_override', 'routes.base_fare'
            )
            ->orderBy('schedules.departure_time')
            ->get();
    }
}