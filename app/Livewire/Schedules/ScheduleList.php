<?php

namespace App\Livewire\Schedules;

use App\Models\Bus;
use App\Models\Route;
use App\Services\ScheduleService;
use Livewire\Component;
use Livewire\WithPagination;

class ScheduleList extends Component
{
    use WithPagination;

    // ── Filters ───────────────────────────────────────────────
    public string $status = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    // ── Flash ─────────────────────────────────────────────────
    public ?string $flashMessage = null;
    public ?string $flashType = null;

    // ── Modal state ───────────────────────────────────────────
    public bool $showModal = false;
    public ?int $editingId = null;

    // ── Form fields ───────────────────────────────────────────
    public string $bus_id = '';
    public string $route_id = '';
    public string $departure_time = '';
    public string $arrival_time = '';
    public int $available_seats = 0;
    public string $fare_override = '';
    public string $schedule_status = 'Scheduled';

    protected function rules(): array
    {
        return [
            'bus_id' => 'required|integer|min:1',
            'route_id' => 'required|integer|min:1',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'available_seats' => 'required|integer|min:0',
            'fare_override' => 'nullable|numeric|min:0',
            'schedule_status' => 'required|in:Scheduled,Departed,Arrived,Cancelled',
        ];
    }

    // ── Filter watchers ───────────────────────────────────────
    public function updatedStatus(): void
    {
        $this->resetPage();
    }
    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }
    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    // ── Modal helpers ─────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id, ScheduleService $service): void
    {
        $schedule = $service->find($id);

        $this->editingId = $id;
        $this->bus_id = (string) $schedule->bus_id;
        $this->route_id = (string) $schedule->route_id;
        $this->departure_time = $schedule->departure_time->format('Y-m-d\TH:i');
        $this->arrival_time = $schedule->arrival_time->format('Y-m-d\TH:i');
        $this->available_seats = $schedule->available_seats;
        $this->fare_override = $schedule->fare_override ?? '';
        $this->schedule_status = $schedule->schedule_status;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'bus_id',
            'route_id',
            'departure_time',
            'arrival_time',
            'available_seats',
            'fare_override',
            'editingId',
        ]);
        $this->schedule_status = 'Scheduled';
    }

    // ── CRUD actions ──────────────────────────────────────────
    public function save(ScheduleService $service): void
    {
        $this->validate();

        $data = [
            'bus_id' => $this->bus_id,
            'route_id' => $this->route_id,
            'departure_time' => $this->departure_time,
            'arrival_time' => $this->arrival_time,
            'available_seats' => $this->available_seats,
            'fare_override' => $this->fare_override !== '' ? $this->fare_override : null,
            'schedule_status' => $this->schedule_status,
        ];

        try {
            if ($this->editingId) {
                $service->update($this->editingId, $data);
                $this->flashMessage = 'Schedule updated successfully.';
            } else {
                $service->create($data);
                $this->flashMessage = 'Schedule created successfully.';
            }
            $this->flashType = 'success';
            $this->closeModal();
        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType = 'error';
        }
    }

    public function cancel(int $id, ScheduleService $service): void
    {
        try {
            $service->cancel($id);
            $this->flashMessage = 'Schedule and its bookings cancelled.';
            $this->flashType = 'success';
        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType = 'error';
        }
    }

    public function delete(int $id, ScheduleService $service): void
    {
        try {
            $service->delete($id);
            $this->flashMessage = 'Schedule deleted.';
            $this->flashType = 'success';
        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType = 'error';
        }
    }

    // ── Render ────────────────────────────────────────────────
    public function render(ScheduleService $service)
    {
        $schedules = $service->getAll(
            status: $this->status,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
        );

        $buses = Bus::active()->orderBy('bus_name')->get(['bus_id', 'bus_name', 'bus_type']);
        $routes = Route::orderBy('origin')->get(['route_id', 'origin', 'destination', 'base_fare']);

        return view('livewire.schedules.schedule-list', compact('schedules', 'buses', 'routes'));
    }
}