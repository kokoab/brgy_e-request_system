<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'description',
        'price',
        'capacity',
        'is_available',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_available;
    }

    /**
     * Get current occupancy count (approved bookings)
     */
    public function getOccupancyCount(): int
    {
        return $this->bookings()
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Get paid bookings count
     */
    public function getPaidBookingsCount(): int
    {
        return $this->bookings()
            ->where('status', 'approved')
            ->where('is_paid', true)
            ->count();
    }

    /**
     * Get remaining capacity
     */
    public function getRemainingCapacity(): int
    {
        $occupied = $this->getOccupancyCount();
        return max(0, $this->capacity - $occupied);
    }

    /**
     * Check if room is fully occupied
     */
    public function isFullyOccupied(): bool
    {
        return $this->getRemainingCapacity() === 0;
    }

    /**
     * Check if room has available spots
     */
    public function hasAvailableSpots(): bool
    {
        return $this->is_available && $this->getRemainingCapacity() > 0;
    }

    /**
     * Get availability status
     */
    public function getAvailabilityStatus(): string
    {
        if (!$this->is_available) {
            return 'unavailable';
        }
        
        $remaining = $this->getRemainingCapacity();
        if ($remaining === 0) {
            return 'occupied';
        } elseif ($remaining < $this->capacity) {
            return 'partially_occupied';
        }
        
        return 'available';
    }
}

