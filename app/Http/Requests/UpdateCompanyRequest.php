<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class UpdateCompanyRequest extends FormRequest
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
        $companyId = $this->route('company')->id;

        return [
            'company_name'      => 'required|string|max:255|unique:companies,name,' . $companyId,
            'company_email'     => 'required|email|max:255|unique:companies,email,' . $companyId,
            'company_phone'     => 'required|string|max:20',
            'company_address'   => 'required|string',
            'company_logo'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'Nama perusahaan wajib diisi',
            'company_name.string' => 'Nama perusahaan harus berupa teks',
            'company_name.max' => 'Nama perusahaan maksimal 255 karakter',
            'company_name.unique' => 'Nama perusahaan sudah terdaftar',
            'company_email.required' => 'Email wajib diisi',
            'company_email.email' => 'Format email tidak valid',
            'company_email.max' => 'Email maksimal 255 karakter',
            'company_email.unique' => 'Email sudah terdaftar',
            'company_phone.required' => 'Nomor telepon wajib diisi',
            'company_phone.string' => 'Nomor telepon harus berupa teks',
            'company_phone.max' => 'Nomor telepon maksimal 20 karakter',
            'company_address.required' => 'Alamat wajib diisi',
            'company_address.string' => 'Alamat harus berupa teks',
            'company_logo.image' => 'File logo harus berupa gambar',
            'company_logo.mimes' => 'Format logo harus jpeg, png, jpg, gif, atau svg',
            'company_logo.max' => 'Ukuran logo maksimal 2MB',
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
