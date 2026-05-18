<?php

namespace App\Livewire\Routes;

use App\Models\Route;
use Livewire\Component;
use Livewire\WithPagination;

class RouteList extends Component
{
    use WithPagination;

    // ── Filters ───────────────────────────────────────────────
    public string $search = '';

    // ── Flash ─────────────────────────────────────────────────
    public ?string $flashMessage = null;
    public ?string $flashType    = null;

    // ── Modal ─────────────────────────────────────────────────
    public bool $showModal = false;
    public ?int $editingId = null;

    // ── Form fields ───────────────────────────────────────────
    public string $origin         = '';
    public string $destination    = '';
    public string $distance_km    = '';
    public string $base_fare      = '';
    public string $duration_hours = '';

    protected function rules(): array
    {
        return [
            'origin'         => 'required|string|max:100',
            'destination'    => 'required|string|max:100',
            'distance_km'    => 'nullable|numeric|min:0',
            'base_fare'      => 'required|numeric|min:0',
            'duration_hours' => 'nullable|numeric|min:0',
        ];
    }

    protected $validationAttributes = [
        'origin'         => 'origin',
        'destination'    => 'destination',
        'distance_km'    => 'distance',
        'base_fare'      => 'base fare',
        'duration_hours' => 'duration',
    ];

    // ── Filter watchers ───────────────────────────────────────
    public function updatedSearch(): void { $this->resetPage(); }

    // ── Modal helpers ─────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $route = Route::findOrFail($id);

        $this->editingId      = $id;
        $this->origin         = $route->origin;
        $this->destination    = $route->destination;
        $this->distance_km    = $route->distance_km ?? '';
        $this->base_fare      = $route->base_fare;
        $this->duration_hours = $route->duration_hours ?? '';

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['origin', 'destination', 'distance_km', 'base_fare', 'duration_hours', 'editingId']);
        $this->resetValidation();
    }

    // ── CRUD ──────────────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        $data = [
            'origin'         => $this->origin,
            'destination'    => $this->destination,
            'distance_km'    => $this->distance_km ?: null,
            'base_fare'      => $this->base_fare,
            'duration_hours' => $this->duration_hours ?: null,
        ];

        if ($this->editingId) {
            Route::findOrFail($this->editingId)->update($data);
            $this->flashMessage = 'Route updated successfully.';
        } else {
            Route::create($data);
            $this->flashMessage = 'Route created successfully.';
        }

        $this->flashType = 'success';
        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $route = Route::withCount('schedules')->findOrFail($id);

        if ($route->schedules_count > 0) {
            $this->flashMessage = 'Cannot delete a route that has schedules assigned to it.';
            $this->flashType    = 'error';
            return;
        }

        $route->delete();
        $this->flashMessage = 'Route deleted.';
        $this->flashType    = 'success';
    }

    // ── Render ────────────────────────────────────────────────
    public function render()
    {
        $routes = Route::withCount('schedules')
            ->when($this->search, fn($q) => $q
                ->where('origin', 'like', "%{$this->search}%")
                ->orWhere('destination', 'like', "%{$this->search}%")
            )
            ->orderBy('origin')
            ->paginate(15);

        return view('livewire.routes.route-list', compact('routes'));
    }
}