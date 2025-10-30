<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'eventId',
        'userId',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
