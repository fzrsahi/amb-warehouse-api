<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateInvoiceRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cargo_handling_fee' => 'nullable|numeric|min:0',
            'air_handling_fee' => 'nullable|numeric|min:0',
            'inspection_fee' => 'nullable|numeric|min:0',
            'admin_fee' => 'nullable|numeric|min:0',
            'item_ids' => 'nullable|array|min:1',
            'item_ids.*' => 'required|integer|exists:items,id',
        ];
    }

    public function messages(): array
    {
        return [
            'cargo_handling_fee.numeric' => 'Biaya handling kargo harus berupa angka.',
            'cargo_handling_fee.min' => 'Biaya handling kargo minimal 0.',
            'air_handling_fee.numeric' => 'Biaya handling udara harus berupa angka.',
            'air_handling_fee.min' => 'Biaya handling udara minimal 0.',
            'inspection_fee.numeric' => 'Biaya pemeriksaan harus berupa angka.',
            'inspection_fee.min' => 'Biaya pemeriksaan minimal 0.',
            'admin_fee.numeric' => 'Biaya administrasi harus berupa angka.',
            'admin_fee.min' => 'Biaya administrasi minimal 0.',
            'item_ids.array' => 'Item harus berupa array.',
            'item_ids.min' => 'Pilih minimal satu item untuk ditagih.',
            'item_ids.*.exists' => 'Salah satu ID item tidak valid.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $firstError = collect($errors)->first();
        $errorMessage = $firstError[0] ?? 'Validasi gagal';

        throw new HttpResponseException($this->errorResponse($errorMessage, 422, $errors));
    }
}
