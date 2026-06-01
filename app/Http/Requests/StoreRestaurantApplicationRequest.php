<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRestaurantApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'desired_slug' => Str::slug($this->input('desired_slug') ?: $this->input('restaurant_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'restaurant_name' => ['required', 'string', 'max:120'],
            'desired_slug' => [
                'required',
                'alpha_dash',
                'max:80',
                Rule::notIn($this->reservedTenantSlugs()),
                Rule::unique('restaurant_applications', 'desired_slug'),
                Rule::unique('tenants', 'id'),
            ],
            'owner_name' => ['required', 'string', 'max:120'],
            'owner_email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('tenants', 'owner_email'),
                Rule::unique('restaurant_applications', 'owner_email')
                    ->where(fn ($query) => $query->whereIn('status', ['pending', 'approved'])),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:500'],
            'plan_id' => ['nullable', 'exists:plans,id'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function reservedTenantSlugs(): array
    {
        return [
            'applications',
            'build',
            'confirm-password',
            'dashboard',
            'email',
            'images',
            'locale',
            'login',
            'logout',
            'password',
            'plans',
            'profile',
            'register',
            'restaurant',
            'storage',
            'tenants',
            'up',
            'verify-email',
        ];
    }
}
