<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAirlineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:airlines,code',
            'price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama airline harus diisi',
            'name.string' => 'Nama airline harus berupa string',
            'name.max' => 'Nama airline maksimal 255 karakter',
            'code.required' => 'Kode airline harus diisi',
            'code.string' => 'Kode airline harus berupa string',
            'code.max' => 'Kode airline maksimal 255 karakter',
            'code.unique' => 'Kode airline sudah ada',
            'price.required' => 'Harga harus diisi',
            'price.numeric' => 'Harga harus berupa angka',
            'price.min' => 'Harga harus lebih dari 0',
            'price.regex' => 'Harga harus berupa angka dan maksimal 2 desimal',
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
