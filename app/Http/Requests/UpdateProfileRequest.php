<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Semua user login boleh memperbarui profilnya sendiri.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->user()->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            // Wajib diisi hanya saat mengganti password (dicek ke credential login).
            'current_password' => ['required_with:password', 'current_password'],
        ];
    }

    /**
     * Pesan validasi khusus (password saat ini).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required_with' => 'Isi password saat ini untuk mengganti password.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
        ];
    }
}
