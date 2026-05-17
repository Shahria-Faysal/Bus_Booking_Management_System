<?php

namespace App\Livewire\Bookings;

use App\Services\BookingService;
use Livewire\Component;
use Livewire\WithPagination;

class BookingList extends Component
{
    use WithPagination;

    public string $status      = '';
    public string $search      = '';
    public ?string $flashMessage = null;
    public ?string $flashType    = null;

    // Reset pagination when filters change
    public function updatedStatus(): void  { $this->resetPage(); }
    public function updatedSearch(): void  { $this->resetPage(); }

    public function cancelBooking(int $id, BookingService $service): void
    {
        try {
            $service->cancel($id);
            $this->flashMessage = 'Booking cancelled successfully.';
            $this->flashType    = 'success';
        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType    = 'error';
        }
    }

    public function completeBooking(int $id, BookingService $service): void
    {
        try {
            $service->complete($id);
            $this->flashMessage = 'Booking marked as completed.';
            $this->flashType    = 'success';
        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType    = 'error';
        }
    }

    public function bulkNoShow(BookingService $service): void
    {
        $count = $service->bulkNoShow();
        $this->flashMessage = "{$count} bookings marked as No-Show.";
        $this->flashType    = 'success';
    }

    public function render(BookingService $service)
    {
        $bookings = $service->paginate(
            status: $this->status,
            perPage: 15
        );

        return view('livewire.bookings.booking-list', compact('bookings'));
    }
}
