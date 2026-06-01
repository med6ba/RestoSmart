<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformSeeder extends Seeder
{
    private const SUPER_EMAIL = 'super@restosmart.com';

    public function run(): void
    {
        $this->seedPlans();
        $this->seedSuper();
    }

    private function seedPlans(): void
    {
        Plan::query()->updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'monthly_price_cents' => 2900,
                'max_staff' => 5,
                'max_active_orders' => 30,
                'features' => ['Online menu', 'Takeaway checkout', 'Basic kitchen screen', '30-day trial'],
                'is_active' => true,
            ],
        );

        Plan::query()->updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'monthly_price_cents' => 7900,
                'max_staff' => 15,
                'max_active_orders' => 120,
                'features' => ['Delivery dispatch', 'Stock alerts', 'Driver mobile PWA', 'Advanced analytics'],
                'is_active' => true,
            ],
        );

        Plan::query()->updateOrCreate(
            ['slug' => 'business'],
            [
                'name' => 'Business',
                'monthly_price_cents' => 14900,
                'max_staff' => 50,
                'max_active_orders' => 500,
                'features' => ['Multi-branch operations', 'Priority support', 'Billing-ready subscriptions', 'SaaS analytics'],
                'is_active' => true,
            ],
        );
    }

    private function seedSuper(): void
    {
        User::query()->updateOrCreate(
            ['email' => self::SUPER_EMAIL],
            [
                'name' => 'RestoSmart Super',
                'tenant_id' => null,
                'role' => 'super',
                'status' => 'active',
                'password' => Hash::make('password'),
            ],
        );
    }
}
