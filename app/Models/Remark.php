<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{
    public function model() {
        return $this->morphTo();
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
