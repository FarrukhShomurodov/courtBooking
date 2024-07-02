<?php

use App\Http\Controllers\Api\BotUserController;
use App\Http\Controllers\Api\SportTypeController;
use Illuminate\Support\Facades\Route;


Route::put('bot-users/{botUser}/is-active', [BotUserController::class, 'isActive']);
Route::delete('/delete/sport_type_photos/{photoPath}/{sportType}', [SportTypeController::class, 'deletePhoto']);
