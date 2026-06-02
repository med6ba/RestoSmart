<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\DemoTenantSeeder;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoTenantSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_restaurants_with_full_test_people(): void
    {
        $this->seed(PlatformSeeder::class);
        $this->seed(DemoTenantSeeder::class);

        $expectedRestaurants = [
            'demo' => 'RS-DEMO',
            'medina' => 'RS-MED',
            'atlas' => 'RS-ATL',
            'ocean' => 'RS-OCN',
        ];

        $this->assertSame(
            collect(array_keys($expectedRestaurants))->sort()->values()->all(),
            Tenant::query()->pluck('id')->sort()->values()->all(),
        );
        $this->assertSame(4, User::query()->withoutGlobalScopes()->where('role', 'admin')->whereNotNull('tenant_id')->count());
        $this->assertSame(4, User::query()->withoutGlobalScopes()->where('role', 'kitchen')->count());
        $this->assertSame(4, User::query()->withoutGlobalScopes()->where('role', 'driver')->count());
        $this->assertSame(4, User::query()->withoutGlobalScopes()->where('role', 'client')->count());

        foreach ($expectedRestaurants as $tenantId => $orderPrefix) {
            Tenant::query()->findOrFail($tenantId)->run(function () use ($orderPrefix) {
                $this->assertSame(4, User::query()->whereIn('role', ['admin', 'kitchen', 'driver', 'client'])->count());
                $this->assertSame(5, MenuItem::query()->count());
                $this->assertSame(12, RestaurantTable::query()->count());
                $this->assertSame(5, Order::query()->count());
                $this->assertTrue(Order::query()->where('public_code', $orderPrefix.'-1000')->exists());
            });
        }
    }
}
