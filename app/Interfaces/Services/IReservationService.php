<?php

namespace App\Interfaces\Services;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;

interface IReservationService
{
    public function getReservation(int $reservationId, string $userUuid): ?Reservation;
    public function getExpiredReservations(): Collection;
    public function reserve(string $userUuid, int $eventId, int $amount): Reservation;
    public function purchase(string $userUuid, int $reservationId): ?Reservation;
}