<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'company_id',
        'created_by_user_id',
        'total_chargeable_weight',
        'cargo_handling_fee',
        'air_handling_fee',
        'inspection_fee',
        'admin_fee',
        'subtotal',
        'tax_amount',
        'pnbp_amount',
        'total_amount',
        'status',
        'approval_status',
        'approved_by_user_id',
        'approved_at',
        'rejected_by_user_id',
        'rejected_at',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function items() {
        return $this->belongsToMany(Item::class, 'invoice_items');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function remarks()
    {
        return $this->hasMany(Remark::class, 'model_id')->where('model', 'App\Models\Invoice');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Helper methods untuk status
    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    public function canBeApproved()
    {
        return $this->isPending();
    }

    public function canBeRejected()
    {
        return $this->isPending();
    }

    public function canBeEdited()
    {
        return $this->isRejected() || $this->isPending();
    }

    /**
     * Cek apakah invoice sudah dibayar
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Hitung total pembayaran yang sudah dilakukan
     */
    public function getTotalPaidAmount()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Hitung sisa yang belum dibayar
     */
    public function getRemainingAmount()
    {
        return $this->total_amount - $this->getTotalPaidAmount();
    }

    /**
     * Cek apakah invoice sudah lunas
     */
    public function isFullyPaid()
    {
        return $this->getRemainingAmount() <= 0;
    }
}
