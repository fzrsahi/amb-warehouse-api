<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommonUsageStringIndexRequest extends PaginationRequest
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
            //
        ];
    }

    /**
     * Get custom validation rules for CommonUsageString
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return [
            'name' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation messages for CommonUsageString
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return [
            'name.string' => 'Nama harus berupa string',
        ];
    }

    /**
     * Get custom attributes for CommonUsageString
     *
     * @return array
     */
    protected function getCustomAttributes(): array
    {
        return [
            'name' => 'nama',
        ];
    }
}
