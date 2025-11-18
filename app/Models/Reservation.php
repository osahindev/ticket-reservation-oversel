<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = ['user_uid', 'event_id', 'status', 'amount', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'status' => ReservationStatus::class,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
