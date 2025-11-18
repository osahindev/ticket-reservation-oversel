<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = ['user_uid', 'event_id', 'status', 'amount', 'expire_at'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
