<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class UpdateFlightRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi akan kita tangani di controller menggunakan middleware/gate Spatie.
        // Jadi, di sini kita set ke true.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'origin_id' => 'sometimes|exists:locations,id',
            'destination_id' => 'sometimes|exists:locations,id',
            'airline_id' => 'sometimes|exists:airlines,id',
            'flight_date' => 'sometimes|date_format:Y-m-d',
            'departure_at' => 'sometimes|date_format:Y-m-d H:i',
            'arrival_at' => 'nullable|date_format:Y-m-d H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'origin_id.exists' => 'Lokasi asal tidak valid',
            'destination_id.exists' => 'Lokasi tujuan tidak valid',
            'airline_id.exists' => 'Maskapai tidak valid',
            'flight_date.date_format' => 'Format tanggal penerbangan harus YYYY-MM-DD',
            'departure_at.date_format' => 'Format waktu keberangkatan harus YYYY-MM-DD HH:MM',
            'arrival_at.date_format' => 'Format waktu kedatangan harus YYYY-MM-DD HH:MM',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $firstError = collect($errors)->first();
        $errorMessage = $firstError[0] ?? 'Validasi gagal';

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $errorMessage,
                'data' => null,
            ], 422)
        );
    }
}
