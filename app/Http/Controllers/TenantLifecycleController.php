<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantLifecycleController extends Controller
{
    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:trial,active,expired,suspended'],
            'plan_id' => ['nullable', 'exists:plans,id'],
        ]);

        $tenant->update([
            'status' => $data['status'],
            'plan_id' => $data['plan_id'] ?? $tenant->plan_id,
            'current_period_ends_at' => in_array($data['status'], ['trial', 'active'], true)
                ? now()->addMonth()
                : $tenant->current_period_ends_at,
        ]);

        Subscription::query()->where('tenant_id', $tenant->id)->update([
            'status' => $tenant->status,
            'plan_id' => $tenant->plan_id,
            'current_period_ends_at' => $tenant->current_period_ends_at,
        ]);

        return back()->with('status', 'Tenant lifecycle updated.');
    }
}
