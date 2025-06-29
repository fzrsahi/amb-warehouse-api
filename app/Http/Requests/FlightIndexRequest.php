<?php

namespace App\Http\Requests;

class FlightIndexRequest extends PaginationRequest
{
    /**
     * Get custom validation rules for Flight
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return [
            'status' => 'nullable|string|in:incoming,outgoing',
            'airline_id' => 'nullable|integer|exists:airlines,id',
            'arrival_time_start' => 'nullable|date|before_or_equal:arrival_time_end',
            'arrival_time_end' => 'nullable|date|after_or_equal:arrival_time_start',
        ];
    }

    /**
     * Get custom validation messages for Flight
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return [
            'status.in' => 'Status harus incoming atau outgoing',
            'airline_id.integer' => 'ID maskapai harus berupa angka',
            'airline_id.exists' => 'Maskapai tidak ditemukan',
            'arrival_time_start.date' => 'Waktu kedatangan mulai harus berupa tanggal yang valid',
            'arrival_time_start.before_or_equal' => 'Waktu kedatangan mulai harus sebelum atau sama dengan waktu akhir',
            'arrival_time_end.date' => 'Waktu kedatangan akhir harus berupa tanggal yang valid',
            'arrival_time_end.after_or_equal' => 'Waktu kedatangan akhir harus setelah atau sama dengan waktu mulai',
        ];
    }

    /**
     * Get custom attributes for Flight
     *
     * @return array
     */
    protected function getCustomAttributes(): array
    {
        return [
            'status' => 'status',
            'airline_id' => 'maskapai',
            'arrival_time_start' => 'waktu kedatangan mulai',
            'arrival_time_end' => 'waktu kedatangan akhir',
        ];
    }
}
