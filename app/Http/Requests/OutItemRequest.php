<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OutItemRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer|exists:items,id',
        ];
    }

    public function messages(): array
    {
        return [
            'item_ids.required' => 'ID barang wajib diisi.',
            'item_ids.array' => 'ID barang harus berupa array.',
            'item_ids.min' => 'Minimal satu barang harus dipilih.',
            'item_ids.*.required' => 'ID barang tidak boleh kosong.',
            'item_ids.*.integer' => 'ID barang harus berupa angka.',
            'item_ids.*.exists' => 'Barang dengan ID tersebut tidak ditemukan.',
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
