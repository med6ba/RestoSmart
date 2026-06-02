<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantApplicationRequest;
use App\Models\Plan;
use App\Models\RestaurantApplication;
use App\Services\PlatformProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantApplicationController extends Controller
{
    public function create(): View
    {
        return view('platform.apply', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('monthly_price_cents')->get(),
        ]);
    }

    public function store(StoreRestaurantApplicationRequest $request): RedirectResponse
    {
        RestaurantApplication::query()->create($request->validated());

        return redirect()
            ->route('login')
            ->with('status', __('Application received. The platform team can approve it from the super dashboard.'));
    }

    public function approve(Request $request, RestaurantApplication $application, PlatformProvisioningService $service): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['nullable', 'exists:plans,id'],
            'decision_note' => ['nullable', 'string', 'max:500'],
        ]);

        $tenant = $service->approve($application, $request->integer('plan_id') ?: null, $request->input('decision_note'));

        return back()->with('status', __('Tenant :tenant approved and provisioned.', ['tenant' => $tenant->id]));
    }

    public function reject(Request $request, RestaurantApplication $application, PlatformProvisioningService $service): RedirectResponse
    {
        $request->validate([
            'decision_note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->reject($application, $request->input('decision_note'));

        return back()->with('status', __('Application rejected.'));
    }
}
