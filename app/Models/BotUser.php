<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'isactive'
    ];
}
