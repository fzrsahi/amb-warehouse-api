<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\PaginationTrait;
use App\Traits\SearchFilterTrait;
use App\Models\Flight;
use App\Http\Requests\FlightIndexRequest;
use App\Traits\ApiResponse;
use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class FlightController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    public function index(FlightIndexRequest $request)
    {
        try {
            $query = Flight::with(['origin:id,name,code', 'destination:id,name,code', 'airline:id,name,code']);

            $searchableFields = $this->getSearchableFields('Flight');

            $this->applySearch($query, $request, $searchableFields);

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->airline_id) {
                $query->where('airline_id', $request->airline_id);
            }

            if ($request->arrival_time_start) {
                $query->whereDate('arrival_time', '>=', $request->arrival_time_start);
            }

            if ($request->arrival_time_end) {
                $query->whereDate('arrival_time', '<=', $request->arrival_time_end);
            }

            if ($request->sort_by) {
                $sortOrder = $request->sort_order ?? 'asc';
                $query->orderBy($request->sort_by, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

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

            Flight::create($data);
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
