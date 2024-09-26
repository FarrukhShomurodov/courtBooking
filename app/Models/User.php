<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'second_name',
        'login',
        'avatar',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function stadiumOwner(): HasOne
    {
        return $this->hasOne(Stadium::class, 'owner_id');
    }

    public function stadiumManager(): HasOne
    {
        return $this->hasOne(Stadium::class, 'manager_id');
    }

    public function stadiumTrainer(): HasOne
    {
        return $this->hasOne(Stadium::class, 'coach_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function coach(): HasOne
    {
        return $this->hasOne(Coach::class);
    }
}
