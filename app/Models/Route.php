<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    protected $primaryKey = 'route_id';

    protected $fillable = [
        'origin',
        'destination',
        'distance_km',
        'base_fare',
        'duration_hours',
    ];

    protected $casts = [
        'distance_km'    => 'decimal:2',
        'base_fare'      => 'decimal:2',
        'duration_hours' => 'decimal:2',
        'created_at'     => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'route_id', 'route_id');
    }

    // ── Accessors ──────────────────────────────────────────────

    /**
     * Human-readable label: "Dhaka → Chittagong"
     */
    public function getLabelAttribute(): string
    {
        return "{$this->origin} → {$this->destination}";
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeFromOrigin($query, string $origin)
    {
        return $query->where('origin', $origin);
    }

    public function scopeToDestination($query, string $destination)
    {
        return $query->where('destination', $destination);
    }
}
