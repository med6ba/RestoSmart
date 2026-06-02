<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['kitchen']) ?? false;
    }

    public function rules(): array
    {
        return [
            'ingredient_id' => ['required', Rule::exists('ingredients', 'id')->where('tenant_id', tenant('id'))],
            'quantity' => ['required', 'numeric', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:300'],
        ];
    }
}
