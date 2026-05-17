<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passenger extends Model
{
    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'nid_number',
        'address',
        'passenger_type',
        'account_status',
        'join_date',
        'total_trips',
    ];

    protected $casts = [
        'join_date'   => 'date',
        'total_trips' => 'integer',
    ];

    protected $hidden = ['nid_number'];

    // ── Name Accessors / Mutators ────────────────────────────

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function setFullNameAttribute(string $value): void
    {
        $this->attributes['name'] = $value;
    }

    const PASSENGER_TYPES  = ['Regular', 'Student', 'Senior', 'VIP'];
    const ACCOUNT_STATUSES = ['Active', 'Suspended', 'Blacklisted'];

    // ── Relationships ──────────────────────────────────────────

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'passenger_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'passenger_id', 'id');
    }

    // ── Accessors ─────────────────────────────────────────────

    /**
     * Discount entitlement matching QUERY 37.
     */
    public function getDiscountEntitlementAttribute(): string
    {
        return match ($this->passenger_type) {
            'Student' => '10% Student Discount',
            'Senior'  => '15% Senior Discount',
            'VIP'     => '20% VIP Discount',
            default   => 'No Discount',
        };
    }

    /**
     * Loyalty tier based on booking count — matching QUERY 60.
     */
    public function getLoyaltyTierAttribute(): string
    {
        $count = $this->bookings_count ?? $this->bookings()->count();

        return match (true) {
            $count >= 10 => 'Platinum Traveler',
            $count >= 5  => 'Gold Traveler',
            $count >= 2  => 'Silver Traveler',
            $count >= 1  => 'New Traveler',
            default      => 'Inactive',
        };
    }

    /**
     * Whether this passenger is allowed to make new bookings.
     */
    public function getIsBookingEligibleAttribute(): bool
    {
        return $this->account_status === 'Active';
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('account_status', 'Active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('passenger_type', $type);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('full_name', 'LIKE', "%{$term}%")
              ->orWhere('email', 'LIKE', "%{$term}%")
              ->orWhere('phone', 'LIKE', "%{$term}%");
        });
    }
}
