<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Interfaces\Services\IEventService;
use App\Interfaces\Services\IReservationService;
use App\Models\Reservation;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReservationService implements IReservationService
{
    private const RESERVATION_TIMEOUT = 300;

    public function __construct(
        private readonly IEventService $eventService
    ) {
    }

    public function reserve(string $userUuid, int $eventId, int $amount): Reservation
    {
        return DB::transaction(function () use ($userUuid, $eventId, $amount) {
            $event = $this->eventService->getEventForUpdate($eventId);

            throw_if($event === null, ModelNotFoundException::class, "Etkinlik bulunamadı.");
            throw_if($event->ticket_quantity < $amount, \Exception::class, "Yeterli miktarda bilet yok.");

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
}