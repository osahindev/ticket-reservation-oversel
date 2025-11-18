<?php

namespace App\Interfaces\Services;

use App\Models\Reservation;

interface IReservationService
{
    public function reserve(string $userUuid, int $eventId, int $amount): Reservation;
}