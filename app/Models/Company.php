<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'logo',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }
    public function items()
    {
        return $this->hasMany(Item::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Hitung total saldo deposit yang disetujui
     */
    public function getTotalDepositBalance()
    {
        return $this->deposits()
            ->where('status', 'approve')
            ->sum('nominal');
    }

    /**
     * Hitung total pembayaran yang sudah dilakukan
     */
    public function getTotalPayments()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Hitung sisa saldo (deposit - pembayaran)
     */
    public function getRemainingBalance()
    {
        return $this->getTotalDepositBalance() - $this->getTotalPayments();
    }

    /**
     * Cek apakah saldo mencukupi untuk pembayaran
     */
    public function hasSufficientBalance($amount)
    {
        return $this->getRemainingBalance() >= $amount;
    }
}
