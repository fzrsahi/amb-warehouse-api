<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    public function users() {
        return $this->hasMany(User::class);
    }
    public function deposits() {
        return $this->hasMany(Deposit::class);
    }
    public function items() {
        return $this->hasMany(Item::class);
    }
    public function invoices() {
        return $this->hasMany(Invoice::class);
    }
}
