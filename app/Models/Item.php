<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'awb',
        'company_id',
        'flight_id',
        'commodity_type_id',
        'qty',
        'total_qty',
        'gross_weight',
        'chargeable_weight',
        'volume_weight',
        'length',
        'width',
        'height',
        'weight_calculation_method',
        'status',
        'created_by_user_id',
        'accepted_by_user_id',
        'out_by_user_id',
        'accepted_at',
        'in_at',
        'out_at',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }


    public function commodityType()
    {
        return $this->belongsTo(CommodityType::class);
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function outBy()
    {
        return $this->belongsTo(User::class, 'out_by_user_id');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_items');
    }
}
