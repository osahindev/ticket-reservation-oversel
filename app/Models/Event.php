<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = ['name', 'ticket_quantity'];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
