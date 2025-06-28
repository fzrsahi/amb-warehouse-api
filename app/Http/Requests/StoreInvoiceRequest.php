<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInvoiceRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer|exists:items,id',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'ID Perusahaan wajib diisi.',
            'company_id.exists' => 'Perusahaan tidak ditemukan.',
            'item_ids.required' => 'Daftar item wajib diisi.',
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
