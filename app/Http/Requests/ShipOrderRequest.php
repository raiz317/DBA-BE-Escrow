<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tracking_number' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tracking_number.required' => 'Tracking number harus diisi',
        ];
    }
}
