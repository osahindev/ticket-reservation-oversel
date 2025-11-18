<?php

namespace App\Interfaces\Services;

use Illuminate\Database\Eloquent\Collection;

interface IEventService
{
    public function getAllEvents(): Collection;
    public function getEventForUpdate(int $eventId): ?\App\Models\Event;
    public function incrementTicketQuantity(\App\Models\Event $event, int $amount): int;
    public function decreaseTicketQuantity(\App\Models\Event $event, int $amount): int;
}