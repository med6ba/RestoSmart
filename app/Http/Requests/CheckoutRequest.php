<?php

namespace App\Http\Requests;

use App\Models\RestaurantTable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    public function messages(): array
    {
        return [
            'restaurant_table_token.exists' => __('This table QR is not registered for this restaurant.'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('type') !== 'local' || $validator->errors()->has('restaurant_table_token')) {
                return;
            }

            $table = RestaurantTable::query()
                ->where('qr_token', $this->input('restaurant_table_token'))
                ->where('is_active', true)
                ->first();

            if ($table?->is_occupied) {
                $validator->errors()->add('restaurant_table_token', __('This table is already occupied. Please choose another table.'));
            }
        });
    }
}
