<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class StoreAirlineRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:airlines,code',
            'cargo_handling_incoming_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'cargo_handling_outgoing_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'handling_airplane_outgoing_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'handling_airplane_incoming_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'jppgc_incoming_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'jppgc_outgoing_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
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
            'cargo_handling_incoming_price.required' => 'Harga cargo handling incoming harus diisi',
            'cargo_handling_incoming_price.numeric' => 'Harga cargo handling incoming harus berupa angka',
            'cargo_handling_incoming_price.min' => 'Harga cargo handling incoming harus lebih dari 0',
            'cargo_handling_incoming_price.regex' => 'Harga cargo handling incoming harus berupa angka dan maksimal 2 desimal',
            'cargo_handling_outgoing_price.required' => 'Harga cargo handling outgoing harus diisi',
            'cargo_handling_outgoing_price.numeric' => 'Harga cargo handling outgoing harus berupa angka',
            'cargo_handling_outgoing_price.min' => 'Harga cargo handling outgoing harus lebih dari 0',
            'cargo_handling_outgoing_price.regex' => 'Harga cargo handling outgoing harus berupa angka dan maksimal 2 desimal',
            'handling_airplane_outgoing_price.required' => 'Harga handling airplane outgoing harus diisi',
            'handling_airplane_outgoing_price.numeric' => 'Harga handling airplane outgoing harus berupa angka',
            'handling_airplane_outgoing_price.min' => 'Harga handling airplane outgoing harus lebih dari 0',
            'handling_airplane_outgoing_price.regex' => 'Harga handling airplane outgoing harus berupa angka dan maksimal 2 desimal',
            'handling_airplane_incoming_price.required' => 'Harga handling airplane incoming harus diisi',
            'handling_airplane_incoming_price.numeric' => 'Harga handling airplane incoming harus berupa angka',
            'handling_airplane_incoming_price.min' => 'Harga handling airplane incoming harus lebih dari 0',
            'handling_airplane_incoming_price.regex' => 'Harga handling airplane incoming harus berupa angka dan maksimal 2 desimal',
            'jppgc_incoming_price.required' => 'Harga JPPGC incoming harus diisi',
            'jppgc_incoming_price.numeric' => 'Harga JPPGC incoming harus berupa angka',
            'jppgc_incoming_price.min' => 'Harga JPPGC incoming harus lebih dari 0',
            'jppgc_incoming_price.regex' => 'Harga JPPGC incoming harus berupa angka dan maksimal 2 desimal',
            'jppgc_outgoing_price.required' => 'Harga JPPGC outgoing harus diisi',
            'jppgc_outgoing_price.numeric' => 'Harga JPPGC outgoing harus berupa angka',
            'jppgc_outgoing_price.min' => 'Harga JPPGC outgoing harus lebih dari 0',
            'jppgc_outgoing_price.regex' => 'Harga JPPGC outgoing harus berupa angka dan maksimal 2 desimal',
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
