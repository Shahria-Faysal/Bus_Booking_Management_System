<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'booking_id',
        'passenger_id',
        'amount_due',
        'amount_paid',
        'payment_method',
        'payment_status',
        'payment_date',
        'refund_amount',
    ];

    protected $casts = [
        'amount_due'    => 'decimal:2',
        'amount_paid'   => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'payment_date'  => 'datetime',
    ];

    const METHODS  = ['Cash', 'Card', 'Mobile Banking', 'Online'];
    const STATUSES = ['Pending', 'Paid', 'Refunded', 'Partial'];

    // ── Relationships ──────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id', 'id');
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getBalanceDueAttribute(): float
    {
        return (float) ($this->amount_due - $this->amount_paid);
    }

    /**
     * Payment completion label matching QUERY 33.
     */
    public function getPaymentCompletionAttribute(): string
    {
        return $this->amount_paid >= $this->amount_due ? 'Fully Paid' : 'Incomplete';
    }

    /**
     * Collection priority label matching QUERY 40.
     */
    public function getCollectionPriorityAttribute(): string
    {
        return match ($this->payment_status) {
            'Paid'     => 'Settled',
            'Refunded' => 'Refunded',
            'Partial'  => 'Follow Up Needed',
            default    => 'Pending Collection',
        };
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->whereIn('payment_status', ['Pending', 'Partial']);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'Paid');
    }
}
