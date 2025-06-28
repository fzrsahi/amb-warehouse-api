<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Flight;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    use ApiResponse, PaginationTrait;

    /**
     * Generate unique item code (BTB/TTB).
     * Format: [OriginCode][Type][YYMM][Sequence] e.g., GTOCOD25060000156
     */
    private function generateItemCode(Item $item): string
    {
        $flight = Flight::with('origin')->find($item->flight_id);
        $originCode = $flight->origin->code ?? 'XXX'; // Fallback code
        $type = 'TTB'; // Tanda Terima Barang
        $datePart = date('ym');
        $sequence = str_pad($item->id, 7, '0', STR_PAD_LEFT);

        return strtoupper($originCode . $type . $datePart . $sequence);
    }

    public function index(PaginationRequest $request)
    {
        try {
            $user = $request->user();
            $query = Item::with(['company:id,name', 'flight.origin:id,code', 'flight.destination:id,code', 'createdBy:id,name', 'acceptedBy:id,name']);

            $userRoleType = $user->roles->first()->type ?? null;

            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
            } elseif ($userRoleType === 'company') {
                $query->where('company_id', $user->company_id);
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk melihat data barang.');
            }

            $result = $this->handlePaginationWithFormat($query, $request);

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Data barang berhasil diambil', 200, $pagination);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data barang');
        }
    }

    public function store(StoreItemRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            $userRoleType = $user->roles->first()->type ?? null;
            $data = $request->validated();

            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
            } elseif ($userRoleType === 'company') {
                $data['company_id'] = $user->company_id;
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk membuat item');
            }

            $data['created_by_user_id'] = $user->id;

            $gross_weight = (float) $data['gross_weight'];
            $volume_weight = null;
            $chargeable_weight = $gross_weight;


            if ($data['weight_calculation_method'] === 'volume') {
                $length = (float) $data['length'];
                $width = (float) $data['width'];
                $height = (float) $data['height'];

                $volume_weight = ($length * $width * $height) / 5000;

                $chargeable_weight = max($gross_weight, $volume_weight);
            }

            $data['volume_weight'] = $volume_weight;
            $data['chargeable_weight'] = $chargeable_weight;


            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
                $data['accepted_by_user_id'] = $user->id;
                $data['accepted_at'] = now();
                $data['in_at'] = now();
                $data['status'] = 'at_origin_warehouse';
            } elseif ($userRoleType === 'company') {
                $data['status'] = 'pending_submission';
                $data['accepted_by_user_id'] = null;
                $data['accepted_at'] = null;
                $data['in_at'] = null;
            }

            $data['code'] = 'TEMP-' . uniqid();
            $item = Item::create($data);

            $item->code = $this->generateItemCode($item);
            $item->save();

            DB::commit();
            return $this->successResponse(null, 'Barang berhasil dibuat', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat barang');
        }
    }

    public function show(Item $item)
    {
        try {
            $user = request()->user();
            $userRoleType = $user->roles->first()->type ?? null;

            if (!($user->hasRole('super-admin') || $userRoleType === 'warehouse' || ($userRoleType === 'company' && $user->company_id === $item->company_id))) {
                return $this->forbiddenResponse();
            }

            $item->load(['company:id,name', 'flight.origin:id,code', 'flight.destination:id,code', 'createdBy:id,name', 'acceptedBy:id,name']);
            return $this->successResponse($item, 'Detail barang berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil detail barang');
        }
    }

    public function verify(Request $request, Item $item)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();

            if ($item->status !== 'pending_submission') {
                return $this->errorResponse('Barang ini tidak dalam status menunggu verifikasi.', 422);
            }

            $item->update([
                'status' => 'at_origin_warehouse',
                'accepted_by_user_id' => $user->id,
                'accepted_at' => now(),
                'in_at' => now(),
            ]);

            DB::commit();
            return $this->successResponse($item, 'Barang berhasil diterima dan diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal verifikasi barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat verifikasi barang.');
        }
    }


    public function update(UpdateItemRequest $request, Item $item)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            $userRoleType = $user->roles->first()->type ?? null;
            $data = $request->validated();

            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
            } elseif ($userRoleType === 'company' && $user->company_id === $item->company_id) {
                $data['company_id'] = $user->company_id;
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk mengedit item ini');
            }

            $gross_weight = (float) $data['gross_weight'];
            $volume_weight = null;
            $chargeable_weight = $gross_weight;

            if ($data['weight_calculation_method'] === 'volume') {
                $length = (float) $data['length'];
                $width = (float) $data['width'];
                $height = (float) $data['height'];

                $volume_weight = ($length * $width * $height) / 5000;
                $chargeable_weight = max($gross_weight, $volume_weight);
            }

            $data['volume_weight'] = $volume_weight;
            $data['chargeable_weight'] = $chargeable_weight;

            $item->update($data);

            DB::commit();
            return $this->successResponse(null, 'Barang berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui barang');
        }
    }


    public function destroy(Item $item)
    {
        DB::beginTransaction();
        try {
            $user = request()->user();
            $userRoleType = $user->roles->first()->type ?? null;

            if (!($user->hasRole('super-admin') || $userRoleType === 'warehouse')) {
                return $this->forbiddenResponse();
            }

            if ($item->invoices()->exists()) {
                return $this->errorResponse('Barang tidak dapat dihapus karena sudah masuk ke dalam invoice.', 422);
            }

            $item->delete();

            DB::commit();
            return $this->successResponse(null, 'Barang berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus barang');
        }
    }
}
