<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transactionId',
        'ticketTypeId',
        'qty',
        'unitPriceIDR',
        'lineTotalIDR',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transactionId');
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class, 'ticketTypeId');
    }
}
