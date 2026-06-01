<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        return view('landing', [
            'plans' => $this->activePlans(),
        ]);
    }

    private function activePlans(): Collection
    {
        try {
            $plans = Plan::query()
                ->where('is_active', true)
                ->orderBy('monthly_price_cents')
                ->get();
        } catch (Throwable) {
            $plans = collect();
        }

        if ($plans->isNotEmpty()) {
            return $plans;
        }

        return collect([
            (object) [
                'name' => 'Starter',
                'slug' => 'starter',
                'monthly_price_cents' => 2900,
                'max_staff' => 5,
                'max_active_orders' => 30,
                'features' => ['Online menu', 'Click & collect', 'Basic kitchen screen', '30-day trial'],
            ],
            (object) [
                'name' => 'Pro',
                'slug' => 'pro',
                'monthly_price_cents' => 7900,
                'max_staff' => 15,
                'max_active_orders' => 120,
                'features' => ['Delivery dispatch', 'Stock alerts', 'Driver mobile PWA', 'Advanced analytics'],
            ],
            (object) [
                'name' => 'Business',
                'slug' => 'business',
                'monthly_price_cents' => 14900,
                'max_staff' => 50,
                'max_active_orders' => 500,
                'features' => ['Multi-branch operations', 'Priority support', 'Billing-ready subscriptions', 'SaaS analytics'],
            ],
        ]);
    }
}
