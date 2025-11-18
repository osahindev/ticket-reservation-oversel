<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReserveRequest;
use App\Http\Resources\ReservationResource;
use App\Interfaces\Services\IReservationService;
use App\Interfaces\Services\IUserService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        private IUserService $userService,
        private IReservationService $reservationService
    ) {
    }

    public function reserve(ReserveRequest $request)
    {
        $visitorToken = $this->userService->getVisitorToken();

        if ($visitorToken === null) {
            $visitorToken = $this->userService->createVisitorToken();
        }

        $reservation = $this->reservationService->reserve(
            userUuid: $visitorToken,
            eventId: $request->input('event_id'),
            amount: $request->input('amount')
        );

        return new ReservationResource($reservation);
    }
}
