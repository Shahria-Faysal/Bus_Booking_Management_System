<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'bus_id',
        'route_id',
        'departure_time',
        'arrival_time',
        'available_seats',
        'fare_override',
        'schedule_status',
    ];

    protected $casts = [
        'departure_time'  => 'datetime',
        'arrival_time'    => 'datetime',
        'available_seats' => 'integer',
        'fare_override'   => 'decimal:2',
    ];

    // const STATUSES = ['Scheduled', 'Departed', 'Arrived', 'Cancelled'];

    // ── Relationships ──────────────────────────────────────────

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id', 'bus_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'route_id', 'route_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'schedule_id', 'schedule_id');
    }

    // ── Accessors ─────────────────────────────────────────────

    /**
     * Effective fare: override if set, else route base fare.
     */
    public function getEffectiveFareAttribute(): float
    {
        return (float) ($this->fare_override ?? $this->route?->base_fare ?? 0);
    }

    /**
     * Seat availability label matching QUERY 32 logic.
     */
    public function getSeatStatusAttribute(): string
    {
        if ($this->available_seats === 0) return 'Full';
        if ($this->available_seats <= 5)  return 'Almost Full';
        return 'Available';
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('available_seats', '>', 0)
                     ->where('schedule_status', 'Scheduled');
    }

    public function scopeBetweenDates($query, string $from, string $to)
    {
        return $query->whereBetween(\DB::raw('DATE(departure_time)'), [$from, $to]);
    }
}
