<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    protected $primaryKey = 'bus_id';

    // buses table uses 'added_at' instead of Laravel's default 'created_at'
    const CREATED_AT = 'added_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'registration_no',
        'bus_name',
        'bus_type',
        'total_seats',
        'operator_name',
        'status',
    ];

    protected $casts = [
        'total_seats' => 'integer',
        'added_at'    => 'datetime',
    ];

    // ── Constants ─────────────────────────────────────────────

    const BUS_TYPES = ['AC', 'Non-AC', 'Sleeper', 'Mini'];
    const STATUSES  = ['Active', 'Maintenance', 'Retired'];

    // ── Relationships ──────────────────────────────────────────

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'bus_id', 'bus_id');
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('bus_type', $type);
    }

    // ── Accessors ─────────────────────────────────────────────

    /**
     * Comfort rating matching QUERY 39
     */
    public function getComfortRatingAttribute(): string
    {
        return match ($this->bus_type) {
            'Sleeper' => 'Luxury',
            'AC'      => 'Comfortable',
            'Non-AC'  => 'Standard',
            'Mini'    => 'Basic',
            default   => 'Unknown',
        };
    }
}
