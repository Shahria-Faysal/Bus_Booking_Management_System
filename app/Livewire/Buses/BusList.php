<?php

namespace App\Livewire\Buses;

use App\Models\Bus;
use Livewire\Component;
use Livewire\WithPagination;

class BusList extends Component
{
    use WithPagination;

    // ── Filters ───────────────────────────────────────────────
    public string $search   = '';
    public string $type     = '';
    public string $status   = '';

    // ── Flash ─────────────────────────────────────────────────
    public ?string $flashMessage = null;
    public ?string $flashType    = null;

    // ── Modal ─────────────────────────────────────────────────
    public bool $showModal = false;
    public ?int $editingId = null;

    // ── Form fields ───────────────────────────────────────────
    public string $registration_no = '';
    public string $bus_name        = '';
    public string $bus_type        = 'Non-AC';
    public int    $total_seats     = 40;
    public string $operator_name   = '';
    public string $bus_status      = 'Active';

    protected function rules(): array
    {
        $uniqueRule = $this->editingId
            ? "unique:buses,registration_no,{$this->editingId},bus_id"
            : 'unique:buses,registration_no';

        return [
            'registration_no' => "required|string|max:30|{$uniqueRule}",
            'bus_name'        => 'required|string|max:100',
            'bus_type'        => 'required|in:AC,Non-AC,Sleeper,Mini',
            'total_seats'     => 'required|integer|min:1|max:100',
            'operator_name'   => 'nullable|string|max:150',
            'bus_status'      => 'required|in:Active,Maintenance,Retired',
        ];
    }

    protected $validationAttributes = [
        'registration_no' => 'registration number',
        'bus_name'        => 'bus name',
        'bus_type'        => 'bus type',
        'total_seats'     => 'total seats',
        'operator_name'   => 'operator name',
        'bus_status'      => 'status',
    ];

    // ── Filter watchers ───────────────────────────────────────
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedType(): void   { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }

    // ── Modal helpers ─────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $bus = Bus::findOrFail($id);

        $this->editingId       = $id;
        $this->registration_no = $bus->registration_no;
        $this->bus_name        = $bus->bus_name;
        $this->bus_type        = $bus->bus_type;
        $this->total_seats     = $bus->total_seats;
        $this->operator_name   = $bus->operator_name ?? '';
        $this->bus_status      = $bus->status;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['registration_no', 'bus_name', 'operator_name', 'editingId']);
        $this->bus_type    = 'Non-AC';
        $this->total_seats = 40;
        $this->bus_status  = 'Active';
        $this->resetValidation();
    }

    // ── CRUD ──────────────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        $data = [
            'registration_no' => $this->registration_no,
            'bus_name'        => $this->bus_name,
            'bus_type'        => $this->bus_type,
            'total_seats'     => $this->total_seats,
            'operator_name'   => $this->operator_name ?: null,
            'status'          => $this->bus_status,
        ];

        if ($this->editingId) {
            Bus::findOrFail($this->editingId)->update($data);
            $this->flashMessage = 'Bus updated successfully.';
        } else {
            Bus::create($data);
            $this->flashMessage = 'Bus added successfully.';
        }

        $this->flashType = 'success';
        $this->closeModal();
    }

    public function retire(int $id): void
    {
        Bus::findOrFail($id)->update(['status' => 'Retired']);
        $this->flashMessage = 'Bus marked as retired.';
        $this->flashType    = 'success';
    }

    public function delete(int $id): void
    {
        $bus = Bus::withCount([
            'schedules' => fn($q) => $q->whereIn('schedule_status', ['Scheduled', 'Departed'])
        ])->findOrFail($id);

        if ($bus->schedules_count > 0) {
            $this->flashMessage = 'Cannot delete a bus with active or upcoming schedules.';
            $this->flashType    = 'error';
            return;
        }

        $bus->delete();
        $this->flashMessage = 'Bus deleted.';
        $this->flashType    = 'success';
    }

    // ── Render ────────────────────────────────────────────────
    public function render()
    {
        $buses = Bus::withCount('schedules')
            ->when($this->search, fn($q) => $q
                ->where('bus_name', 'like', "%{$this->search}%")
                ->orWhere('registration_no', 'like', "%{$this->search}%")
                ->orWhere('operator_name', 'like', "%{$this->search}%")
            )
            ->when($this->type,   fn($q) => $q->where('bus_type', $this->type))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy('bus_name')
            ->paginate(15);

        return view('livewire.buses.bus-list', compact('buses'));
    }
}