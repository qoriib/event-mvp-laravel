<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['email', 'passwordHash', 'name', 'role'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['passwordHash', 'remember_token'];

    /**
     * Available user roles.
     */
    public const ROLE_ORGANIZER = 'ORGANIZER';
    public const ROLE_CUSTOMER = 'CUSTOMER';

    public function setPasswordHashAttribute(string $value): void
    {
        $this->attributes['passwordHash'] = Hash::make($value);
    }

    public function getAuthPassword(): string
    {
        return $this->passwordHash;
    }

    public function organizer()
    {
        return $this->hasOne(OrganizerProfile::class, 'userId');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'userId');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'userId');
    }

    public function ownedTickets()
    {
        return $this->hasMany(Ticket::class, 'ownerUserId');
    }
}
