<?php

namespace App\Livewire\Payments;

use App\Services\PaymentService;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentList extends Component
{
    use WithPagination;

    public string $status        = '';
    public ?string $flashMessage = null;
    public ?string $flashType    = null;

    // Pay modal
    public bool   $showPayModal  = false;
    public int    $payingId      = 0;
    public string $paymentMethod = 'Cash';
    public float  $partialAmount = 0;
    public bool   $isPartial     = false;

    public function updatedStatus(): void { $this->resetPage(); }

    public function openPay(int $id, bool $partial = false): void
    {
        $this->payingId      = $id;
        $this->isPartial     = $partial;
        $this->paymentMethod = 'Cash';
        $this->partialAmount = 0;
        $this->showPayModal  = true;
    }

    public function confirmPay(PaymentService $service): void
    {
        try {
            if ($this->isPartial) {
                $this->validate(['partialAmount' => 'required|numeric|min:0.01']);
                $service->partial($this->payingId, $this->partialAmount, $this->paymentMethod);
                $this->flashMessage = 'Partial payment recorded.';
            } else {
                $service->pay($this->payingId, $this->paymentMethod);
                $this->flashMessage = 'Payment marked as paid.';
            }
            $this->flashType   = 'success';
            $this->showPayModal = false;
        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType    = 'error';
        }
    }

    public function refund(int $id, PaymentService $service): void
    {
        try {
            $service->refund($id);
            $this->flashMessage = 'Refund issued.';
            $this->flashType    = 'success';
        } catch (\Exception $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType    = 'error';
        }
    }

    public function render(PaymentService $service)
    {
        $payments = $service->paginate(status: $this->status);

        return view('livewire.payments.payment-list', compact('payments'));
    }
}
