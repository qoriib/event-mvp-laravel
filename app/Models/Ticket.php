<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'eventId',
        'transactionId',
        'ticketTypeId',
        'ownerUserId',
        'checkedInAt',
    ];

    protected $casts = [
        'checkedInAt' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventId');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transactionId');
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class, 'ticketTypeId');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'ownerUserId');
    }
}
