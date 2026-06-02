<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('tenant_id', tenant('id'))],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:600'],
            'price' => ['required', 'numeric', 'min:0.5', 'max:9999'],
            'prep_minutes' => ['required', 'integer', 'min:1', 'max:240'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable'],
            'cropped_image' => ['nullable', 'string'],
        ];
    }
}
