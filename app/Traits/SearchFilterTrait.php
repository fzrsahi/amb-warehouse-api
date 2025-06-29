<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SearchFilterTrait
{
    /**
     * Apply search to query
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchableFields
     * @return Builder
     */
    protected function applySearch(Builder $query, Request $request, array $searchableFields = []): Builder
    {
        if ($request->has('search') && !empty($request->search) && !empty($searchableFields)) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search, $searchableFields) {
                foreach ($searchableFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            });
        }

        return $query;
    }

    /**
     * Apply filters to query
     *
     * @param Builder $query
     * @param Request $request
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $query, Request $request, array $filters = []): Builder
    {
        foreach ($filters as $filterKey => $filterConfig) {
            if ($request->has($filterKey) && !empty($request->$filterKey)) {
                $this->applySingleFilter($query, $request, $filterKey, $filterConfig);
            }
        }

        return $query;
    }

    /**
     * Apply single filter based on configuration
     *
     * @param Builder $query
     * @param Request $request
     * @param string $filterKey
     * @param array $filterConfig
     * @return void
     */
    protected function applySingleFilter(Builder $query, Request $request, string $filterKey, array $filterConfig): void
    {
        $value = $request->$filterKey;
        $type = $filterConfig['type'] ?? 'exact';
        $field = $filterConfig['field'] ?? $filterKey;

        switch ($type) {
            case 'exact':
                $query->where($field, $value);
                break;

            case 'like':
                $query->where($field, 'LIKE', "%{$value}%");
                break;

            case 'date':
                $query->whereDate($field, $value);
                break;

            case 'date_range':
                $this->applyDateRangeFilter($query, $request, $filterKey, $filterConfig);
                break;

            case 'in':
                $values = is_string($value) ? explode(',', $value) : (array) $value;
                $query->whereIn($field, $values);
                break;

            case 'relation':
                $this->applyRelationFilter($query, $request, $filterKey, $filterConfig);
                break;

            case 'boolean':
                $query->where($field, (bool) $value);
                break;

            default:
                $query->where($field, $value);
                break;
        }
    }

    /**
     * Apply date range filter
     *
     * @param Builder $query
     * @param Request $request
     * @param string $filterKey
     * @param array $filterConfig
     * @return void
     */
    protected function applyDateRangeFilter(Builder $query, Request $request, string $filterKey, array $filterConfig): void
    {
        $field = $filterConfig['field'] ?? $filterKey;
        $startField = $filterConfig['start_field'] ?? "{$filterKey}_start";
        $endField = $filterConfig['end_field'] ?? "{$filterKey}_end";

        $startDate = $request->get($startField);
        $endDate = $request->get($endField);

        if ($startDate) {
            $query->whereDate($field, '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate($field, '<=', $endDate);
        }
    }

    /**
     * Apply relation filter
     *
     * @param Builder $query
     * @param Request $request
     * @param string $filterKey
     * @param array $filterConfig
     * @return void
     */
    protected function applyRelationFilter(Builder $query, Request $request, string $filterKey, array $filterConfig): void
    {
        $relation = $filterConfig['relation'];
        $field = $filterConfig['field'];
        $value = $request->$filterKey;

        $query->whereHas($relation, function ($q) use ($field, $value) {
            $q->where($field, $value);
        });
    }

    /**
     * Get searchable fields for a model
     *
     * @param string $model
     * @return array
     */
    protected function getSearchableFields(string $model): array
    {
        $searchableFields = [
            'User' => ['name', 'email'],
            'Company' => ['name', 'email', 'phone', 'address'],
            'Flight' => ['flight_number'],
            'Item' => ['code', 'description'],
            'Invoice' => ['invoice_number'],
            'Deposit' => ['deposit_number'],
            'Location' => ['name', 'code'],
            'Airline' => ['name', 'code'],
            'Role' => ['name'],
            'Permission' => ['name'],
            'CommodityType' => ['name'],
            'CommonUsageString' => ['name'],
            'WarehouseSetting' => ['admin_fee', 'tax', 'pnbp'],
        ];

        return $searchableFields[$model] ?? [];
    }

    /**
     * Get default filters for a model
     *
     * @param string $model
     * @return array
     */
    protected function getDefaultFilters(string $model): array
    {
        $defaultFilters = [
            'User' => [
                'role_id' => ['type' => 'relation', 'relation' => 'roles', 'field' => 'id'],
                'company_id' => ['type' => 'exact'],
                'created_at' => ['type' => 'date_range'],
            ],
            'Company' => [
                'status' => ['type' => 'exact', 'field' => 'status'],
                'created_at' => ['type' => 'date_range', 'field' => 'created_at'],
            ],
            'Flight' => [
                'status' => ['type' => 'exact', 'field' => 'status'],
                'airline_id' => ['type' => 'exact', 'field' => 'airline_id'],
                'origin_id' => ['type' => 'exact', 'field' => 'origin_id'],
                'destination_id' => ['type' => 'exact', 'field' => 'destination_id'],
                'departure_time' => ['type' => 'date_range', 'field' => 'departure_time'],
                'arrival_time' => ['type' => 'date_range', 'field' => 'arrival_time'],
            ],
            'Item' => [
                'status' => ['type' => 'exact', 'field' => 'status'],
                'company_id' => ['type' => 'exact', 'field' => 'company_id'],
                'flight_id' => ['type' => 'exact', 'field' => 'flight_id'],
                'created_at' => ['type' => 'date_range', 'field' => 'created_at'],
            ],
            'Invoice' => [
                'status' => ['type' => 'exact', 'field' => 'status'],
                'approval_status' => ['type' => 'exact', 'field' => 'approval_status'],
                'company_id' => ['type' => 'exact', 'field' => 'company_id'],
                'issued_at' => ['type' => 'date_range', 'field' => 'issued_at'],
                'created_at' => ['type' => 'date_range', 'field' => 'created_at'],
            ],
            'Deposit' => [
                'status' => ['type' => 'exact', 'field' => 'status'],
                'company_id' => ['type' => 'exact', 'field' => 'company_id'],
                'created_by_user_id' => ['type' => 'exact', 'field' => 'created_by_user_id'],
                'accepted_by_user_id' => ['type' => 'exact', 'field' => 'accepted_by_user_id'],
                'deposit_at' => ['type' => 'date_range', 'field' => 'deposit_at'],
                'created_at' => ['type' => 'date_range', 'field' => 'created_at'],
                'accepted_at' => ['type' => 'date_range', 'field' => 'accepted_at'],
            ],
        ];

        return $defaultFilters[$model] ?? [];
    }
}
