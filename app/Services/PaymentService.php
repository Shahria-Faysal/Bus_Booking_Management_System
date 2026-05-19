<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaymentService
{
    public function paginate(string $status = '', int $perPage = 15): LengthAwarePaginator
    {
        return Payment::with(['booking.schedule.route', 'passenger'])
            ->when($status, fn($q) => $q->where('payment_status', $status))
            ->latest('payment_id')
            ->paginate($perPage);
    }

    public function find(int $id): Payment
    {
        return Payment::with(['booking.schedule.route', 'passenger'])->findOrFail($id);
    }

    public function pay(int $id, string $method): Payment
    {
        $payment = Payment::findOrFail($id);
        $payment->update([
            'amount_paid'    => $payment->amount_due,
            'payment_status' => 'Paid',
            'payment_method' => $method,
            'payment_date'   => now(),
        ]);

        AuditLog::write('PAYMENT_RECEIVED', 'payments', $payment->payment_id,
            "Payment of ৳{$payment->amount_due} received via {$method}. Booking #{$payment->booking_id}");

        return $payment->fresh();
    }

    public function partial(int $id, float $amount, string $method): Payment
    {
        $payment = Payment::findOrFail($id);
        $newPaid = $payment->amount_paid + $amount;
        $isFullyPaid = $newPaid >= $payment->amount_due;

        $payment->update([
            'amount_paid'    => min($newPaid, $payment->amount_due),
            'payment_status' => $isFullyPaid ? 'Paid' : 'Partial',
            'payment_method' => $method,
            'payment_date'   => now(),
        ]);

        AuditLog::write('PAYMENT_PARTIAL', 'payments', $payment->payment_id,
            "Partial payment of ৳{$amount} received via {$method}. Booking #{$payment->booking_id}");

        return $payment->fresh();
    }

    public function refund(int $id): Payment
    {
        $payment = Payment::with('booking')->findOrFail($id);

        if ($payment->booking->booking_status !== 'Cancelled') {
            throw new \Exception('Refund can only be issued for cancelled bookings.');
        }

        $payment->update([
            'payment_status' => 'Refunded',
            'refund_amount'  => $payment->amount_paid,
            'payment_date'   => now(),
        ]);

        AuditLog::write('PAYMENT_REFUND', 'payments', $payment->payment_id,
            "Refund of ৳{$payment->amount_paid} issued for Booking #{$payment->booking_id}");

        return $payment->fresh();
    }

    public function pending(): Collection
    {
        return Payment::query()
            ->join('bookings',   'payments.booking_id',   '=', 'bookings.booking_id')
            ->join('passengers', 'payments.passenger_id', '=', 'passengers.passenger_id')
            ->join('schedules',  'bookings.schedule_id',  '=', 'schedules.schedule_id')
            ->join('routes',     'schedules.route_id',    '=', 'routes.route_id')
            ->whereIn('payments.payment_status', ['Pending', 'Partial'])
            ->selectRaw('
                payments.payment_id,
                passengers.full_name  AS passenger_name,
                routes.origin,
                routes.destination,
                bookings.journey_date,
                bookings.seat_number,
                payments.amount_due,
                payments.amount_paid,
                (payments.amount_due - payments.amount_paid) AS balance_due,
                payments.payment_status
            ')
            ->orderByDesc('balance_due')
            ->get();
    }

    public function byMethod(): Collection
    {
        return Payment::query()
            ->whereIn('payment_status', ['Paid', 'Partial'])
            ->selectRaw('
                payment_method,
                COUNT(*)                      AS total_transactions,
                ROUND(SUM(amount_paid), 2)    AS total_collected,
                ROUND(AVG(amount_paid), 2)    AS avg_transaction_value
            ')
            ->groupBy('payment_method')
            ->orderByDesc('total_collected')
            ->get();
    }
}
