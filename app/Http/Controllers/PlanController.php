<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'max_staff' => ['required', 'integer', 'min:1'],
            'max_active_orders' => ['required', 'integer', 'min:1'],
            'features' => ['nullable', 'string', 'max:1000'],
        ]);

        Plan::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'monthly_price_cents' => (int) round($data['monthly_price'] * 100),
            'max_staff' => $data['max_staff'],
            'max_active_orders' => $data['max_active_orders'],
            'features' => collect(explode("\n", $data['features'] ?? ''))->map(fn ($item) => trim($item))->filter()->values()->all(),
        ]);

        return back()->with('status', __('Plan created.'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'max_staff' => ['required', 'integer', 'min:1'],
            'max_active_orders' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plan->update([
            'monthly_price_cents' => (int) round($data['monthly_price'] * 100),
            'max_staff' => $data['max_staff'],
            'max_active_orders' => $data['max_active_orders'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', __('Plan updated.'));
    }
}
