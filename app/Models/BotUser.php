<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'first_name',
        'second_name',
        'uname',
        'typed_name',
        'phone',
        'sms_code',
        'step',
        'lang',
        'isactive'
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
