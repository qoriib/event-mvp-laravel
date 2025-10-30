<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'eventId',
        'name',
        'priceIDR',
        'quota',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventId');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'ticketTypeId');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'ticketTypeId');
    }
}
