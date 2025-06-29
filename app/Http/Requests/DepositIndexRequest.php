<?php

namespace App\Http\Requests;

class DepositIndexRequest extends PaginationRequest
{
    /**
     * Get custom validation rules for Deposit
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return [
            'status' => 'nullable|string|in:submit,approve,reject',
            'company_id' => 'nullable|integer|exists:companies,id',
        ];
    }

    /**
     * Get custom validation messages for Deposit
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return [
            'status.in' => 'Status harus submit, approve, atau reject',
            'company_id.integer' => 'ID perusahaan harus berupa angka',
            'company_id.exists' => 'Perusahaan tidak ditemukan',
        ];
    }

    /**
     * Get custom attributes for Deposit
     *
     * @return array
     */
    protected function getCustomAttributes(): array
    {
        return [
            'status' => 'status',
            'company_id' => 'perusahaan',
        ];
    }
}
