<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/events", \App\Http\Controllers\EventController::class);
Route::post("/reserve", [\App\Http\Controllers\ReservationController::class, 'reserve']);
Route::post("/purchase", [\App\Http\Controllers\ReservationController::class, 'purchase']);