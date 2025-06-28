<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreItemRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'awb' => 'required|string|max:255|unique:items,awb',
            'flight_id' => 'required|exists:flights,id',
            'company_id' => 'nullable|exists:companies,id',
            'commodity' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'gross_weight' => 'required|numeric|min:0',
            'weight_calculation_method' => 'required|string|in:actual,volume',
            'length' => 'required_if:weight_calculation_method,volume|nullable|numeric|min:0',
            'width' => 'required_if:weight_calculation_method,volume|nullable|numeric|min:0',
            'height' => 'required_if:weight_calculation_method,volume|nullable|numeric|min:0',
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
            'weight_calculation_method.required' => 'Metode perhitungan berat wajib diisi.',
            'length.numeric' => 'Panjang harus berupa angka.',
            'width.numeric' => 'Lebar harus berupa angka.',
            'height.numeric' => 'Tinggi harus berupa angka.',
            'weight_calculation_method.string' => 'Metode perhitungan berat harus berupa string.',
            'weight_calculation_method.in' => 'Metode perhitungan berat harus berupa actual atau volume.',
            'length.required_if' => 'Panjang wajib diisi jika metode perhitungan berat adalah volume.',
            'width.required_if' => 'Lebar wajib diisi jika metode perhitungan berat adalah volume.',
            'height.required_if' => 'Tinggi wajib diisi jika metode perhitungan berat adalah volume.',
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
