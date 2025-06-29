<?php

namespace App\Http\Requests;

class InvoiceIndexRequest extends PaginationRequest
{
    /**
     * Get custom validation rules for Invoice
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return [
            'status' => 'nullable|string|in:incoming,outgoing',
            'company_id' => 'nullable|integer|exists:companies,id',
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }

    /**
     * Get custom validation messages for Invoice
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return [
            'status.in' => 'Status harus salah satu dari: incoming, outgoing',
            'company_id.integer' => 'ID perusahaan harus berupa angka',
            'company_id.exists' => 'Perusahaan tidak ditemukan',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid',
            'start_date.before_or_equal' => 'Tanggal mulai harus sebelum atau sama dengan tanggal akhir',
            'end_date.date' => 'Tanggal akhir harus berupa tanggal yang valid',
            'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai',
        ];
    }

    /**
     * Get custom attributes for Invoice
     *
     * @return array
     */
    protected function getCustomAttributes(): array
    {
        return [
            'status' => 'status',
            'company_id' => 'perusahaan',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal akhir',
        ];
    }
}
