<?php

namespace App\Http\Requests;

class ItemIndexRequest extends PaginationRequest
{
    /**
     * Get custom validation rules for Item
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return [
            'company_id' => 'nullable|integer|exists:companies,id',
            'flight_id' => 'nullable|integer|exists:flights,id',
            'commodity_type_id' => 'nullable|integer|exists:commodity_types,id',
            'in_invoice' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom validation messages for Item
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return [
            'company_id.integer' => 'ID perusahaan harus berupa angka',
            'company_id.exists' => 'Perusahaan tidak ditemukan',
            'flight_id.integer' => 'ID penerbangan harus berupa angka',
            'flight_id.exists' => 'Penerbangan tidak ditemukan',
            'commodity_type_id.integer' => 'ID jenis komoditas harus berupa angka',
            'commodity_type_id.exists' => 'Jenis komoditas tidak ditemukan',
            'in_invoice.boolean' => 'Filter invoice harus berupa boolean (true/false)',
        ];
    }

    /**
     * Get custom attributes for Item
     *
     * @return array
     */
    protected function getCustomAttributes(): array
    {
        return [
            'company_id' => 'perusahaan',
            'flight_id' => 'penerbangan',
            'commodity_type_id' => 'jenis komoditas',
            'in_invoice' => 'dalam invoice',
        ];
    }
}
