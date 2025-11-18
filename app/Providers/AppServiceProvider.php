<?php

namespace App\Providers;

use App\Interfaces\Services\IEventService;
use App\Interfaces\Services\IReservationService;
use App\Interfaces\Services\IUserService;
use App\Services\EventService;
use App\Services\ReservationService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IUserService::class, UserService::class);
        $this->app->bind(IReservationService::class, ReservationService::class);
        $this->app->bind(IEventService::class, EventService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
