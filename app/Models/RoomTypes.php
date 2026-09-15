<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomTypes extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'type',
        'price_per_night',
        'max_occupancy',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
