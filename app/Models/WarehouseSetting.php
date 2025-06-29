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
        'minimal_charge_weight',
        'max_negative_balance',
    ];

    /**
     * Get the max negative balance as string with minus sign
     */
    public function getMaxNegativeBalanceStringAttribute()
    {
        return '-' . number_format($this->max_negative_balance, 2, '.', '');
    }
}
