<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    protected $fillable = [
        'name',
        'code',
        'cargo_handling_incoming_price',
        'cargo_handling_outgoing_price',
        'handling_airplane_outgoing_price',
        'handling_airplane_incoming_price',
        'jppgc_incoming_price',
        'jppgc_outgoing_price',
    ];
}
