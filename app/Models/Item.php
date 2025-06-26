<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public function company() {
        return $this->belongsTo(Company::class);
    }
    public function flight() {
        return $this->belongsTo(Flight::class);
    }
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    public function invoices() {
        return $this->belongsToMany(Invoice::class, 'invoice_items');
    }
}
