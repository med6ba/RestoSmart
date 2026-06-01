<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['delivery', 'click_collect'])],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'delivery_address' => ['required_if:type,delivery', 'nullable', 'string', 'max:700'],
            'kitchen_notes' => ['nullable', 'string', 'max:700'],
        ];
    }
}
