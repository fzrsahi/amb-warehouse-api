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
        ];
    }
}
