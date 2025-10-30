<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizerId',
        'title',
        'description',
        'category',
        'location',
        'startAt',
        'endAt',
        'isPaid',
        'capacity',
        'seatsAvailable',
    ];

    protected $casts = [
        'isPaid' => 'boolean',
        'startAt' => 'datetime',
        'endAt' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizerId');
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class, 'eventId');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'eventId');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'eventId');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'eventId');
    }
}
