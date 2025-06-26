<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    public function origin() {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }
    public function destination() {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }
    public function airline() {
        return $this->belongsTo(Airline::class);
    }
    public function items() {
        return $this->hasMany(Item::class);
    }
}
