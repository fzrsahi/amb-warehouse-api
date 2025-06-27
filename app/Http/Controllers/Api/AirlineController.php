<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Http\Requests\PaginationRequest;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAirlineRequest;
use App\Http\Requests\UpdateAirlineRequest;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use Illuminate\Support\Facades\Log;

class AirlineController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $query = Airline::query();
            $result = $this->handlePaginationWithFormat($query, $request);

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Data airline berhasil diambil', code: 200, pagination: $pagination);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data airline: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data airline');
        }
    }

    public function store(StoreAirlineRequest $request)
    {
        try {
            $airline = Airline::create($request->all());
            return $this->successResponse(null, 'Data airline berhasil dibuat', code: 201);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat membuat data airline: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat data airline');
        }
    }

    public function update(UpdateAirlineRequest $request, Airline $airline)
    {
        try {
            $airline->update($request->all());
            return $this->successResponse(null, 'Data airline berhasil diubah', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengubah data airline: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengubah data airline');
        }
    }

    public function destroy(Airline $airline)
    {
        try {
            $airline->delete();
            return $this->successResponse(null, 'Data airline berhasil dihapus', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat menghapus data airline: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus data airline');
        }
    }
}
