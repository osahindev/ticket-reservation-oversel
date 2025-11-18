<?php

namespace App\Console\Commands;

use App\Interfaces\Services\IEventService;
use App\Interfaces\Services\IReservationService;
use DB;
use Illuminate\Console\Command;
use Log;

class ReleaseExpiresReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-expires-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired reservations and return tickets to event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info(now() . " - Releasing expired reservations");
        $reservationService = app(IReservationService::class);
        $eventService = app(IEventService::class);
        $expiresReservations = $reservationService->getExpiredReservations();

        foreach ($expiresReservations as $reservation) {
            DB::transaction(function () use ($reservation, $eventService) {
                $event = $eventService->getEventForUpdate($reservation->event_id);

                if ($event !== null) {
                    $eventService->incrementTicketQuantity($event, $reservation->amount);
                }

                $reservation->delete();
            });
        }
        Log::info(now() . " - Finished releasing expired reservations");
    }
}
