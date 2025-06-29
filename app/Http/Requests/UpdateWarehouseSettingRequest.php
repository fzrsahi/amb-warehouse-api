<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class UpdateWarehouseSettingRequest extends FormRequest
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
            'admin_fee' => 'sometimes|numeric|min:0|max:999999999999.99',
            'tax' => 'sometimes|numeric|min:0|max:100',
            'pnbp' => 'sometimes|numeric|min:0|max:999999999999.99',
            'minimal_charge_weight' => 'sometimes|integer|min:0',
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
            'admin_fee.numeric' => 'Biaya admin harus berupa angka',
            'admin_fee.min' => 'Biaya admin minimal 0',
            'admin_fee.max' => 'Biaya admin maksimal 999999999999.99',
            'tax.numeric' => 'Pajak harus berupa angka',
            'tax.min' => 'Pajak minimal 0%',
            'tax.max' => 'Pajak maksimal 100%',
            'pnbp.numeric' => 'PNBP harus berupa angka',
            'pnbp.min' => 'PNBP minimal 0',
            'pnbp.max' => 'PNBP maksimal 999999999999.99',
            'minimal_charge_weight.integer' => 'Minimal charge weight harus berupa angka',
            'minimal_charge_weight.min' => 'Minimal charge weight minimal 0',
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
            'admin_fee' => 'biaya admin',
            'tax' => 'pajak',
            'pnbp' => 'PNBP',
            'minimal_charge_weight' => 'minimal charge weight',
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
