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
        return Schedule::with(['bus', 'route', 'bookings.passenger'])->findOrFail($id); //hypothetical schedule detail page, the code below too is also valid if i dont use these hypothetical values (its only for future improvements)
        
        // return Schedule::findOrFail($id);
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

    public function seatSummary(string $search = ''): Collection
    {
        return Schedule::with(['bus', 'route'])
            ->withCount([
                'bookings as confirmed_bookings' => fn($q) => $q->where('booking_status', 'Confirmed')
            ])
            ->when($search, fn($q) => $q
                ->whereHas('route', fn($r) => $r
                    ->where('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                )
                ->orWhereHas('bus', fn($b) => $b
                    ->where('bus_name', 'like', "%{$search}%")
                )
            )
            ->orderBy('departure_time')
            ->get()
            ->each(function (Schedule $schedule) {
                $schedule->remaining_seats = $schedule->bus->total_seats - $schedule->confirmed_bookings;
                $schedule->current_fare    = $schedule->fare_override ?? $schedule->route->base_fare;
            });
    }
}