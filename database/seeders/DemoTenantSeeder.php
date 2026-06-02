<?php

namespace Database\Seeders;

use App\Models\BillingHistory;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\DeliveryMessage;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlatformNotification;
use App\Models\RestaurantApplication;
use App\Models\RestaurantTable;
use App\Models\StockMovement;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    private const RESTAURANTS = [
        [
            'id' => 'demo',
            'name' => 'Demo Restaurant',
            'owner_name' => 'Demo Admin',
            'owner_email' => 'admin@demo.com',
            'phone' => '+1 555 0200',
            'address' => '12 Demo Street',
            'city' => 'Demo City',
            'plan' => 'starter',
            'status' => 'active',
            'order_prefix' => 'RS-DEMO',
            'client_address' => '44 Client Avenue',
            'office_address' => '91 Office Park',
            'restaurant_location' => [40.730610, -73.935242],
            'client_location' => [40.743120, -73.922420],
            'office_location' => [40.720360, -73.996310],
            'users' => [
                'admin' => ['name' => 'Demo Admin', 'email' => 'admin@demo.com', 'phone' => '+1 555 0201'],
                'kitchen' => ['name' => 'Demo Kitchen', 'email' => 'kitchen@demo.com', 'phone' => '+1 555 0202'],
                'driver' => ['name' => 'Demo Driver', 'email' => 'driver@demo.com', 'phone' => '+1 555 0203'],
                'client' => ['name' => 'Demo Client', 'email' => 'client@demo.com', 'phone' => '+1 555 0204'],
            ],
        ],
        [
            'id' => 'medina',
            'name' => 'Medina Bistro',
            'owner_name' => 'Salma El Idrissi',
            'owner_email' => 'admin@medina.test',
            'phone' => '+212 600 100 010',
            'address' => '18 Rue Riad Zitoun, Marrakech',
            'city' => 'Marrakech',
            'plan' => 'pro',
            'status' => 'active',
            'order_prefix' => 'RS-MED',
            'client_address' => '7 Avenue Mohammed V',
            'office_address' => '22 Gueliz Business Center',
            'restaurant_location' => [31.625825, -7.989160],
            'client_location' => [31.632410, -8.008450],
            'office_location' => [31.636720, -8.013160],
            'users' => [
                'admin' => ['name' => 'Salma El Idrissi', 'email' => 'admin@medina.test', 'phone' => '+212 600 100 011'],
                'kitchen' => ['name' => 'Youssef Kitchen', 'email' => 'kitchen@medina.test', 'phone' => '+212 600 100 012'],
                'driver' => ['name' => 'Hamza Delivery', 'email' => 'driver@medina.test', 'phone' => '+212 600 100 013'],
                'client' => ['name' => 'Nadia Client', 'email' => 'client@medina.test', 'phone' => '+212 600 100 014'],
            ],
        ],
        [
            'id' => 'atlas',
            'name' => 'Atlas Kitchen',
            'owner_name' => 'Amine Berrada',
            'owner_email' => 'admin@atlas.test',
            'phone' => '+212 600 200 020',
            'address' => '4 Boulevard Zerktouni, Casablanca',
            'city' => 'Casablanca',
            'plan' => 'business',
            'status' => 'trial',
            'order_prefix' => 'RS-ATL',
            'client_address' => '35 Maarif Residence',
            'office_address' => '10 Casa Finance City',
            'restaurant_location' => [33.589886, -7.603869],
            'client_location' => [33.581020, -7.638260],
            'office_location' => [33.546900, -7.666020],
            'users' => [
                'admin' => ['name' => 'Amine Berrada', 'email' => 'admin@atlas.test', 'phone' => '+212 600 200 021'],
                'kitchen' => ['name' => 'Meryem Kitchen', 'email' => 'kitchen@atlas.test', 'phone' => '+212 600 200 022'],
                'driver' => ['name' => 'Omar Delivery', 'email' => 'driver@atlas.test', 'phone' => '+212 600 200 023'],
                'client' => ['name' => 'Karim Client', 'email' => 'client@atlas.test', 'phone' => '+212 600 200 024'],
            ],
        ],
        [
            'id' => 'ocean',
            'name' => 'Ocean Grill',
            'owner_name' => 'Imane Lahbabi',
            'owner_email' => 'admin@ocean.test',
            'phone' => '+212 600 300 030',
            'address' => '2 Corniche Road, Agadir',
            'city' => 'Agadir',
            'plan' => 'pro',
            'status' => 'suspended',
            'order_prefix' => 'RS-OCN',
            'client_address' => '16 Marina Residence',
            'office_address' => '5 Agadir Bay Offices',
            'restaurant_location' => [30.427755, -9.598107],
            'client_location' => [30.421890, -9.617260],
            'office_location' => [30.397150, -9.586890],
            'users' => [
                'admin' => ['name' => 'Imane Lahbabi', 'email' => 'admin@ocean.test', 'phone' => '+212 600 300 031'],
                'kitchen' => ['name' => 'Rachid Kitchen', 'email' => 'kitchen@ocean.test', 'phone' => '+212 600 300 032'],
                'driver' => ['name' => 'Said Delivery', 'email' => 'driver@ocean.test', 'phone' => '+212 600 300 033'],
                'client' => ['name' => 'Leila Client', 'email' => 'client@ocean.test', 'phone' => '+212 600 300 034'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::RESTAURANTS as $restaurant) {
            $plan = Plan::query()->where('slug', $restaurant['plan'])->firstOrFail();
            $tenant = $this->seedTenant($plan, $restaurant);

            $this->seedPlatformRecords($tenant, $plan, $restaurant);

            $tenant->run(function () use ($restaurant) {
                $users = $this->seedUsers($restaurant);
                $items = $this->seedMenu();
                $tables = $this->seedTables($restaurant);
                $this->seedAddresses($users, $restaurant);
                $this->seedOrders($users, $items, $tables, $restaurant);
                $this->seedNotifications($users, $restaurant);
            });
        }
    }

    /**
     * @param  array<string, mixed>  $restaurant
     */
    private function seedTenant(Plan $plan, array $restaurant): Tenant
    {
        return Tenant::query()->updateOrCreate(
            ['id' => $restaurant['id']],
            [
                'name' => $restaurant['name'],
                'slug' => $restaurant['id'],
                'owner_email' => $restaurant['owner_email'],
                'phone' => $restaurant['phone'],
                'address' => $restaurant['address'],
                'status' => $restaurant['status'],
                'trial_ends_at' => $restaurant['status'] === 'trial' ? now()->addDays(30) : null,
                'current_period_ends_at' => in_array($restaurant['status'], ['trial', 'active'], true) ? now()->addDays(30) : null,
                'plan_id' => $plan->id,
                'data' => [
                    'local_url' => '/'.$restaurant['id'],
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $restaurant
     */
    private function seedPlatformRecords(Tenant $tenant, Plan $plan, array $restaurant): void
    {
        RestaurantApplication::query()->updateOrCreate(
            ['desired_slug' => $restaurant['id']],
            [
                'restaurant_name' => $restaurant['name'],
                'owner_name' => $restaurant['owner_name'],
                'owner_email' => $restaurant['owner_email'],
                'phone' => $restaurant['phone'],
                'address' => $restaurant['address'],
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
                'status' => $tenant->status,
                'trial_started_at' => $tenant->status === 'trial' ? now() : null,
                'trial_ends_at' => $tenant->trial_ends_at,
                'current_period_started_at' => in_array($tenant->status, ['trial', 'active'], true) ? now() : null,
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

        BillingHistory::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'status' => 'paid'],
            [
                'plan_id' => $plan->id,
                'amount_cents' => $plan->monthly_price_cents,
                'issued_at' => now()->subDays(45)->startOfDay(),
                'paid_at' => now()->subDays(44)->setTime(10, 15),
            ],
        );

        BillingHistory::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'status' => 'pending'],
            [
                'plan_id' => $plan->id,
                'amount_cents' => $plan->monthly_price_cents,
                'issued_at' => now()->addDays(15)->startOfDay(),
                'paid_at' => null,
            ],
        );

        PlatformNotification::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'type' => 'tenant_approved'],
            [
                'title' => 'Restaurant approved',
                'body' => $restaurant['name'].' is ready at /'.$restaurant['id'],
            ],
        );

        PlatformNotification::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'type' => 'subscription_status'],
            [
                'title' => 'Subscription seeded',
                'body' => $restaurant['name'].' has demo billing history and trial metadata.',
                'read_at' => $restaurant['id'] === 'demo' ? null : now()->subDays(1),
            ],
        );

        if ($restaurant['id'] === 'demo') {
            $this->seedApplicationQueue($plan);
        }
    }

    private function seedApplicationQueue(Plan $plan): void
    {
        RestaurantApplication::query()->updateOrCreate(
            ['desired_slug' => 'fresh-corner'],
            [
                'restaurant_name' => 'Fresh Corner',
                'owner_name' => 'Mona Alvarez',
                'owner_email' => 'owner@fresh-corner.test',
                'phone' => '+1 555 0401',
                'address' => '80 Market Street',
                'plan_id' => $plan->id,
                'status' => 'pending',
                'tenant_id' => null,
                'decision_note' => null,
                'decided_at' => null,
            ],
        );

        RestaurantApplication::query()->updateOrCreate(
            ['desired_slug' => 'closed-pop-up'],
            [
                'restaurant_name' => 'Closed Pop Up',
                'owner_name' => 'Jon Reed',
                'owner_email' => 'owner@closed-pop-up.test',
                'phone' => '+1 555 0402',
                'address' => '21 Temporary Lane',
                'plan_id' => $plan->id,
                'status' => 'rejected',
                'tenant_id' => null,
                'decision_note' => 'Missing legal restaurant details in demo data.',
                'decided_at' => now()->subDays(3),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $restaurant
     * @return array<string, User>
     */
    private function seedUsers(array $restaurant): array
    {
        $users = $restaurant['users'];

        return [
            'admin' => $this->user($users['admin']['email'], [
                'name' => $users['admin']['name'],
                'phone' => $users['admin']['phone'],
                'role' => 'admin',
                'available' => false,
                'default_address' => $restaurant['address'],
            ]),
            'kitchen' => $this->user($users['kitchen']['email'], [
                'name' => $users['kitchen']['name'],
                'phone' => $users['kitchen']['phone'],
                'role' => 'kitchen',
                'available' => false,
            ]),
            'driver' => $this->user($users['driver']['email'], [
                'name' => $users['driver']['name'],
                'phone' => $users['driver']['phone'],
                'role' => 'driver',
                'available' => true,
            ]),
            'client' => $this->user($users['client']['email'], [
                'name' => $users['client']['name'],
                'phone' => $users['client']['phone'],
                'role' => 'client',
                'available' => false,
                'default_address' => $restaurant['client_address'],
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
                ['category_id' => $categories['bowls']->id, 'description' => 'Grilled chicken, rice, herbs, and mild harissa.', 'price_cents' => 1290, 'prep_minutes' => 14, 'is_active' => true, 'stock_tracked' => true, 'image_url' => '/images/dishes/harissa-chicken-bowl.png'],
            ),
            'veggie_bowl' => MenuItem::query()->updateOrCreate(
                ['name' => 'Market Veggie Bowl'],
                ['category_id' => $categories['bowls']->id, 'description' => 'Roasted vegetables with rice and tahini sauce.', 'price_cents' => 1090, 'prep_minutes' => 11, 'is_active' => true, 'stock_tracked' => true, 'image_url' => '/images/dishes/market-veggie-bowl.png'],
            ),
            'kofta_wrap' => MenuItem::query()->updateOrCreate(
                ['name' => 'Lamb Kofta Wrap'],
                ['category_id' => $categories['grill']->id, 'description' => 'Kofta, pickles, herbs, yogurt, and flatbread.', 'price_cents' => 1180, 'prep_minutes' => 12, 'is_active' => true, 'stock_tracked' => true, 'image_url' => '/images/dishes/lamb-kofta-wrap.png'],
            ),
            'mint_tea' => MenuItem::query()->updateOrCreate(
                ['name' => 'Iced Mint Tea'],
                ['category_id' => $categories['drinks']->id, 'description' => 'Fresh mint tea over ice.', 'price_cents' => 350, 'prep_minutes' => 2, 'is_active' => true, 'stock_tracked' => true, 'image_url' => '/images/dishes/iced-mint-tea.png'],
            ),
            'date_cake' => MenuItem::query()->updateOrCreate(
                ['name' => 'Date Orange Cake'],
                ['category_id' => $categories['drinks']->id, 'description' => 'Date cake with orange syrup.', 'price_cents' => 520, 'prep_minutes' => 4, 'is_active' => true, 'stock_tracked' => true, 'image_url' => '/images/dishes/date-orange-cake.png'],
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
     * @param  array<string, mixed>  $restaurant
     */
    private function seedAddresses(array $users, array $restaurant): void
    {
        CustomerAddress::query()->updateOrCreate(
            ['user_id' => $users['client']->id, 'label' => 'Home'],
            [
                'address' => $restaurant['client_address'],
                'city' => $restaurant['city'],
                'phone' => $users['client']->phone,
                'instructions' => 'Ring the bell.',
            ],
        );

        CustomerAddress::query()->updateOrCreate(
            ['user_id' => $users['client']->id, 'label' => 'Office'],
            [
                'address' => $restaurant['office_address'],
                'city' => $restaurant['city'],
                'phone' => $users['client']->phone,
                'instructions' => 'Leave at reception.',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $restaurant
     * @return array<string, RestaurantTable>
     */
    private function seedTables(array $restaurant): array
    {
        $tables = [];

        for ($i = 1; $i <= 12; $i++) {
            $code = sprintf('T%03d', $i);

            $tables[$code] = RestaurantTable::query()->updateOrCreate(
                ['code' => $code],
                [
                    'qr_token' => strtoupper($restaurant['id']).'-TABLE-'.$code,
                    'sort_order' => $i,
                    'is_active' => true,
                    'is_occupied' => false,
                    'occupied_order_id' => null,
                    'occupied_at' => null,
                ],
            );
        }

        return $tables;
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, MenuItem>  $items
     * @param  array<string, RestaurantTable>  $tables
     * @param  array<string, mixed>  $restaurant
     */
    private function seedOrders(array $users, array $items, array $tables, array $restaurant): void
    {
        $prefix = $restaurant['order_prefix'];

        $this->order($prefix.'-1000', $users['client'], null, [
            [$items['veggie_bowl'], 1, null],
            [$items['mint_tea'], 1, null],
        ], [
            'type' => 'local',
            'status' => 'received',
            'payment_status' => 'pending',
            'restaurant_table_id' => $tables['T001']->id,
            'delivery_address' => null,
            'kitchen_notes' => 'Table QR scanned.',
            'placed_at' => now()->subMinutes(7),
        ], $restaurant);

        $this->order($prefix.'-1001', $users['client'], null, [
            [$items['chicken_bowl'], 1, null],
            [$items['mint_tea'], 1, null],
        ], [
            'type' => 'delivery',
            'status' => 'received',
            'payment_status' => 'pending',
            'delivery_address' => $restaurant['client_address'],
            'kitchen_notes' => 'Sauce on the side.',
            'placed_at' => now()->subMinutes(18),
        ], $restaurant);

        $this->order($prefix.'-1002', $users['client'], null, [
            [$items['kofta_wrap'], 2, 'Extra pickles'],
        ], [
            'type' => 'takeaway',
            'status' => 'preparing',
            'payment_status' => 'pending',
            'delivery_address' => null,
            'kitchen_notes' => 'Customer arrives in 15 minutes.',
            'placed_at' => now()->subMinutes(25),
        ], $restaurant);

        $this->order($prefix.'-1003', $users['client'], null, [
            [$items['veggie_bowl'], 1, 'No tahini'],
            [$items['mint_tea'], 2, null],
        ], [
            'type' => 'delivery',
            'status' => 'ready',
            'payment_status' => 'pending',
            'delivery_address' => $restaurant['office_address'],
            'kitchen_notes' => null,
            'placed_at' => now()->subMinutes(32),
            'ready_at' => now()->subMinutes(4),
        ], $restaurant);

        $this->order($prefix.'-1004', $users['client'], $users['driver'], [
            [$items['date_cake'], 2, null],
            [$items['mint_tea'], 1, null],
        ], [
            'type' => 'delivery',
            'status' => 'out_for_delivery',
            'payment_status' => 'pending',
            'delivery_address' => $restaurant['client_address'],
            'kitchen_notes' => null,
            'placed_at' => now()->subMinutes(58),
            'ready_at' => now()->subMinutes(22),
        ], $restaurant);

        $this->order($prefix.'-1005', $users['client'], $users['driver'], [
            [$items['chicken_bowl'], 1, null],
            [$items['date_cake'], 1, null],
        ], [
            'type' => 'delivery',
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivery_address' => $restaurant['client_address'],
            'kitchen_notes' => null,
            'placed_at' => now()->subHours(2),
            'ready_at' => now()->subMinutes(95),
            'delivered_at' => now()->subMinutes(74),
        ], $restaurant);

        $this->order($prefix.'-1006', $users['client'], null, [
            [$items['kofta_wrap'], 1, null],
            [$items['mint_tea'], 1, null],
        ], [
            'type' => 'local',
            'status' => 'collected',
            'payment_status' => 'paid',
            'restaurant_table_id' => $tables['T002']->id,
            'delivery_address' => null,
            'kitchen_notes' => 'Served at table.',
            'placed_at' => now()->subHours(3),
            'ready_at' => now()->subMinutes(160),
            'collected_at' => now()->subMinutes(145),
        ], $restaurant);

        $this->seedDeliveryMessages($users, $restaurant);
    }

    /**
     * @param  array<int, array{0: MenuItem, 1: int, 2: string|null}>  $items
     * @param  array<string, mixed>  $payload
     */
    private function order(string $code, User $customer, ?User $driver, array $items, array $payload, array $restaurant): Order
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
                'restaurant_table_id' => $payload['restaurant_table_id'] ?? null,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'delivery_address' => $payload['delivery_address'],
                'type' => $payload['type'] === 'click_collect' ? 'takeaway' : $payload['type'],
                'status' => $payload['status'],
                'payment_status' => $payload['payment_status'],
                'subtotal_cents' => $subtotal,
                'delivery_fee_cents' => $deliveryFee,
                'total_cents' => $subtotal + $deliveryFee,
                'kitchen_notes' => $payload['kitchen_notes'],
                'placed_at' => $payload['placed_at'],
                'ready_at' => $payload['ready_at'] ?? null,
                'delivered_at' => $payload['delivered_at'] ?? null,
                'collected_at' => $payload['collected_at'] ?? null,
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
                    'route_summary' => 'Restaurant -> '.$payload['delivery_address'],
                    'assigned_at' => in_array($payload['status'], ['assigned', 'out_for_delivery', 'delivered'], true) ? now()->subMinutes(20) : null,
                    'picked_up_at' => in_array($payload['status'], ['out_for_delivery', 'delivered'], true) ? now()->subMinutes(12) : null,
                    'delivered_at' => $payload['status'] === 'delivered' ? $payload['delivered_at'] ?? now()->subMinutes(2) : null,
                ] + $this->deliveryRouteAttributes($payload, $restaurant),
            );
        } else {
            $order->delivery()->delete();
        }

        if (($payload['type'] ?? null) === 'local' && ($payload['restaurant_table_id'] ?? null)) {
            $isOccupied = ! in_array($payload['status'], ['collected', 'cancelled'], true);

            RestaurantTable::query()
                ->whereKey($payload['restaurant_table_id'])
                ->update([
                    'is_occupied' => $isOccupied,
                    'occupied_order_id' => $isOccupied ? $order->id : null,
                    'occupied_at' => $isOccupied ? $order->placed_at : null,
                ]);
        }

        return $order->load(['items', 'delivery']);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $restaurant
     * @return array<string, mixed>
     */
    private function deliveryRouteAttributes(array $payload, array $restaurant): array
    {
        $destination = $payload['delivery_address'] === $restaurant['office_address']
            ? $restaurant['office_location']
            : $restaurant['client_location'];

        $driver = match ($payload['status']) {
            'out_for_delivery' => [
                round(($restaurant['restaurant_location'][0] + $destination[0]) / 2, 7),
                round(($restaurant['restaurant_location'][1] + $destination[1]) / 2, 7),
            ],
            'delivered' => $destination,
            default => [null, null],
        };

        return [
            'restaurant_latitude' => $restaurant['restaurant_location'][0],
            'restaurant_longitude' => $restaurant['restaurant_location'][1],
            'destination_latitude' => $destination[0],
            'destination_longitude' => $destination[1],
            'driver_latitude' => $driver[0],
            'driver_longitude' => $driver[1],
            'last_location_at' => $driver[0] ? now()->subMinutes(6) : null,
        ];
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, mixed>  $restaurant
     */
    private function seedDeliveryMessages(array $users, array $restaurant): void
    {
        $prefix = $restaurant['order_prefix'];

        $this->deliveryMessage($prefix.'-1004', $users['client'], $users['driver'], 'Hi, I am near the door.', now()->subMinutes(18), true);
        $this->deliveryMessage($prefix.'-1004', $users['driver'], $users['client'], 'Perfect, I am 5 minutes away.', now()->subMinutes(16), false);
        $this->deliveryMessage($prefix.'-1004', $users['client'], $users['driver'], 'Gate code is 2408.', now()->subMinutes(11), false);

        $this->deliveryMessage($prefix.'-1005', $users['driver'], $users['client'], 'Delivered at the reception desk.', now()->subMinutes(76), true);
        $this->deliveryMessage($prefix.'-1005', $users['client'], $users['driver'], 'Received, thank you.', now()->subMinutes(74), true);
    }

    private function deliveryMessage(string $orderCode, User $sender, User $receiver, string $message, Carbon $sentAt, bool $isRead): void
    {
        $order = Order::query()->where('public_code', $orderCode)->with('delivery')->first();

        if (! $order?->delivery) {
            return;
        }

        $deliveryMessage = DeliveryMessage::query()->updateOrCreate(
            [
                'order_id' => $order->id,
                'sender_id' => $sender->id,
                'message' => $message,
            ],
            [
                'delivery_id' => $order->delivery->id,
                'receiver_id' => $receiver->id,
                'is_read' => $isRead,
                'read_at' => $isRead ? $sentAt->copy()->addMinute() : null,
            ],
        );

        $deliveryMessage->forceFill([
            'created_at' => $sentAt,
            'updated_at' => $sentAt,
        ])->save();
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, mixed>  $restaurant
     */
    private function seedNotifications(array $users, array $restaurant): void
    {
        $prefix = $restaurant['order_prefix'];

        Notification::query()
            ->where('role', 'admin')
            ->where('type', 'low_stock')
            ->delete();

        Notification::query()->updateOrCreate(
            ['role' => 'kitchen', 'type' => 'low_stock', 'title' => 'Low stock: Market vegetables'],
            ['body' => 'Current stock is below the configured threshold.'],
        );

        Notification::query()->updateOrCreate(
            ['role' => 'kitchen', 'type' => 'new_order', 'title' => 'New order '.$prefix.'-1001'],
            ['body' => 'A delivery order is waiting for preparation.'],
        );

        Notification::query()->updateOrCreate(
            ['role' => 'driver', 'type' => 'delivery_ready', 'title' => 'Delivery ready'],
            ['body' => $prefix.'-1003 is ready for dispatch.'],
        );

        Notification::query()->updateOrCreate(
            ['user_id' => $users['client']->id, 'type' => 'order_update', 'title' => 'Order ready'],
            ['body' => $prefix.'-1003 is ready.'],
        );
    }
}
