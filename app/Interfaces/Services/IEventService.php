<?php

namespace App\Interfaces\Services;

interface IEventService
{
    public function getEventForUpdate(int $eventId): ?\App\Models\Event;
    public function incrementTicketQuantity(\App\Models\Event $event, int $amount): int;
    public function decreaseTicketQuantity(\App\Models\Event $event, int $amount): int;
}