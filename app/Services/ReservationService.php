<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Interfaces\Services\IEventService;
use App\Interfaces\Services\IReservationService;
use App\Models\Reservation;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReservationService implements IReservationService
{
    private const RESERVATION_TIMEOUT = 300;

    public function __construct(
        private readonly IEventService $eventService
    ) {
    }

    public function getReservation(int $reservationId, string $userUuid): ?Reservation
    {
        return Reservation::where("id", $reservationId)
            ->where("user_uid", $userUuid)
            ->first();
    }

    public function getExpiredReservations(): Collection
    {
        return Reservation::where("expires_at", "<=", now())
            ->where("status", ReservationStatus::RESERVED)
            ->get();
    }

    public function reserve(string $userUuid, int $eventId, int $amount): Reservation
    {
        return DB::transaction(function () use ($userUuid, $eventId, $amount) {
            $event = $this->eventService->getEventForUpdate($eventId);

            throw_if($event === null, ModelNotFoundException::class, "Event is not found.");
            throw_if($event->ticket_quantity < $amount, \Exception::class, "Not enough tickets available.");

            $this->eventService->decreaseTicketQuantity($event, $amount);

            return Reservation::create([
                "user_uid" => $userUuid,
                "event_id" => $eventId,
                "amount" => $amount,
                "status" => ReservationStatus::RESERVED,
                "expires_at" => now()->addSeconds(self::RESERVATION_TIMEOUT),
            ]);
        });
    }

    public function purchase(string $userUuid, int $reservationId): ?Reservation
    {
        $reservation = $this->getReservation($reservationId, $userUuid);

        throw_if($reservation === null, ModelNotFoundException::class, "Reservation is not found.");
        throw_if($reservation->expires_at->isPast(), \Exception::class, "Reservation has expired.");
        throw_if($reservation->status !== ReservationStatus::RESERVED, \Exception::class, "This reservation already purchased.");

        $reservation->status = ReservationStatus::PURCHASED;
        $reservation->save();

        return $reservation;
    }
}