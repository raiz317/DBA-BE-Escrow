<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'buyer';
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID harus diisi',
            'product_id.exists' => 'Produk tidak ditemukan',
            'quantity.required' => 'Quantity harus diisi',
            'quantity.min' => 'Quantity minimal 1',
        ];
    }
}
