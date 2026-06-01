<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteStaffRequest;
use App\Http\Requests\StockAdjustmentRequest;
use App\Http\Requests\StoreMenuItemRequest;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\OrderWorkflowService;
use App\Support\QrCodeSvg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('tenant.admin.index', [
            'stats' => [
                'today_orders' => Order::query()->whereDate('created_at', today())->count(),
                'revenue' => Order::query()->where('payment_status', 'paid')->sum('total_cents'),
                'active_orders' => Order::query()->whereNotIn('status', ['delivered', 'collected', 'cancelled'])->count(),
                'low_stock' => Ingredient::query()->whereColumn('current_stock', '<=', 'low_stock_threshold')->count(),
            ],
            'orders' => Order::query()->with(['items', 'driver', 'restaurantTable'])->latest()->limit(12)->get(),
            'drivers' => User::query()->where('role', 'driver')->where('status', 'active')->get(),
            'staff' => User::query()->whereIn('role', ['admin', 'kitchen', 'driver'])->latest()->get(),
            'categories' => Category::query()->with('menuItems')->orderBy('sort_order')->get(),
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
            'notifications' => Notification::query()->where('role', 'admin')->latest()->limit(8)->get(),
            'tables' => RestaurantTable::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'tableCount' => RestaurantTable::query()->where('is_active', true)->count(),
        ]);
    }

    public function configureTables(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'table_count' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        $targetCount = (int) $data['table_count'];

        DB::transaction(function () use ($targetCount): void {
            $tables = RestaurantTable::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $nextNumber = $tables
                ->map(fn (RestaurantTable $table) => (int) preg_replace('/\D+/', '', $table->code))
                ->max() + 1;

            while ($tables->count() < $targetCount) {
                $tables->push(RestaurantTable::query()->create([
                    'code' => sprintf('T%03d', $nextNumber),
                    'qr_token' => $this->uniqueTableToken(),
                    'sort_order' => $tables->count() + 1,
                    'is_active' => true,
                ]));

                $nextNumber++;
            }

            $tables = RestaurantTable::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($tables as $index => $table) {
                $table->update([
                    'sort_order' => $index + 1,
                    'is_active' => $index < $targetCount,
                ]);
            }
        });

        return back()->with('status', 'Restaurant tables updated.');
    }

    public function tableQr(RestaurantTable $restaurantTable): Response
    {
        return response(QrCodeSvg::make($restaurantTable->qrPayload()), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:400'],
        ]);

        Category::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'description' => $data['description'] ?? null,
            'sort_order' => Category::query()->max('sort_order') + 1,
        ]);

        return back()->with('status', 'Category created.');
    }

    public function storeMenuItem(StoreMenuItemRequest $request): RedirectResponse
    {
        $data = $request->validated();

        MenuItem::query()->create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_cents' => (int) round($data['price'] * 100),
            'prep_minutes' => $data['prep_minutes'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Menu item created.');
    }

    public function adjustStock(StockAdjustmentRequest $request): RedirectResponse
    {
        $ingredient = Ingredient::query()->findOrFail($request->integer('ingredient_id'));
        $quantity = (float) $request->input('quantity');

        $ingredient->increment('current_stock', $quantity);

        StockMovement::query()->create([
            'ingredient_id' => $ingredient->id,
            'type' => $quantity > 0 ? 'restock' : 'adjustment',
            'quantity' => $quantity,
            'note' => $request->input('note') ?: 'Manual stock adjustment',
        ]);

        return back()->with('status', 'Stock adjusted.');
    }

    public function inviteStaff(InviteStaffRequest $request): RedirectResponse
    {
        User::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role' => $request->input('role'),
            'status' => 'active',
            'available' => $request->input('role') === 'driver',
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('status', 'Staff account created.');
    }

    public function assign(Request $request, Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'driver_id' => ['required', Rule::exists('users', 'id')->where('tenant_id', tenant('id'))->where('role', 'driver')],
        ]);

        abort_unless($order->type === 'delivery' && in_array($order->status, ['ready', 'assigned'], true), 422);

        $driver = User::query()->where('role', 'driver')->findOrFail($data['driver_id']);
        $workflow->assignDriver($order, $driver);

        return back()->with('status', $order->public_code.' assigned to '.$driver->name.'.');
    }

    private function uniqueTableToken(): string
    {
        do {
            $token = Str::upper(Str::random(24));
        } while (RestaurantTable::query()->withoutGlobalScopes()->where('qr_token', $token)->exists());

        return $token;
    }
}
