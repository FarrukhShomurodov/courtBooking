<?php

use App\Http\Controllers\Api\BotUserController;
use App\Http\Controllers\Api\SportTypeController;
use App\Http\Controllers\Api\StadiumController;
use Illuminate\Support\Facades\Route;


Route::put('bot-users/{botUser}/is-active', [BotUserController::class, 'isActive']);
Route::delete('/delete/sport_type_photos/{photoPath}/{sportType}', [SportTypeController::class, 'deletePhoto']);
Route::delete('/delete/stadium_photos/{photoPath}/{sportType}', [StadiumController::class, 'deletePhoto']);
