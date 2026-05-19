<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $primaryKey = 'booking_id';

    // booking_date acts as created_at; we manage it manually
    const CREATED_AT = 'booking_date';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'schedule_id',
        'passenger_id',
        'seat_number',
        'journey_date',
        'fare_paid',
        'booking_status',
        'discount_pct',
        'notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'journey_date' => 'date',
        'fare_paid'    => 'decimal:2',
        'discount_pct' => 'decimal:2',
    ];

    const STATUSES = ['Confirmed', 'Cancelled', 'Completed', 'No-Show'];

    // ── Relationships ──────────────────────────────────────────

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'schedule_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id', 'id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'booking_id', 'booking_id');
    }

    // ── Accessors ─────────────────────────────────────────────

    /**
     * Discount level label matching QUERY 35.
     */
    // public function getDiscountLevelAttribute(): string
    // {
    //     return match (true) {
    //         $this->discount_pct == 0   => 'No Discount',
    //         $this->discount_pct <= 5   => 'Small Discount',
    //         $this->discount_pct <= 15  => 'Standard Discount',
    //         default                    => 'Premium Discount',
    //     };
    // }

    // /**
    //  * Whether a refund is eligible matching QUERY 34 logic.
    //  */
    // public function getRefundEligibleAttribute(): bool
    // {
    //     return $this->booking_status === 'Cancelled'
    //         && $this->journey_date->isFuture();
    // }

    // // ── Scopes ────────────────────────────────────────────────

    // public function scopeConfirmed($query)
    // {
    //     return $query->where('booking_status', 'Confirmed');
    // }

    // public function scopeCancelled($query)
    // {
    //     return $query->where('booking_status', 'Cancelled');
    // }

    // public function scopeCompleted($query)
    // {
    //     return $query->where('booking_status', 'Completed');
    // }

    // public function scopeForPassenger($query, int $passengerId)
    // {
    //     return $query->where('passenger_id', $passengerId);
    // }

    // public function scopeForSchedule($query, int $scheduleId)
    // {
    //     return $query->where('schedule_id', $scheduleId);
    // }
}
