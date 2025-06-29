<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class UpdateCommodityTypeRequest extends FormRequest
{
    use ApiResponse;

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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('commodity_types', 'name')->ignore($this->commodity_type),
            ],
        ];
    }

    /**
     * Get custom validation messages for the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis komoditas wajib diisi',
            'name.string' => 'Nama jenis komoditas harus berupa string',
            'name.max' => 'Nama jenis komoditas maksimal 255 karakter',
            'name.unique' => 'Nama jenis komoditas sudah ada',
        ];
    }

    /**
     * Get custom attributes for the request.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama jenis komoditas',
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
