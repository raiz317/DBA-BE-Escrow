<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:bank_transfer,virtual_account,ewallet',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Payment method harus diisi',
            'payment_method.in' => 'Payment method tidak valid',
        ];
    }
}
