<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case RESERVED = 'reserved';
    case PURCHASED = 'purchased';
}