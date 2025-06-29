<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_fee',
        'tax',
        'pnbp',
    ];

    protected $casts = [
        'admin_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'pnbp' => 'decimal:2',
    ];
}
