<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Invoice;
use App\Models\Item;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\VerifyInvoiceRequest;
use App\Traits\PaginationTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $user = $request->user();
            $query = Invoice::with(['company:id,name', 'createdBy:id,name']);

            $userRoleType = $user->roles->first()->type ?? null;

            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
            } elseif ($userRoleType === 'company') {
                $query->where('company_id', $user->company_id);
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk melihat invoice.');
            }

            $result = $this->handlePaginationWithFormat($query, $request);
            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Data invoice berhasil diambil', 200, $pagination);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data invoice: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data invoice');
        }
    }

    public function store(StoreInvoiceRequest $request)
    {
        try {
            $user = $request->user();
            $userRoleType = $user->roles->first()->type ?? null;

            // Hanya warehouse/admin yang bisa membuat invoice
            if (!($user->hasRole('super-admin') || $userRoleType === 'warehouse')) {
                return $this->forbiddenResponse('Hanya petugas warehouse yang dapat membuat invoice.');
            }

            DB::beginTransaction();

            $data = $request->validated();
            $data['created_by_user_id'] = $user->id;
            $data['issued_at'] = now();

            // Ambil items untuk kalkulasi
            $items = Item::whereIn('id', $data['item_ids'])->get();

            // Hitung total chargeable weight
            $totalChargeableWeight = $items->sum('chargeable_weight');
            $data['total_chargeable_weight'] = $totalChargeableWeight;

            // Kalkulasi biaya (contoh sederhana, bisa disesuaikan dengan business logic)
            $cargoHandlingFee = 5000; // per kg
            $airHandlingFee = 3000; // per kg
            $inspectionFee = 10000; // flat rate
            $adminFee = 5000; // flat rate

            $data['cargo_handling_fee'] = $totalChargeableWeight * $cargoHandlingFee;
            $data['air_handling_fee'] = $totalChargeableWeight * $airHandlingFee;
            $data['inspection_fee'] = $inspectionFee;
            $data['admin_fee'] = $adminFee;

            // Hitung subtotal
            $data['subtotal'] = $data['cargo_handling_fee'] + $data['air_handling_fee'] + $data['inspection_fee'] + $data['admin_fee'];

            // Hitung pajak (PPN 11%)
            $data['tax_amount'] = $data['subtotal'] * 0.11;

            // Hitung PNBP (contoh: 1% dari subtotal)
            $data['pnbp_amount'] = $data['subtotal'] * 0.01;

            // Hitung total
            $data['total_amount'] = $data['subtotal'] + $data['tax_amount'] + $data['pnbp_amount'];

            // Generate invoice number
            $data['invoice_number'] = $this->generateInvoiceNumber();

            // Buat invoice
            $invoice = Invoice::create($data);

            // Attach items
            $invoice->items()->attach($data['item_ids']);

            // Buat remark pertama
            $invoice->remarks()->create([
                'user_id' => $user->id,
                'model' => 'App\Models\Invoice',
                'model_id' => $invoice->id,
                'status' => 'submit',
                'description' => 'Invoice telah dibuat dan menunggu persetujuan.',
            ]);

            DB::commit();

            $invoice->load(['company:id,name', 'createdBy:id,name', 'items']);

            return $this->successResponse($invoice, 'Invoice berhasil dibuat', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat invoice: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat invoice');
        }
    }

    public function show(Invoice $invoice)
    {
        try {
            $user = request()->user();
            $userRoleType = $user->roles->first()->type ?? null;

            // Cek akses
            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
            } elseif ($userRoleType === 'company' && $user->company_id !== $invoice->company_id) {
                return $this->forbiddenResponse('Anda hanya dapat melihat invoice milik perusahaan Anda sendiri');
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk melihat invoice ini');
            }

            $invoice->load([
                'createdBy:id,name,email',
                'approvedBy:id,name,email',
                'rejectedBy:id,name,email',
                'company:id,name,email,phone,address',
                'items',
                'remarks' => function ($query) {
                    $query->with('user:id,name,email')
                        ->orderBy('created_at', 'desc');
                }
            ]);

            return $this->successResponse($invoice, 'Data invoice berhasil diambil', 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data invoice: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data invoice');
        }
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        try {
            $user = request()->user();
            $userRoleType = $user->roles->first()->type ?? null;

            // Cek akses - hanya warehouse/admin yang bisa edit
            if (!($user->hasRole('super-admin') || $userRoleType === 'warehouse')) {
                return $this->forbiddenResponse('Hanya petugas warehouse yang dapat mengubah invoice');
            }

            // Cek apakah invoice bisa diedit
            if (!$invoice->canBeEdited()) {
                return $this->errorResponse('Invoice tidak dapat diedit karena status approval tidak sesuai', 422);
            }

            DB::beginTransaction();

            $data = $request->validated();
            $updateData = [];

            // Update biaya jika ada
            if (isset($data['cargo_handling_fee'])) {
                $updateData['cargo_handling_fee'] = $data['cargo_handling_fee'];
            }
            if (isset($data['air_handling_fee'])) {
                $updateData['air_handling_fee'] = $data['air_handling_fee'];
            }
            if (isset($data['inspection_fee'])) {
                $updateData['inspection_fee'] = $data['inspection_fee'];
            }
            if (isset($data['admin_fee'])) {
                $updateData['admin_fee'] = $data['admin_fee'];
            }

            // Recalculate totals
            if (!empty($updateData)) {
                $subtotal = ($updateData['cargo_handling_fee'] ?? $invoice->cargo_handling_fee) +
                    ($updateData['air_handling_fee'] ?? $invoice->air_handling_fee) +
                    ($updateData['inspection_fee'] ?? $invoice->inspection_fee) +
                    ($updateData['admin_fee'] ?? $invoice->admin_fee);

                $updateData['subtotal'] = $subtotal;
                $updateData['tax_amount'] = $subtotal * 0.11;
                $updateData['pnbp_amount'] = $subtotal * 0.01;
                $updateData['total_amount'] = $subtotal + $updateData['tax_amount'] + $updateData['pnbp_amount'];

                $invoice->update($updateData);
            }

            // Update items jika ada
            if (isset($data['item_ids'])) {
                $invoice->items()->sync($data['item_ids']);

                // Recalculate total chargeable weight
                $totalChargeableWeight = $invoice->items()->sum('chargeable_weight');
                $invoice->update(['total_chargeable_weight' => $totalChargeableWeight]);
            }

            // Reset approval status jika sebelumnya rejected
            if ($invoice->isRejected()) {
                $invoice->update([
                    'approval_status' => 'pending',
                    'rejected_by_user_id' => null,
                    'rejected_at' => null,
                ]);
            }

            // Buat remark untuk mencatat perubahan
            $remarkDescription = 'Invoice telah diperbarui';
            if ($invoice->isRejected()) {
                $remarkDescription = 'Invoice telah dikoreksi setelah penolakan';
            }

            $invoice->remarks()->create([
                'user_id' => $user->id,
                'model' => 'App\Models\Invoice',
                'model_id' => $invoice->id,
                'status' => 'submit',
                'description' => $remarkDescription,
            ]);

            DB::commit();

            return $this->successResponse(null, 'Invoice berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat memperbarui invoice: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui invoice');
        }
    }

    public function verify(VerifyInvoiceRequest $request, Invoice $invoice)
    {
        try {
            $user = request()->user();
            $userRoleType = $user->roles->first()->type ?? null;

            // Cek akses - hanya company yang bisa approve/reject
            if ($userRoleType !== 'company') {
                return $this->forbiddenResponse('Hanya perusahaan yang dapat menyetujui atau menolak invoice');
            }

            // Cek apakah invoice milik perusahaan user
            if ($user->company_id !== $invoice->company_id) {
                return $this->forbiddenResponse('Anda hanya dapat menyetujui atau menolak invoice milik perusahaan Anda sendiri');
            }

            // Cek apakah invoice bisa diapprove/reject
            if (!$invoice->canBeApproved() && !$invoice->canBeRejected()) {
                return $this->errorResponse('Invoice tidak dapat diverifikasi karena status tidak sesuai', 422);
            }

            DB::beginTransaction();

            $data = $request->validated();

            if ($data['status'] === 'approve') {
                // Cek saldo sebelum approve
                $company = $user->company;
                $remainingBalance = $company->getRemainingBalance();

                if (!$company->hasSufficientBalance($invoice->total_amount)) {
                    return $this->errorResponse(
                        "Saldo tidak mencukupi. Saldo tersedia: Rp " . number_format($remainingBalance, 0, ',', '.') .
                            ", Total invoice: Rp " . number_format($invoice->total_amount, 0, ',', '.'),
                        422
                    );
                }

                // Update status invoice
                $invoice->update([
                    'approval_status' => 'approved',
                    'approved_by_user_id' => $user->id,
                    'approved_at' => now(),
                ]);

                // Buat payment record untuk pemotongan saldo otomatis
                $payment = $invoice->payments()->create([
                    'company_id' => $company->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->total_amount,
                    'payment_method' => 'deposit',
                    'status' => 'completed',
                    'description' => 'Pembayaran otomatis dari saldo deposit',
                    'created_by_user_id' => $user->id,
                    'paid_at' => now(),
                ]);

                // Update status invoice menjadi paid
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                $remarkStatus = 'approve';
                $remarkDescription = 'Invoice telah disetujui dan dibayar otomatis dari saldo deposit';
            } else {
                $invoice->update([
                    'approval_status' => 'rejected',
                    'rejected_by_user_id' => $user->id,
                    'rejected_at' => now(),
                ]);

                $remarkStatus = 'reject';
                $remarkDescription = 'Invoice ditolak: ' . $data['description'];
            }

            // Buat remark
            $invoice->remarks()->create([
                'user_id' => $user->id,
                'model' => 'App\Models\Invoice',
                'model_id' => $invoice->id,
                'status' => $remarkStatus,
                'description' => $remarkDescription,
            ]);

            DB::commit();

            $statusMessage = $data['status'] === 'approve' ? 'disetujui dan dibayar' : 'ditolak';
            return $this->successResponse(null, "Invoice berhasil {$statusMessage}", 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat memverifikasi invoice: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memverifikasi invoice');
        }
    }

    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Ambil nomor terakhir untuk bulan ini
        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
