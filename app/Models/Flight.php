<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{

    protected $fillable = [
        'origin_id',
        'destination_id',
        'airline_id',
        'status',
        'flight_date',
        'departure_at',
        'arrival_at',
    ];

    public function origin()
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }
    public function destination()
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
