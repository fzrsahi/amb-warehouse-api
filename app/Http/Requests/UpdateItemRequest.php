<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class UpdateItemRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('item')->id;

        return [
            'awb' => 'required|string|max:255|unique:items,awb,' . $itemId,
            'flight_id' => 'required|exists:flights,id',
            'company_id' => 'nullable|exists:companies,id',
            'commodity' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'gross_weight' => 'required|numeric|min:0',
            'chargeable_weight' => 'required|numeric|min:0',
            'status' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'awb.required' => 'Nomor AWB wajib diisi.',
            'awb.unique' => 'Nomor AWB sudah terdaftar.',
            'flight_id.required' => 'Penerbangan wajib diisi.',
            'flight_id.exists' => 'Penerbangan tidak ditemukan.',
            'company_id.exists' => 'Perusahaan tidak ditemukan.',
            'commodity.required' => 'Komoditas wajib diisi.',
            'qty.required' => 'Jumlah (Qty) wajib diisi.',
            'qty.integer' => 'Jumlah (Qty) harus berupa angka.',
            'gross_weight.required' => 'Berat kotor wajib diisi.',
            'chargeable_weight.required' => 'Berat kena cas wajib diisi.',
            'status.required' => 'Status wajib diisi.',
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
