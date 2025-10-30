<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'eventId',
        'status',
        'totalBeforeIDR',
        'pointsUsedIDR',
        'promoCode',
        'promoDiscountIDR',
        'totalPayableIDR',
        'paymentProofUrl',
        'paymentProofAt',
        'expiresAt',
        'decisionDueAt',
    ];

    protected $casts = [
        'paymentProofAt' => 'datetime',
        'expiresAt' => 'datetime',
        'decisionDueAt' => 'datetime',
    ];

    public const STATUS_WAITING_PAYMENT = 'WAITING_PAYMENT';
    public const STATUS_WAITING_CONFIRMATION = 'WAITING_CONFIRMATION';
    public const STATUS_DONE = 'DONE';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_CANCELED = 'CANCELED';

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventId');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transactionId');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'transactionId');
    }
}
