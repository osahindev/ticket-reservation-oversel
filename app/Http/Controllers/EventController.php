<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Interfaces\Services\IEventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private IEventService $eventService
    ) {
    }
    public function __invoke()
    {
        return EventResource::collection($this->eventService->getAllEvents());
    }
}
