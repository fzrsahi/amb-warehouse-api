<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginationRequest extends FormRequest
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
        $baseRules = [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|string|max:50',
            'sort_order' => 'nullable|in:asc,desc',
        ];

        // Get custom rules from child class if exists
        $customRules = $this->getCustomRules();

        return array_merge($baseRules, $customRules);
    }

    /**
     * Get custom validation rules for specific controllers
     * Override this method in child classes to add custom rules
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return [];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        $baseMessages = [
            'page.integer' => 'Page harus berupa angka',
            'page.min' => 'Page minimal adalah 1',
            'limit.integer' => 'Limit harus berupa angka',
            'limit.min' => 'Limit minimal adalah 1',
            'limit.max' => 'Limit maksimal adalah 100',
            'search.string' => 'Search harus berupa teks',
            'search.max' => 'Search maksimal 255 karakter',
            'sort_by.string' => 'Sort by harus berupa teks',
            'sort_by.max' => 'Sort by maksimal 50 karakter',
            'sort_order.in' => 'Sort order harus asc atau desc',
        ];

        // Get custom messages from child class if exists
        $customMessages = $this->getCustomMessages();

        return array_merge($baseMessages, $customMessages);
    }

    /**
     * Get custom validation messages for specific controllers
     * Override this method in child classes to add custom messages
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        $baseAttributes = [
            'page' => 'halaman',
            'limit' => 'batas data',
            'search' => 'pencarian',
            'sort_by' => 'urutkan berdasarkan',
            'sort_order' => 'urutan',
        ];

        // Get custom attributes from child class if exists
        $customAttributes = $this->getCustomAttributes();

        return array_merge($baseAttributes, $customAttributes);
    }

    /**
     * Get custom attributes for specific controllers
     * Override this method in child classes to add custom attributes
     *
     * @return array
     */
    protected function getCustomAttributes(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert empty strings to null for better validation
        $this->merge([
            'search' => $this->search ? trim($this->search) : null,
            'sort_by' => $this->sort_by ? trim($this->sort_by) : null,
            'sort_order' => $this->sort_order ? strtolower(trim($this->sort_order)) : null,
        ]);

        // Prepare custom data
        $this->prepareCustomData();
    }

    /**
     * Prepare custom data for validation
     * Override this method in child classes to add custom data preparation
     *
     * @return void
     */
    protected function prepareCustomData()
    {
        // Override in child classes if needed
    }
}
