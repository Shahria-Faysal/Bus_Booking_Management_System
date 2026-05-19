<?php

namespace App\Livewire\Bookings;

use App\Models\Passenger;
use App\Models\Schedule;
use App\Services\BookingService;
use Livewire\Component;

class CreateBooking extends Component
{
    // Form fields
    public string $schedule_id  = '';
    public string $passenger_id = '';
    public string $seat_number  = '';
    public string $journey_date = '';
    public string $notes        = '';

    // UI state
    public ?float  $previewFare     = null;
    public ?string $previewDiscount = null;
    public ?string $flashMessage    = null;
    public ?string $flashType       = null;

    protected array $rules = [
        'schedule_id'  => 'required|integer|min:1',
        'passenger_id' => 'required|integer|min:1',
        'seat_number'  => 'required|string|max:10',
        'journey_date' => 'required|date|after_or_equal:today',
        'notes'        => 'nullable|string|max:500',
    ];

    /**
     * When a passenger or schedule is selected, preview the fare + discount.
     */
    public function updatedPassengerId(): void { $this->previewFare(); }
    public function updatedScheduleId(): void  { $this->previewFare(); }

    public function previewFare(): void
    {
        if (!$this->passenger_id || !$this->schedule_id) {
            $this->previewFare     = null;
            $this->previewDiscount = null;
            return;
        }

        $passenger = Passenger::find($this->passenger_id);
        $schedule  = Schedule::with('route')->find($this->schedule_id);

        if (!$passenger || !$schedule) return;

        $fare = app(BookingService::class)->calculateFare($passenger, $schedule);

        $this->previewFare = $fare['fare_paid'];
        $this->previewDiscount = $fare['discount_pct'] > 0
            ? "{$fare['discount_pct']}% {$passenger->passenger_type} discount applied"
            : null;
    }

    public function save(BookingService $service): void
    {
        $this->validate();

        try {
            $booking = $service->create([
                'schedule_id'  => $this->schedule_id,
                'passenger_id' => $this->passenger_id,
                'seat_number'  => $this->seat_number,
                'journey_date' => $this->journey_date,
                'notes'        => $this->notes,
            ]);

            $this->flashMessage = "Booking #{$booking->booking_id} confirmed!";
            $this->flashType    = 'success';
            $this->reset(['schedule_id', 'passenger_id', 'seat_number', 'journey_date', 'notes', 'previewFare', 'previewDiscount']);

        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType    = 'error';
        }
    }

    public function render()
    {
        $schedules = Schedule::with('route')
            ->where('schedule_status', 'Scheduled')
            ->where('available_seats', '>', 0)
            ->orderBy('departure_time')
            ->get();

        $passengers = Passenger::active()->orderBy('name')->get();

        return view('livewire.bookings.create-booking', compact('schedules', 'passengers'));
    }
}
