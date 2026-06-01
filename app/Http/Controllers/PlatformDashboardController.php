<?php

namespace App\Http\Controllers;

use App\Models\BillingHistory;
use App\Models\Plan;
use App\Models\PlatformNotification;
use App\Models\RestaurantApplication;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            return view('platform.owner-dashboard', [
                'applications' => RestaurantApplication::query()
                    ->where('owner_email', $user->email)
                    ->latest()
                    ->get(),
                'tenants' => Tenant::query()
                    ->where(function ($query) use ($user) {
                        $query->where('owner_email', $user->email)
                            ->orWhere('id', $user->tenant_id);
                    })
                    ->with(['plan', 'subscription'])
                    ->get(),
                'plans' => Plan::query()->where('is_active', true)->get(),
            ]);
        }

        return view('platform.dashboard', [
            'stats' => [
                'restaurants' => Tenant::query()->count(),
                'active' => Tenant::query()->where('status', 'active')->count(),
                'trial' => Tenant::query()->where('status', 'trial')->count(),
                'pending' => RestaurantApplication::query()->where('status', 'pending')->count(),
                'mrr' => Subscription::query()
                    ->where('subscriptions.status', 'active')
                    ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                    ->sum('plans.monthly_price_cents'),
            ],
            'applications' => RestaurantApplication::query()->with('plan')->latest()->limit(10)->get(),
            'tenants' => Tenant::query()->with(['plan', 'subscription'])->latest()->get(),
            'plans' => Plan::query()->orderBy('monthly_price_cents')->get(),
            'billing' => BillingHistory::query()->with('plan')->latest()->limit(8)->get(),
            'notifications' => PlatformNotification::query()->latest()->limit(8)->get(),
        ]);
    }
}
