<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan user terautentikasi dan memiliki role seller
        return $this->user() && $this->user()->role === 'seller';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            // 'file' agar bisa menerima upload, 'image' memastikan itu gambar
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|string', // Tetap sediakan jika ingin simpan link
            'status' => 'nullable|in:active,inactive',
        ];
    }

    // Tambahkan fungsi prepareForValidation untuk membersihkan string kosong dari Next.js
    protected function prepareForValidation()
    {
        if ($this->image_url === '') {
            $this->merge([
                'image_url' => null,
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk harus diisi',
            'price.required' => 'Harga harus diisi',
            'price.min' => 'Harga tidak boleh negatif',
            'stock.required' => 'Stok harus diisi',
            'stock.min' => 'Stok tidak boleh negatif',
            'image_url.url' => 'Format URL gambar tidak valid (Gunakan http:// atau https://)',
        ];
    }
}
