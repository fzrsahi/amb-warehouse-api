<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use Illuminate\Support\Facades\Log;
use App\Models\Location;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Traits\SearchFilterTrait;


class LocationController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $query = Location::query();

            // Get searchable fields for Location
            $searchableFields = $this->getSearchableFields('Location');

            // Apply search
            $this->applySearch($query, $request, $searchableFields);

            // Apply sorting
            if ($request->sort_by) {
                $sortOrder = $request->sort_order ?? 'asc';
                $query->orderBy($request->sort_by, $sortOrder);
            } else {
                $query->orderBy('name', 'asc');
            }

            $result = $this->handlePaginationWithFormat($query, $request, ["id", "name", "code"]);

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, message: 'Daftar lokasi berhasil diambil.', code: 200, pagination: $pagination);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil daftar lokasi: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil daftar lokasi.');
        }
    }

    public function show(Location $location)
    {
        try {
            return $this->successResponse($location, 'Detail lokasi berhasil diambil', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil detail lokasi: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil detail lokasi');
        }
    }

    public function store(StoreLocationRequest $request)
    {
        try {
            $location = Location::create($request->all());
            return $this->successResponse(null, message: 'Lokasi berhasil dibuat.', code: 201);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat membuat lokasi: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat lokasi.');
        }
    }


    public function update(UpdateLocationRequest $request, Location $location)
    {
        try {
            $location->update($request->all());
            return $this->successResponse(null, message: 'Lokasi berhasil diubah.', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengubah lokasi: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengubah lokasi.');
        }
    }

    public function destroy(Location $location)
    {
        try {
            $location->delete();
            return $this->successResponse(null, message: 'Lokasi berhasil dihapus.', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat menghapus lokasi: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus lokasi.');
        }
    }
}
