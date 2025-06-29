<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Flight;
use App\Models\WarehouseSetting;
use App\Http\Requests\ItemIndexRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Requests\OutItemRequest;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use App\Traits\SearchFilterTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    /**
     * Generate unique item code.
     * Format: ORIGIN + IN/OUT + DEST + datetime + string unique
     * Example: GTIINCGK202506261234567
     */
    private function generateItemCode(Item $item): string
    {
        $flight = Flight::with(['origin', 'destination'])->find($item->flight_id);

        // Get origin code (fallback to XXX)
        $originCode = $flight->origin->code ?? 'XXX';

        // Convert flight status to IN/OUT
        $statusCode = ($flight->status === 'incoming') ? 'IN' : 'OUT';

        // Get destination code (fallback to XXX)
        $destinationCode = $flight->destination->code ?? 'XXX';

        // Format datetime as YYYYMMDD
        $datePart = date('Ymd');

        // Generate unique string (combination of item ID and timestamp)
        $uniqueString = str_pad($item->id, 7, '0', STR_PAD_LEFT);

        return strtoupper($originCode . $statusCode . $destinationCode . $datePart . $uniqueString);
    }

    public function index(ItemIndexRequest $request)
    {
        try {
            $user = $request->user();
            $query = Item::with([
                'company:id,name',
                'flight.origin:id,code',
                'flight.destination:id,code',
                'commodityType:id,name',
                'createdBy:id,name',
                'acceptedBy:id,name'
            ]);

            $userRoleType = $user->roles->first()->type ?? null;

            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
                // Super admin and warehouse can see all items
            } elseif ($userRoleType === 'company') {
                $query->where('company_id', $user->company_id);
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk melihat data barang.');
            }

            $searchableFields = $this->getSearchableFields('Item');
            $defaultFilters = $this->getDefaultFilters('Item');

            $customFilters = [
                'company_id' => ['type' => 'exact'],
                'flight_id' => ['type' => 'exact'],
                'commodity_type_id' => ['type' => 'exact'],
            ];

            $filters = array_merge($defaultFilters, $customFilters);

            // Apply search and filters
            $this->applySearch($query, $request, $searchableFields);
            $this->applyFilters($query, $request, $filters);

            // Apply custom invoice filter
            $this->applyInvoiceFilter($query, $request);

            // Apply AWB completeness filter
            $this->applyAwbCompletenessFilter($query, $request);

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

            // Check AWB completeness - convert to array and add completion info
            if ($data && count($data) > 0) {
                // Convert to array if it's a Collection
                $dataArray = is_object($data) ? $data->toArray() : $data;

                // If items are objects, convert them to arrays
                if (!empty($dataArray) && is_object($dataArray[0])) {
                    $dataArray = array_map(function ($item) {
                        return is_object($item) ? $item->toArray() : $item;
                    }, $dataArray);
                }

                // Add AWB completion info
                $dataArray = $this->checkAwbCompleteness($dataArray);
                $data = $dataArray;
            }

            return $this->successResponse($data, 'Data barang berhasil diambil', 200, $pagination);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data barang');
        }
    }

    /**
     * Check AWB completeness by comparing sum of qty with total_qty for each AWB
     *
     * @param array $items
     * @return array
     */
    private function checkAwbCompleteness(array $items): array
    {
        // Group items by AWB
        $awbGroups = [];
        foreach ($items as $item) {
            $awb = $item['awb'];
            $qty = $item['qty'];
            $totalQty = $item['total_qty'];

            if (!isset($awbGroups[$awb])) {
                $awbGroups[$awb] = [
                    'items' => [],
                    'total_qty_expected' => 0,
                    'total_qty_actual' => 0
                ];
            }

            $awbGroups[$awb]['items'][] = $item;
            $awbGroups[$awb]['total_qty_expected'] = $totalQty; // Should be same for all items with same AWB
            $awbGroups[$awb]['total_qty_actual'] += $qty;
        }

        // Add completeness information to each item
        foreach ($items as &$item) {
            $awb = $item['awb'];
            $awbGroup = $awbGroups[$awb];

            // Check if AWB is complete
            $isAwbComplete = $awbGroup['total_qty_actual'] >= $awbGroup['total_qty_expected'];

            // Add AWB completion info to item
            $item['awb_completion'] = [
                'awb' => $awb,
                'expected_total_qty' => $awbGroup['total_qty_expected'],
                'actual_total_qty' => $awbGroup['total_qty_actual'],
                'is_complete' => $isAwbComplete,
                'completion_percentage' => $awbGroup['total_qty_expected'] > 0
                    ? round(($awbGroup['total_qty_actual'] / $awbGroup['total_qty_expected']) * 100, 2)
                    : 0
            ];
        }

        return $items;
    }

    /**
     * Apply invoice filter to check if items are included in invoices
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param ItemIndexRequest $request
     * @return void
     */
    private function applyInvoiceFilter($query, $request)
    {
        if ($request->has('in_invoice')) {
            $inInvoice = filter_var($request->in_invoice, FILTER_VALIDATE_BOOLEAN);

            if ($inInvoice) {
                // Items that are included in invoices
                $query->whereHas('invoices');
            } else {
                // Items that are not included in invoices
                $query->whereDoesntHave('invoices');
            }
        }
    }

    /**
     * Apply AWB completeness filter
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param ItemIndexRequest $request
     * @return void
     */
    private function applyAwbCompletenessFilter($query, $request)
    {
        if ($request->has('awb_complete')) {
            $awbComplete = filter_var($request->awb_complete, FILTER_VALIDATE_BOOLEAN);

            if ($awbComplete) {
                // Items with complete AWB - where sum of qty equals total_qty for that AWB
                $query->whereIn('awb', function ($subQuery) {
                    $subQuery->select('awb')
                        ->from('items')
                        ->groupBy('awb', 'total_qty')
                        ->havingRaw('SUM(qty) >= MAX(total_qty)');
                });
            } else {
                // Items with incomplete AWB - where sum of qty is less than total_qty for that AWB
                $query->whereIn('awb', function ($subQuery) {
                    $subQuery->select('awb')
                        ->from('items')
                        ->groupBy('awb', 'total_qty')
                        ->havingRaw('SUM(qty) < MAX(total_qty)');
                });
            }
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

            $warehouseSetting = WarehouseSetting::first();
            $minimalChargeWeight = $warehouseSetting ? $warehouseSetting->minimal_charge_weight : 0;

            if ($chargeable_weight < $minimalChargeWeight) {
                $chargeable_weight = $minimalChargeWeight;
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

            $item->load([
                'company:id,name',
                'flight.origin:id,code',
                'flight.destination:id,code',
                'commodityType:id,name',
                'createdBy:id,name',
                'acceptedBy:id,name',
                'outBy:id,name'
            ]);
            return $this->successResponse($item, 'Detail barang berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil detail barang');
        }
    }

    public function getByAwb(string $awb)
    {
        try {
            $user = request()->user();
            $userRoleType = $user->roles->first()->type ?? null;

            // Query items with the same AWB
            $query = Item::with([
                'company:id,name',
                'flight.origin:id,code',
                'flight.destination:id,code',
                'commodityType:id,name',
                'createdBy:id,name',
                'acceptedBy:id,name',
                'outBy:id,name'
            ])->where('awb', $awb);

            // Apply authorization
            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
                // Super admin and warehouse can see all items
            } elseif ($userRoleType === 'company') {
                $query->where('company_id', $user->company_id);
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk melihat data barang.');
            }

            $items = $query->get();

            if ($items->isEmpty()) {
                return $this->notFoundResponse('Tidak ada barang dengan AWB tersebut atau Anda tidak memiliki akses');
            }

            // Convert to array and add AWB completion info
            $dataArray = $items->toArray();
            $dataArray = $this->checkAwbCompleteness($dataArray);

            // Add summary information
            $firstItem = $dataArray[0];
            $awbSummary = [
                'awb' => $awb,
                'total_items' => count($dataArray),
                'expected_total_qty' => $firstItem['awb_completion']['expected_total_qty'],
                'actual_total_qty' => $firstItem['awb_completion']['actual_total_qty'],
                'is_complete' => $firstItem['awb_completion']['is_complete'],
                'completion_percentage' => $firstItem['awb_completion']['completion_percentage'],
                'company' => $firstItem['company'],
                'flight' => isset($firstItem['flight']) ? [
                    'origin' => $firstItem['flight']['origin'],
                    'destination' => $firstItem['flight']['destination']
                ] : null
            ];

            $responseData = [
                'awb_summary' => $awbSummary,
                'items' => $dataArray
            ];

            return $this->successResponse($responseData, "Data barang dengan AWB {$awb} berhasil diambil");
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data barang berdasarkan AWB: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data barang');
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

    public function out(OutItemRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            $userRoleType = $user->roles->first()->type ?? null;

            // Check if user has permission to release items
            if (!($user->hasRole('super-admin') || $userRoleType === 'warehouse')) {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk mengeluarkan barang.');
            }

            $itemIds = $request->validated()['item_ids'];
            $items = Item::whereIn('id', $itemIds)->get();

            if ($items->count() !== count($itemIds)) {
                return $this->errorResponse('Beberapa barang tidak ditemukan.', 422);
            }

            $successCount = 0;
            $failedItems = [];

            foreach ($items as $item) {
                if ($item->status !== 'at_origin_warehouse') {
                    $failedItems[] = [
                        'id' => $item->id,
                        'awb' => $item->awb,
                        'reason' => 'Barang tidak dalam status di gudang asal.'
                    ];
                    continue;
                }

                $item->update([
                    'status' => 'out_from_origin_warehouse',
                    'out_at' => now(),
                    'out_by_user_id' => $user->id,
                ]);

                $successCount++;
            }

            DB::commit();

            $message = "Berhasil mengeluarkan {$successCount} barang dari gudang asal.";
            if (count($failedItems) > 0) {
                $message .= " " . count($failedItems) . " barang gagal dikeluarkan.";
            }

            return $this->successResponse([
                'success_count' => $successCount,
                'failed_items' => $failedItems,
                'total_items' => count($itemIds)
            ], $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengeluarkan barang: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengeluarkan barang');
        }
    }
}
