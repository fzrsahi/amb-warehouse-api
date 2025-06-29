<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Company;
use App\Models\WarehouseSetting;
use App\Http\Requests\InvoiceIndexRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\VerifyInvoiceRequest;
use App\Traits\PaginationTrait;
use App\Traits\SearchFilterTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    public function index(InvoiceIndexRequest $request)
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

            // Get searchable fields and filters for Invoice
            $searchableFields = $this->getSearchableFields('Invoice');
            $defaultFilters = $this->getDefaultFilters('Invoice');

            // Custom filters for Invoice
            $customFilters = [
                'status' => ['type' => 'exact'],
                'company_id' => ['type' => 'exact'],
            ];

            // Merge default and custom filters
            $filters = array_merge($defaultFilters, $customFilters);

            // Apply search and filters
            $this->applySearch($query, $request, $searchableFields);
            $this->applyFilters($query, $request, $filters);

            // Apply custom date range filter for in_at
            $this->applyInAtDateRangeFilter($query, $request);

            // Apply sorting
            if ($request->sort_by) {
                $sortOrder = $request->sort_order ?? 'asc';
                $query->orderBy($request->sort_by, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
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

    /**
     * Apply date range filter for in_at field
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param InvoiceIndexRequest $request
     * @return void
     */
    private function applyInAtDateRangeFilter($query, $request)
    {
        if ($request->start_date) {
            $query->where('in_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('in_at', '<=', $request->end_date . ' 23:59:59');
        }
    }

    public function store(StoreInvoiceRequest $request)
    {
        try {
            $user = $request->user();
            $userRoleType = $user->roles->first()->type ?? null;

            if (!($user->hasRole('super-admin') || $userRoleType === 'warehouse')) {
                return $this->forbiddenResponse('Hanya petugas warehouse yang dapat membuat invoice.');
            }

            DB::beginTransaction();

            $data = $request->validated();

            // Validasi maksimal 5 item per invoice
            if (count($data['item_ids']) > 5) {
                return $this->errorResponse('Maksimal 5 item per invoice.', 422);
            }

            $items = Item::with(['flight.airline', 'flight.origin', 'flight.destination'])
                ->whereIn('id', $data['item_ids'])
                ->get();

            if ($items->count() !== count($data['item_ids'])) {
                return $this->errorResponse('Beberapa item tidak ditemukan.', 422);
            }

            // Validasi item tidak boleh sudah ada invoicenya
            $itemsWithInvoice = $items->filter(function ($item) {
                return $item->invoices()->exists();
            });

            if ($itemsWithInvoice->count() > 0) {
                $itemCodes = $itemsWithInvoice->pluck('code')->implode(', ');
                return $this->errorResponse("Item dengan kode {$itemCodes} sudah memiliki invoice.", 422);
            }

            // Validasi item sudah keluar
            $itemsAlreadyOut = $items->where('out_at', '!=', null);
            if ($itemsAlreadyOut->count() > 0) {
                $itemCodes = $itemsAlreadyOut->pluck('code')->implode(', ');
                return $this->errorResponse("Item dengan kode {$itemCodes} sudah keluar dan tidak dapat ditagih lagi.", 422);
            }

            // Validasi status flight harus sama (semua incoming atau semua outgoing)
            $flightStatuses = $items->pluck('flight.status')->unique();
            if ($flightStatuses->count() > 1) {
                return $this->errorResponse('Semua item harus memiliki status flight yang sama (incoming atau outgoing).', 422);
            }

            $flightStatus = $flightStatuses->first();

            // Ambil data first item untuk generate invoice number
            $firstItem = $items->first();
            $flight = $firstItem->flight;

            $data['created_by_user_id'] = $user->id;
            $data['issued_at'] = now();

            // Hitung total chargeable weight
            $totalChargeableWeight = $items->sum('chargeable_weight');
            $data['total_chargeable_weight'] = $totalChargeableWeight;

            // Ambil pricing dari airline berdasarkan flight status
            $airline = $flight->airline;

            // Cargo Handling Fee
            if ($flightStatus === 'incoming') {
                $cargoHandlingFeePerKg = $airline->cargo_handling_incoming_price;
                $airHandlingFeePerKg = $airline->handling_airplane_incoming_price;
                $jppgcFeePerKg = $airline->jppgc_incoming_price;
            } else {
                $cargoHandlingFeePerKg = $airline->cargo_handling_outgoing_price;
                $airHandlingFeePerKg = $airline->handling_airplane_outgoing_price;
                $jppgcFeePerKg = $airline->jppgc_outgoing_price;
            }

            $data['cargo_handling_fee'] = $totalChargeableWeight * $cargoHandlingFeePerKg;
            $data['air_handling_fee'] = $totalChargeableWeight * $airHandlingFeePerKg;
            $data['inspection_fee'] = $totalChargeableWeight * $jppgcFeePerKg;

            // Ambil setting dari WarehouseSetting
            $warehouseSetting = WarehouseSetting::first();
            $adminFee = $warehouseSetting ? $warehouseSetting->admin_fee : 5000;
            $taxRate = $warehouseSetting ? ($warehouseSetting->tax / 100) : 0.11;
            $pnbpRate = $warehouseSetting ? ($warehouseSetting->pnbp / 100) : 0.01;

            $data['admin_fee'] = $adminFee;

            $data['subtotal'] = $data['cargo_handling_fee'] + $data['air_handling_fee'] + $data['inspection_fee'] + $data['admin_fee'];

            $data['tax_amount'] = $data['subtotal'] * $taxRate;
            $data['pnbp_amount'] = $data['subtotal'] * $pnbpRate;

            $data['total_amount'] = $data['subtotal'] + $data['tax_amount'] + $data['pnbp_amount'];

            // Generate invoice number dengan format baru
            $data['invoice_number'] = $this->generateInvoiceNumber($flight, $flightStatus);

            $invoice = Invoice::create($data);

            $invoice->items()->attach($data['item_ids']);

            // Update items out_at dan out_by_user_id
            Item::whereIn('id', $data['item_ids'])->update([
                'out_at' => now(),
                'out_by_user_id' => $user->id
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
                // Hilangkan validasi saldo - saldo bisa minus

                // Update status invoice
                $invoice->update([
                    'approval_status' => 'approved',
                    'approved_by_user_id' => $user->id,
                    'approved_at' => now(),
                ]);

                // Buat payment record untuk pemotongan saldo otomatis
                $company = $user->company;
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

    private function generateInvoiceNumber($flight, $flightStatus)
    {
        // Format: INVOICE+ORIGIN + IN/OUT + DEST + datetime + string unique
        $prefix = 'INVOICE';

        // Get origin and destination codes
        $originCode = $flight->origin->code ?? 'XXX';
        $destinationCode = $flight->destination->code ?? 'XXX';

        // Convert flight status to IN/OUT
        $statusCode = ($flightStatus === 'incoming') ? 'IN' : 'OUT';

        // Format datetime as YYYYMMDD
        $datePart = date('Ymd');

        // Generate unique sequence
        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}{$originCode}{$statusCode}{$destinationCode}{$datePart}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastSequence = (int) substr($lastInvoice->invoice_number, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        $uniqueString = str_pad($newSequence, 4, '0', STR_PAD_LEFT);

        return strtoupper($prefix . $originCode . $statusCode . $destinationCode . $datePart . $uniqueString);
    }

    public function autoStore(StoreInvoiceRequest $request)
    {
        try {
            $user = $request->user();
            $userRoleType = $user->roles->first()->type ?? null;

            if (!($user->hasRole('super-admin') || $userRoleType === 'warehouse')) {
                return $this->forbiddenResponse('Hanya petugas warehouse yang dapat membuat invoice otomatis.');
            }

            DB::beginTransaction();

            $data = $request->validated();

            // Validasi maksimal 5 item per invoice
            if (count($data['item_ids']) > 5) {
                return $this->errorResponse('Maksimal 5 item per invoice.', 422);
            }

            $items = Item::with(['flight.airline', 'flight.origin', 'flight.destination'])
                ->whereIn('id', $data['item_ids'])
                ->get();

            if ($items->count() !== count($data['item_ids'])) {
                DB::rollBack();
                $foundItemIds = $items->pluck('id')->toArray();
                $missingItemIds = array_diff($data['item_ids'], $foundItemIds);
                return $this->errorResponse("Item dengan ID " . implode(', ', $missingItemIds) . " tidak ditemukan.", 422);
            }

            // Validasi item tidak boleh sudah ada invoicenya
            $itemsWithInvoice = $items->filter(function ($item) {
                return $item->invoices()->exists();
            });

            if ($itemsWithInvoice->count() > 0) {
                $itemCodes = $itemsWithInvoice->pluck('code')->implode(', ');
                return $this->errorResponse("Item dengan kode {$itemCodes} sudah memiliki invoice.", 422);
            }

            $itemsAlreadyOut = $items->whereNotNull('out_at');
            if ($itemsAlreadyOut->count() > 0) {
                DB::rollBack();
                $itemCodes = $itemsAlreadyOut->pluck('code')->implode(', ');
                return $this->errorResponse("Item dengan kode {$itemCodes} sudah keluar dan tidak dapat ditagih lagi.", 422);
            }

            // Validasi status flight harus sama (semua incoming atau semua outgoing)
            $flightStatuses = $items->pluck('flight.status')->unique();
            if ($flightStatuses->count() > 1) {
                return $this->errorResponse('Semua item harus memiliki status flight yang sama (incoming atau outgoing).', 422);
            }

            $flightStatus = $flightStatuses->first();

            // Ambil data first item untuk generate invoice number
            $firstItem = $items->first();
            $flight = $firstItem->flight;

            $data['created_by_user_id'] = $user->id;
            $data['issued_at'] = now();

            $totalChargeableWeight = $items->sum('chargeable_weight');
            $data['total_chargeable_weight'] = $totalChargeableWeight;

            // Ambil pricing dari airline berdasarkan flight status
            $airline = $flight->airline;

            // Cargo Handling Fee
            if ($flightStatus === 'incoming') {
                $cargoHandlingFeePerKg = $airline->cargo_handling_incoming_price;
                $airHandlingFeePerKg = $airline->handling_airplane_incoming_price;
                $jppgcFeePerKg = $airline->jppgc_incoming_price;
            } else {
                $cargoHandlingFeePerKg = $airline->cargo_handling_outgoing_price;
                $airHandlingFeePerKg = $airline->handling_airplane_outgoing_price;
                $jppgcFeePerKg = $airline->jppgc_outgoing_price;
            }

            $data['cargo_handling_fee'] = $totalChargeableWeight * $cargoHandlingFeePerKg;
            $data['air_handling_fee'] = $totalChargeableWeight * $airHandlingFeePerKg;
            $data['inspection_fee'] = $totalChargeableWeight * $jppgcFeePerKg;

            // Ambil setting dari WarehouseSetting
            $warehouseSetting = WarehouseSetting::first();
            $adminFee = $warehouseSetting ? $warehouseSetting->admin_fee : 5000;
            $taxRate = $warehouseSetting ? ($warehouseSetting->tax / 100) : 0.11;
            $pnbpRate = $warehouseSetting ? ($warehouseSetting->pnbp / 100) : 0.01;

            $data['admin_fee'] = $adminFee;

            $data['subtotal'] = $data['cargo_handling_fee'] + $data['air_handling_fee'] + $data['inspection_fee'] + $data['admin_fee'];

            $data['tax_amount'] = $data['subtotal'] * $taxRate;
            $data['pnbp_amount'] = $data['subtotal'] * $pnbpRate;

            $data['total_amount'] = $data['subtotal'] + $data['tax_amount'] + $data['pnbp_amount'];

            $data['invoice_number'] = $this->generateInvoiceNumber($flight, $flightStatus);

            $company = Company::find($data['company_id']);
            if (!$company) {
                DB::rollBack();
                return $this->errorResponse('Perusahaan tidak ditemukan.', 422);
            }

            // Hilangkan validasi saldo - saldo bisa minus
            $data['approval_status'] = 'approved';
            $data['approved_by_user_id'] = $user->id;
            $data['approved_at'] = now();
            $data['status'] = 'paid';
            $data['paid_at'] = now();

            $invoice = Invoice::create($data);

            $invoice->items()->attach($data['item_ids']);

            Item::whereIn('id', $data['item_ids'])->update([
                'out_at' => now(),
                'out_by_user_id' => $user->id
            ]);

            $invoice->payments()->create([
                'company_id' => $company->id,
                'invoice_id' => $invoice->id,
                'amount' => $data['total_amount'],
                'payment_method' => 'balance_deduction',
                'status' => 'completed',
                'description' => 'Pembayaran otomatis melalui pemotongan saldo',
                'created_by_user_id' => $user->id,
                'paid_at' => now(),
            ]);

            DB::commit();

            $invoice->load(['company:id,name', 'createdBy:id,name', 'items']);

            return $this->successResponse(null, 'Invoice berhasil dibuat dan dibayar otomatis', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat invoice otomatis: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat invoice otomatis');
        }
    }
}
