<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{

    protected $fillable = [
        'deposit_at',
        'created_by_user_id',
        'accepted_by_user_id',
        'nominal',
        'company_id',
        'status',
        'accepted_at',
    ];

    public function company() {
        return $this->belongsTo(Company::class);
    }
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    public function acceptedBy() {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }
    public function remarks() {
        return $this->morphMany(Remark::class, 'model');
    }
}
