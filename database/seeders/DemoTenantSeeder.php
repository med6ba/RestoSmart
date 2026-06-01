<?php

namespace Database\Seeders;

use App\Models\BillingHistory;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlatformNotification;
use App\Models\RestaurantApplication;
use App\Models\StockMovement;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    private const TENANT_ID = 'demo';

    public function run(): void
    {
        $plan = Plan::query()->where('slug', 'starter')->firstOrFail();
        $tenant = $this->seedTenant($plan);

        $this->seedPlatformRecords($tenant, $plan);

        $tenant->run(function () {
            $users = $this->seedUsers();
            $items = $this->seedMenu();
            $this->seedAddresses($users);
            $this->seedOrders($users, $items);
            $this->seedNotifications($users);
        });
    }

    private function seedTenant(Plan $plan): Tenant
    {
        return Tenant::query()->updateOrCreate(
            ['id' => self::TENANT_ID],
            [
                'name' => 'Demo Restaurant',
                'slug' => self::TENANT_ID,
                'owner_email' => 'admin@demo.com',
                'phone' => '+1 555 0200',
                'address' => '12 Demo Street',
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
                'current_period_ends_at' => now()->addDays(30),
                'plan_id' => $plan->id,
                'data' => [
                    'local_url' => '/'.self::TENANT_ID,
                ],
            ],
        );
    }

    private function seedPlatformRecords(Tenant $tenant, Plan $plan): void
    {
        RestaurantApplication::query()->updateOrCreate(
            ['desired_slug' => self::TENANT_ID],
            [
                'restaurant_name' => 'Demo Restaurant',
                'owner_name' => 'Demo Admin',
                'owner_email' => 'admin@demo.com',
                'phone' => '+1 555 0200',
                'address' => '12 Demo Street',
                'plan_id' => $plan->id,
                'status' => 'approved',
                'tenant_id' => $tenant->id,
                'decision_note' => 'Seeded demo tenant.',
                'decided_at' => now(),
            ],
        );

        Subscription::query()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'plan_id' => $plan->id,
                'status' => 'trial',
                'trial_started_at' => now(),
                'trial_ends_at' => $tenant->trial_ends_at,
                'current_period_started_at' => now(),
                'current_period_ends_at' => $tenant->current_period_ends_at,
            ],
        );

        BillingHistory::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'status' => 'trial_credit'],
            [
                'plan_id' => $plan->id,
                'amount_cents' => 0,
                'issued_at' => now(),
            ],
        );

        PlatformNotification::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'type' => 'tenant_approved'],
            [
                'title' => 'Restaurant approved',
                'body' => 'Demo Restaurant is ready at /demo',
            ],
        );
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        return [
            'admin' => $this->user('admin@demo.com', [
                'name' => 'Demo Admin',
                'phone' => '+1 555 0201',
                'role' => 'admin',
                'available' => false,
                'default_address' => '12 Demo Street',
            ]),
            'kitchen' => $this->user('kitchen@demo.com', [
                'name' => 'Demo Kitchen',
                'phone' => '+1 555 0202',
                'role' => 'kitchen',
                'available' => false,
            ]),
            'driver' => $this->user('driver@demo.com', [
                'name' => 'Demo Driver',
                'phone' => '+1 555 0203',
                'role' => 'driver',
                'available' => true,
            ]),
            'client' => $this->user('client@demo.com', [
                'name' => 'Demo Client',
                'phone' => '+1 555 0204',
                'role' => 'client',
                'available' => false,
                'default_address' => '44 Client Avenue',
            ]),
        ];
    }

    private function user(string $email, array $attributes): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            $attributes + [
                'status' => 'active',
                'password' => Hash::make('password'),
            ],
        );
    }

    /**
     * @return array<string, MenuItem>
     */
    private function seedMenu(): array
    {
        $categories = [
            'bowls' => Category::query()->updateOrCreate(
                ['slug' => 'signature-bowls'],
                ['name' => 'Signature Bowls', 'description' => 'Fast demo meals for lunch and delivery.', 'sort_order' => 1, 'is_active' => true],
            ),
            'grill' => Category::query()->updateOrCreate(
                ['slug' => 'grill-wraps'],
                ['name' => 'Grill & Wraps', 'description' => 'Prepared plates, wraps, and dispatch-friendly food.', 'sort_order' => 2, 'is_active' => true],
            ),
            'drinks' => Category::query()->updateOrCreate(
                ['slug' => 'drinks-desserts'],
                ['name' => 'Drinks & Desserts', 'description' => 'Simple add-ons for checkout demos.', 'sort_order' => 3, 'is_active' => true],
            ),
        ];

        $ingredients = $this->seedIngredients();

        $items = [
            'chicken_bowl' => MenuItem::query()->updateOrCreate(
                ['name' => 'Harissa Chicken Bowl'],
                ['category_id' => $categories['bowls']->id, 'description' => 'Grilled chicken, rice, herbs, and mild harissa.', 'price_cents' => 1290, 'prep_minutes' => 14, 'is_active' => true, 'stock_tracked' => true],
            ),
            'veggie_bowl' => MenuItem::query()->updateOrCreate(
                ['name' => 'Market Veggie Bowl'],
                ['category_id' => $categories['bowls']->id, 'description' => 'Roasted vegetables with rice and tahini sauce.', 'price_cents' => 1090, 'prep_minutes' => 11, 'is_active' => true, 'stock_tracked' => true],
            ),
            'kofta_wrap' => MenuItem::query()->updateOrCreate(
                ['name' => 'Lamb Kofta Wrap'],
                ['category_id' => $categories['grill']->id, 'description' => 'Kofta, pickles, herbs, yogurt, and flatbread.', 'price_cents' => 1180, 'prep_minutes' => 12, 'is_active' => true, 'stock_tracked' => true],
            ),
            'mint_tea' => MenuItem::query()->updateOrCreate(
                ['name' => 'Iced Mint Tea'],
                ['category_id' => $categories['drinks']->id, 'description' => 'Fresh mint tea over ice.', 'price_cents' => 350, 'prep_minutes' => 2, 'is_active' => true, 'stock_tracked' => true],
            ),
            'date_cake' => MenuItem::query()->updateOrCreate(
                ['name' => 'Date Orange Cake'],
                ['category_id' => $categories['drinks']->id, 'description' => 'Date cake with orange syrup.', 'price_cents' => 520, 'prep_minutes' => 4, 'is_active' => true, 'stock_tracked' => true],
            ),
        ];

        $items['chicken_bowl']->ingredients()->sync([
            $ingredients['rice']->id => ['quantity_required' => 0.25],
            $ingredients['chicken']->id => ['quantity_required' => 0.20],
            $ingredients['vegetables']->id => ['quantity_required' => 0.10],
            $ingredients['harissa']->id => ['quantity_required' => 0.03],
        ]);

        $items['veggie_bowl']->ingredients()->sync([
            $ingredients['rice']->id => ['quantity_required' => 0.25],
            $ingredients['vegetables']->id => ['quantity_required' => 0.25],
            $ingredients['chickpeas']->id => ['quantity_required' => 0.15],
        ]);

        $items['kofta_wrap']->ingredients()->sync([
            $ingredients['flatbread']->id => ['quantity_required' => 1],
            $ingredients['lamb']->id => ['quantity_required' => 0.18],
            $ingredients['vegetables']->id => ['quantity_required' => 0.08],
        ]);

        $items['mint_tea']->ingredients()->sync([
            $ingredients['mint_tea']->id => ['quantity_required' => 1],
        ]);

        $items['date_cake']->ingredients()->sync([
            $ingredients['dates']->id => ['quantity_required' => 0.08],
            $ingredients['orange']->id => ['quantity_required' => 0.05],
        ]);

        return $items;
    }

    /**
     * @return array<string, Ingredient>
     */
    private function seedIngredients(): array
    {
        $ingredients = [
            'rice' => ['Rice', 'kg', 18.50, 8],
            'chicken' => ['Chicken', 'kg', 7.25, 5],
            'vegetables' => ['Market vegetables', 'kg', 3.50, 6],
            'chickpeas' => ['Chickpeas', 'kg', 14, 5],
            'mint_tea' => ['Mint tea', 'servings', 64, 20],
            'flatbread' => ['Flatbread', 'pieces', 22, 10],
            'lamb' => ['Lamb kofta', 'kg', 6, 4],
            'harissa' => ['Harissa sauce', 'kg', 2, 2.5],
            'dates' => ['Dates', 'kg', 9, 4],
            'orange' => ['Orange syrup', 'liters', 12, 5],
        ];

        return collect($ingredients)
            ->mapWithKeys(function (array $ingredient, string $key) {
                [$name, $unit, $stock, $threshold] = $ingredient;

                $model = Ingredient::query()->updateOrCreate(
                    ['name' => $name],
                    ['unit' => $unit, 'current_stock' => $stock, 'low_stock_threshold' => $threshold],
                );

                StockMovement::query()->firstOrCreate(
                    ['ingredient_id' => $model->id, 'type' => 'restock', 'note' => 'Opening demo stock'],
                    ['quantity' => $stock],
                );

                return [$key => $model];
            })
            ->all();
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedAddresses(array $users): void
    {
        CustomerAddress::query()->updateOrCreate(
            ['user_id' => $users['client']->id, 'label' => 'Home'],
            [
                'address' => '44 Client Avenue',
                'city' => 'Demo City',
                'phone' => $users['client']->phone,
                'instructions' => 'Ring the bell.',
            ],
        );

        CustomerAddress::query()->updateOrCreate(
            ['user_id' => $users['client']->id, 'label' => 'Office'],
            [
                'address' => '91 Office Park',
                'city' => 'Demo City',
                'phone' => $users['client']->phone,
                'instructions' => 'Leave at reception.',
            ],
        );
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, MenuItem>  $items
     */
    private function seedOrders(array $users, array $items): void
    {
        $this->order('RS-DEMO-1001', $users['client'], null, [
            [$items['chicken_bowl'], 1, null],
            [$items['mint_tea'], 1, null],
        ], [
            'type' => 'delivery',
            'status' => 'received',
            'payment_status' => 'pending',
            'delivery_address' => '44 Client Avenue',
            'kitchen_notes' => 'Sauce on the side.',
            'placed_at' => now()->subMinutes(18),
        ]);

        $this->order('RS-DEMO-1002', $users['client'], null, [
            [$items['kofta_wrap'], 2, 'Extra pickles'],
        ], [
            'type' => 'click_collect',
            'status' => 'preparing',
            'payment_status' => 'pending',
            'delivery_address' => null,
            'kitchen_notes' => 'Customer arrives in 15 minutes.',
            'placed_at' => now()->subMinutes(25),
        ]);

        $this->order('RS-DEMO-1003', $users['client'], null, [
            [$items['veggie_bowl'], 1, 'No tahini'],
            [$items['mint_tea'], 2, null],
        ], [
            'type' => 'delivery',
            'status' => 'ready',
            'payment_status' => 'pending',
            'delivery_address' => '91 Office Park',
            'kitchen_notes' => null,
            'placed_at' => now()->subMinutes(32),
            'ready_at' => now()->subMinutes(4),
        ]);

        $this->order('RS-DEMO-1004', $users['client'], $users['driver'], [
            [$items['date_cake'], 2, null],
            [$items['mint_tea'], 1, null],
        ], [
            'type' => 'delivery',
            'status' => 'out_for_delivery',
            'payment_status' => 'pending',
            'delivery_address' => '44 Client Avenue',
            'kitchen_notes' => null,
            'placed_at' => now()->subMinutes(58),
            'ready_at' => now()->subMinutes(22),
        ]);
    }

    /**
     * @param  array<int, array{0: MenuItem, 1: int, 2: string|null}>  $items
     * @param  array<string, mixed>  $payload
     */
    private function order(string $code, User $customer, ?User $driver, array $items, array $payload): Order
    {
        $subtotal = collect($items)->sum(fn (array $line) => $line[0]->price_cents * $line[1]);
        $deliveryFee = $payload['type'] === 'delivery' ? 300 : 0;
        $address = $payload['type'] === 'delivery'
            ? CustomerAddress::query()->where('user_id', $customer->id)->where('address', $payload['delivery_address'])->first()
            : null;

        $order = Order::query()->updateOrCreate(
            ['public_code' => $code],
            [
                'user_id' => $customer->id,
                'driver_id' => $driver?->id,
                'customer_address_id' => $address?->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'delivery_address' => $payload['delivery_address'],
                'type' => $payload['type'],
                'status' => $payload['status'],
                'payment_status' => $payload['payment_status'],
                'subtotal_cents' => $subtotal,
                'delivery_fee_cents' => $deliveryFee,
                'total_cents' => $subtotal + $deliveryFee,
                'kitchen_notes' => $payload['kitchen_notes'],
                'placed_at' => $payload['placed_at'],
                'ready_at' => $payload['ready_at'] ?? null,
            ],
        );

        $order->items()->delete();
        StockMovement::query()->where('order_id', $order->id)->delete();

        foreach ($items as [$menuItem, $quantity, $notes]) {
            $order->items()->create([
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'quantity' => $quantity,
                'unit_price_cents' => $menuItem->price_cents,
                'total_price_cents' => $menuItem->price_cents * $quantity,
                'notes' => $notes,
            ]);

            foreach ($menuItem->ingredients as $ingredient) {
                $required = (float) $ingredient->pivot->quantity_required * $quantity;

                $ingredient->stockMovements()->create([
                    'order_id' => $order->id,
                    'type' => 'usage',
                    'quantity' => -1 * $required,
                    'note' => 'Consumed for '.$code,
                ]);
            }
        }

        if ($payload['type'] === 'delivery') {
            $deliveryStatus = match ($payload['status']) {
                'assigned' => 'assigned',
                'out_for_delivery' => 'picked_up',
                'delivered' => 'delivered',
                default => 'waiting',
            };

            $order->delivery()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'driver_id' => $driver?->id,
                    'status' => $deliveryStatus,
                    'route_summary' => 'Restaurant -> '.$payload['delivery_address'].' (simulated 14 min route)',
                    'assigned_at' => in_array($payload['status'], ['assigned', 'out_for_delivery', 'delivered'], true) ? now()->subMinutes(20) : null,
                    'picked_up_at' => in_array($payload['status'], ['out_for_delivery', 'delivered'], true) ? now()->subMinutes(12) : null,
                    'delivered_at' => $payload['status'] === 'delivered' ? $payload['delivered_at'] ?? now()->subMinutes(2) : null,
                ],
            );
        } else {
            $order->delivery()->delete();
        }

        return $order->load(['items', 'delivery']);
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedNotifications(array $users): void
    {
        Notification::query()->updateOrCreate(
            ['role' => 'admin', 'type' => 'low_stock', 'title' => 'Low stock: Market vegetables'],
            ['body' => 'Current stock is below the configured threshold.'],
        );

        Notification::query()->updateOrCreate(
            ['role' => 'kitchen', 'type' => 'new_order', 'title' => 'New order RS-DEMO-1001'],
            ['body' => 'A delivery order is waiting for preparation.'],
        );

        Notification::query()->updateOrCreate(
            ['role' => 'driver', 'type' => 'delivery_ready', 'title' => 'Delivery ready'],
            ['body' => 'RS-DEMO-1003 is ready for dispatch.'],
        );

        Notification::query()->updateOrCreate(
            ['user_id' => $users['client']->id, 'type' => 'order_update', 'title' => 'Order ready'],
            ['body' => 'RS-DEMO-1003 is ready.'],
        );
    }
}
