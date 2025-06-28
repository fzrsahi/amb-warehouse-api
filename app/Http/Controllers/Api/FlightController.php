<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\PaginationTrait;
use App\Models\Flight;
use App\Http\Requests\PaginationRequest;
use App\Traits\ApiResponse;
use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class FlightController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $query = Flight::with(['origin:id,name,code', 'destination:id,name,code', 'airline:id,name,code']);
            $result = $this->handlePaginationWithFormat($query, $request);

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Flight berhasil diambil', code: 200, pagination: $pagination);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data flight: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data flight');
        }
    }

    public function show(Flight $flight)
    {
        try {
            $flight->load(['origin:id,name,code', 'destination:id,name,code', 'airline:id,name,code']);
            return $this->successResponse($flight, 'Flight berhasil diambil', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data flight: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data flight');
        }
    }

    public function store(StoreFlightRequest $request)
    {
        try {
            $data = $request->all();

            $origin = Location::find($data['origin_id']);
            if ($origin && ($origin->code === 'GTO' || $origin->code === 'gto')) {
                $data['status'] = 'outgoing';
            } else {
                $data['status'] = 'incoming';
            }

            $flight = Flight::create($data);
            return $this->successResponse(null, 'Flight berhasil dibuat', code: 201);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat membuat data flight: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat data flight');
        }
    }

    public function update(UpdateFlightRequest $request, Flight $flight)
    {
        try {
            $data = $request->all();

            if (isset($data['origin_id'])) {
                $origin = Location::find($data['origin_id']);
                if ($origin && ($origin->code === 'GTO' || $origin->code === 'gto')) {
                    $data['status'] = 'outgoing';
                } else {
                    $data['status'] = 'incoming';
                }
            }

            $flight->update($data);

            return $this->successResponse(null, 'Flight berhasil diperbarui', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat memperbarui data flight: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui data flight');
        }
    }

    public function destroy(Flight $flight)
    {
        try {
            $flight->delete();
            return $this->successResponse(null, 'Flight berhasil dihapus', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat menghapus data flight: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus data flight');
        }
    }
}
