<?php

namespace App\Livewire\Passengers;

use App\Services\PassengerService;
use Livewire\Component;
use Livewire\WithPagination;

class PassengerList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type   = '';
    public string $status = '';

    public ?string $flashMessage = null;
    public ?string $flashType    = null;

    // Modal state
    public bool  $showModal    = false;
    public array $form         = [
        'full_name'      => '',
        'email'          => '',
        'phone'          => '',
        'nid_number'     => '',
        'address'        => '',
        'passenger_type' => 'Regular',
        'account_status' => 'Active',
    ];
    public ?int $editingId = null;

    protected array $rules = [
        'form.full_name'      => 'required|string|max:150',
        'form.email'          => 'required|email|max:120',
        'form.phone'          => 'nullable|string|max:20',
        'form.nid_number'     => 'nullable|string|max:30',
        'form.address'        => 'nullable|string',
        'form.passenger_type' => 'required|in:Regular,Student,Senior,VIP',
        'form.account_status' => 'required|in:Active,Suspended,Blacklisted',
    ];

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedType(): void   { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset('form', 'editingId');
        $this->form = [
            'full_name'      => '',
            'email'          => '',
            'phone'          => '',
            'nid_number'     => '',
            'address'        => '',
            'passenger_type' => 'Regular',
            'account_status' => 'Active',
        ];
        $this->showModal = true;
    }

    public function openEdit(int $id, PassengerService $service): void
    {
        $passenger     = $service->find($id);
        $this->editingId = $id;
        $this->form = [
            'full_name'      => $passenger->full_name,
            'email'          => $passenger->email,
            'phone'          => $passenger->phone,
            'nid_number'     => $passenger->nid_number,
            'address'        => $passenger->address,
            'passenger_type' => $passenger->passenger_type,
            'account_status' => $passenger->account_status,
        ];
        $this->showModal = true;
    }

    public function save(PassengerService $service): void
    {
        $this->validate();

        try {
            if ($this->editingId) {
                $service->update($this->editingId, $this->form);
                $this->flashMessage = 'Passenger updated.';
            } else {
                $service->create($this->form);
                $this->flashMessage = 'Passenger created.';
            }
            $this->flashType  = 'success';
            $this->showModal  = false;
            $this->reset('form', 'editingId');
        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType    = 'error';
        }
    }

    public function reactivate(int $id, PassengerService $service): void
    {
        $service->reactivate($id);
        $this->flashMessage = 'Passenger reactivated.';
        $this->flashType    = 'success';
    }

    public function delete(int $id, PassengerService $service): void
    {
        $service->delete($id);
        $this->flashMessage = 'Passenger deleted.';
        $this->flashType    = 'success';
    }

    public function render(PassengerService $service)
    {
        $passengers = $service->paginate(
            search: $this->search,
            type:   $this->type,
            status: $this->status,
        );

        return view('livewire.passengers.passenger-list', compact('passengers'));
    }
}
