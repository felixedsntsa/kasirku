<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:0', 'max:1000000'], // stok fisik hasil hitung
            'note' => ['nullable', 'string', 'max:255'],
            'old_stock' => ['nullable', 'integer'], // hanya untuk pesan sukses
        ];
    }
}
