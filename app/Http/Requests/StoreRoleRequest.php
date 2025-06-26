<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class StoreRoleRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama peran wajib diisi',
            'name.string' => 'Nama peran harus berupa teks',
            'name.max' => 'Nama peran maksimal 255 karakter',
            'name.unique' => 'Nama peran sudah terdaftar',
            'permissions.array' => 'Permissions harus berupa array',
            'permissions.*.exists' => 'Permission tidak ditemukan',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors()->toArray(), 'Validasi gagal')
        );
    }
}
