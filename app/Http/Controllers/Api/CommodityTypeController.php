<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommodityType;
use App\Http\Requests\CommodityTypeIndexRequest;
use App\Http\Requests\StoreCommodityTypeRequest;
use App\Http\Requests\UpdateCommodityTypeRequest;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use App\Traits\SearchFilterTrait;
use Illuminate\Support\Facades\Log;

class CommodityTypeController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    public function index(CommodityTypeIndexRequest $request)
    {
        try {
            $query = CommodityType::query();

            // Get searchable fields for CommodityType
            $searchableFields = $this->getSearchableFields('CommodityType');

            // Apply search
            $this->applySearch($query, $request, $searchableFields);

            // Apply sorting
            if ($request->sort_by) {
                $sortOrder = $request->sort_order ?? 'asc';
                $query->orderBy($request->sort_by, $sortOrder);
            } else {
                $query->orderBy('name', 'asc');
            }

            $result = $this->handlePaginationWithFormat($query, $request);
            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Data jenis komoditas berhasil diambil', 200, $pagination);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data jenis komoditas: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data jenis komoditas');
        }
    }


    public function store(StoreCommodityTypeRequest $request)
    {
        try {
            $data = $request->validated();

            $commodityType = CommodityType::create($data);

            return $this->successResponse(null, 'Jenis komoditas berhasil dibuat', 201);
        } catch (\Exception $e) {
            Log::error('Gagal membuat jenis komoditas: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat jenis komoditas');
        }
    }

    public function show(CommodityType $commodityType)
    {
        try {
            return $this->successResponse($commodityType, 'Detail jenis komoditas berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail jenis komoditas: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil detail jenis komoditas');
        }
    }

    public function update(UpdateCommodityTypeRequest $request, CommodityType $commodityType)
    {
        try {
            $data = $request->validated();

            $commodityType->update($data);

            return $this->successResponse(null, 'Jenis komoditas berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui jenis komoditas: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui jenis komoditas');
        }
    }

    public function destroy(CommodityType $commodityType)
    {
        try {
            if ($commodityType->items()->exists()) {
                return $this->errorResponse('Jenis komoditas tidak dapat dihapus karena masih digunakan oleh barang.', 422);
            }

            $commodityType->delete();

            return $this->successResponse(null, 'Jenis komoditas berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus jenis komoditas: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus jenis komoditas');
        }
    }
}
