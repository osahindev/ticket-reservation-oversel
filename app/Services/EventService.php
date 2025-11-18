<?php

namespace App\Services;

use App\Interfaces\Services\IEventService;

class EventService implements IEventService
{
    public function getEventForUpdate(int $eventId): ?\App\Models\Event
    {
        return \App\Models\Event::where("id", $eventId)->lockForUpdate()->first();
    }

    public function decreaseTicketQuantity(\App\Models\Event $event, int $amount): int
    {
        return $event->decrement("ticket_quantity", $amount);
    }
}