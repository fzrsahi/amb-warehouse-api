<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WarehouseSetting;
use App\Http\Requests\StoreWarehouseSettingRequest;
use App\Http\Requests\UpdateWarehouseSettingRequest;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;

class WarehouseSettingController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $warehouseSetting = WarehouseSetting::first();

            if (!$warehouseSetting) {
                $warehouseSetting = WarehouseSetting::create([
                    'admin_fee' => 0,
                    'tax' => 0,
                    'pnbp' => 0,
                    'minimal_charge_weight' => 0,
                ]);
            }

            return $this->successResponse($warehouseSetting, 'Data pengaturan gudang berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data pengaturan gudang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data pengaturan gudang');
        }
    }


    public function store(StoreWarehouseSettingRequest $request)
    {
        try {
            $existingSetting = WarehouseSetting::first();
            if ($existingSetting) {
                return $this->errorResponse('Pengaturan gudang sudah ada. Gunakan endpoint update untuk mengubah data.', 422);
            }

            $data = $request->validated();
            $warehouseSetting = WarehouseSetting::create($data);

            return $this->successResponse($warehouseSetting, 'Pengaturan gudang berhasil dibuat', 201);
        } catch (\Exception $e) {
            Log::error('Gagal membuat pengaturan gudang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat pengaturan gudang');
        }
    }


    public function update(UpdateWarehouseSettingRequest $request)
    {
        try {
            $data = $request->validated();

            $warehouseSetting = WarehouseSetting::first();

            if (!$warehouseSetting) {
                $warehouseSetting = WarehouseSetting::create($data);
                return $this->successResponse($warehouseSetting, 'Pengaturan gudang berhasil dibuat', 201);
            }

            $warehouseSetting->update($data);

            return $this->successResponse($warehouseSetting->fresh(), 'Pengaturan gudang berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui pengaturan gudang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui pengaturan gudang');
        }
    }


    public function destroy()
    {
        try {
            $warehouseSetting = WarehouseSetting::first();

            if (!$warehouseSetting) {
                return $this->errorResponse('Pengaturan gudang tidak ditemukan', 404);
            }

            $warehouseSetting->delete();

            return $this->successResponse(null, 'Pengaturan gudang berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pengaturan gudang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus pengaturan gudang');
        }
    }
}
