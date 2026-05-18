<?php

namespace App\Livewire\Schedules;

use App\Services\ScheduleService;
use Livewire\Component;

class SeatSummary extends Component
{
    public string $search = '';

    public function updatedSearch(): void {} // triggers re-render on input

    public function render(ScheduleService $service)
    {
        $schedules = $service->seatSummary($this->search);

        return view('livewire.schedules.seat-summary', compact('schedules'));
    }
}