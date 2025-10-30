<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'displayName',
        'bio',
        'ratingsAvg',
        'ratingsCount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organizerId');
    }
}
