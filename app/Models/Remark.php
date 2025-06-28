<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{
    protected $fillable = [
        'model',
        'model_id',
        'user_id',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'model_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'model_id');
    }

    // Polymorphic relationship
    public function remarkable()
    {
        return $this->morphTo('model', 'model', 'model_id');
    }
}
