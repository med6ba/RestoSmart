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
            'type' => ['required', Rule::in(['local', 'takeaway', 'delivery'])],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'delivery_address' => ['required_if:type,delivery', 'nullable', 'string', 'max:700'],
            'restaurant_table_token' => [
                'required_if:type,local',
                'nullable',
                'string',
                'max:80',
                Rule::exists('restaurant_tables', 'qr_token')
                    ->where('tenant_id', tenant('id'))
                    ->where('is_active', true),
            ],
            'kitchen_notes' => ['nullable', 'string', 'max:700'],
        ];
    }
}
