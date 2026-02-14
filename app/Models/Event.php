<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'organizer_id',
        'title',
        'description',
        'location_type',
        'location',
        'event_date',
        'price',
        'total_seats',
        'remaining_seats',
        'status'
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isFull(): bool
    {
        return $this->remaining_seats <= 0;
    }

    public function isPast(): bool
    {
        return $this->event_date->isPast();
    }

    public function canBeBooked(): bool
    {
        return $this->isPublished() && !$this->isFull() && !$this->isPast();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now());
    }

    public function scopeAvailable($query)
    {
        return $query->published()
            ->upcoming()
            ->where('remaining_seats', '>', 0);
    }
}
