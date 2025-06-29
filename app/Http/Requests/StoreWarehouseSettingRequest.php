<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class StoreWarehouseSettingRequest extends FormRequest
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
            'admin_fee' => 'required|numeric|min:0|max:999999999999.99',
            'tax' => 'required|numeric|min:0|max:100',
            'pnbp' => 'required|numeric|min:0|max:999999999999.99',
            'minimal_charge_weight' => 'required|integer|min:0',
            'max_negative_balance' => 'required|numeric|min:0|max:999999999999.99',
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
            'admin_fee.required' => 'Biaya admin wajib diisi',
            'admin_fee.numeric' => 'Biaya admin harus berupa angka',
            'admin_fee.min' => 'Biaya admin minimal 0',
            'admin_fee.max' => 'Biaya admin maksimal 999999999999.99',
            'tax.required' => 'Pajak wajib diisi',
            'tax.numeric' => 'Pajak harus berupa angka',
            'tax.min' => 'Pajak minimal 0%',
            'tax.max' => 'Pajak maksimal 100%',
            'pnbp.required' => 'PNBP wajib diisi',
            'pnbp.numeric' => 'PNBP harus berupa angka',
            'pnbp.min' => 'PNBP minimal 0',
            'pnbp.max' => 'PNBP maksimal 999999999999.99',
            'minimal_charge_weight.required' => 'Minimal charge weight wajib diisi',
            'minimal_charge_weight.integer' => 'Minimal charge weight harus berupa angka',
            'minimal_charge_weight.min' => 'Minimal charge weight minimal 0',
            'max_negative_balance.required' => 'Maksimal minus saldo wajib diisi',
            'max_negative_balance.numeric' => 'Maksimal minus saldo harus berupa angka',
            'max_negative_balance.min' => 'Maksimal minus saldo minimal 0',
            'max_negative_balance.max' => 'Maksimal minus saldo maksimal 999999999999.99',
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
            'max_negative_balance' => 'maksimal minus saldo',
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
