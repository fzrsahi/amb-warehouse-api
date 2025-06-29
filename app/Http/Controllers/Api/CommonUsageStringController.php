<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommonUsageString;
use App\Http\Requests\CommonUsageStringIndexRequest;
use App\Http\Requests\StoreCommonUsageStringRequest;
use App\Http\Requests\UpdateCommonUsageStringRequest;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use App\Traits\SearchFilterTrait;
use Illuminate\Support\Facades\Log;

class CommonUsageStringController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(CommonUsageStringIndexRequest $request)
    {
        try {
            $query = CommonUsageString::query();

            // Get searchable fields for CommonUsageString
            $searchableFields = $this->getSearchableFields('CommonUsageString');

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

            return $this->successResponse($data, 'Data string penggunaan umum berhasil diambil', 200, $pagination);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data string penggunaan umum: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data string penggunaan umum');
        }
    }

    public function store(StoreCommonUsageStringRequest $request)
    {
        try {
            $data = $request->validated();

            $commonUsageString = CommonUsageString::create($data);

            return $this->successResponse($commonUsageString, 'String penggunaan umum berhasil dibuat', 201);
        } catch (\Exception $e) {
            Log::error('Gagal membuat string penggunaan umum: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat string penggunaan umum');
        }
    }

    public function show(CommonUsageString $commonUsageString)
    {
        try {
            return $this->successResponse($commonUsageString, 'Detail string penggunaan umum berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail string penggunaan umum: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil detail string penggunaan umum');
        }
    }


    public function update(UpdateCommonUsageStringRequest $request, CommonUsageString $commonUsageString)
    {
        try {
            $data = $request->validated();

            $commonUsageString->update($data);

            return $this->successResponse($commonUsageString, 'String penggunaan umum berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui string penggunaan umum: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui string penggunaan umum');
        }
    }

    public function destroy(CommonUsageString $commonUsageString)
    {
        try {
            $commonUsageString->delete();

            return $this->successResponse(null, 'String penggunaan umum berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus string penggunaan umum: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus string penggunaan umum');
        }
    }
}
