<?php

namespace App\Http\Requests;

class CommodityTypeIndexRequest extends PaginationRequest
{
    /**
     * Get custom validation rules for CommodityType
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return [
            'name' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation messages for CommodityType
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return [
            'name.string' => 'Nama harus berupa string',
        ];
    }

    /**
     * Get custom attributes for CommodityType
     *
     * @return array
     */
    protected function getCustomAttributes(): array
    {
        return [
            'name' => 'nama',
        ];
    }
}
