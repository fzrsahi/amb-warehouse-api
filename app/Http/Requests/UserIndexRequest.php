<?php

namespace App\Http\Requests;

class UserIndexRequest extends PaginationRequest
{
    /**
     * Get custom validation rules for User
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return [
            'role_id' => 'nullable|integer|exists:roles,id',
            'company_id' => 'nullable|integer|exists:companies,id',
            'created_at_start' => 'nullable|date|before_or_equal:created_at_end',
            'created_at_end' => 'nullable|date|after_or_equal:created_at_start',
            'updated_at_start' => 'nullable|date|before_or_equal:updated_at_end',
            'updated_at_end' => 'nullable|date|after_or_equal:updated_at_start',
            'email' => 'nullable|string|max:255',
            'role_type' => 'nullable|string|in:warehouse,company,super-admin',
        ];
    }

    /**
     * Get custom validation messages for User
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return [
            'role_id.integer' => 'ID role harus berupa angka',
            'role_id.exists' => 'Role tidak ditemukan',
            'company_id.integer' => 'ID perusahaan harus berupa angka',
            'company_id.exists' => 'Perusahaan tidak ditemukan',
            'created_at_start.date' => 'Tanggal dibuat mulai harus berupa tanggal yang valid',
            'created_at_start.before_or_equal' => 'Tanggal dibuat mulai harus sebelum atau sama dengan tanggal akhir',
            'created_at_end.date' => 'Tanggal dibuat akhir harus berupa tanggal yang valid',
            'created_at_end.after_or_equal' => 'Tanggal dibuat akhir harus setelah atau sama dengan tanggal mulai',
            'updated_at_start.date' => 'Tanggal update mulai harus berupa tanggal yang valid',
            'updated_at_start.before_or_equal' => 'Tanggal update mulai harus sebelum atau sama dengan tanggal update akhir',
            'updated_at_end.date' => 'Tanggal update akhir harus berupa tanggal yang valid',
            'updated_at_end.after_or_equal' => 'Tanggal update akhir harus setelah atau sama dengan tanggal update mulai',
            'email.string' => 'Email harus berupa teks',
            'email.max' => 'Email maksimal 255 karakter',
            'role_type.in' => 'Tipe role harus warehouse, company, atau super-admin',
        ];
    }

    /**
     * Get custom attributes for User
     *
     * @return array
     */
    protected function getCustomAttributes(): array
    {
        return [
            'role_id' => 'role',
            'company_id' => 'perusahaan',
            'created_at_start' => 'tanggal dibuat mulai',
            'created_at_end' => 'tanggal dibuat akhir',
            'updated_at_start' => 'tanggal update mulai',
            'updated_at_end' => 'tanggal update akhir',
            'email' => 'email',
            'role_type' => 'tipe role',
        ];
    }
}
