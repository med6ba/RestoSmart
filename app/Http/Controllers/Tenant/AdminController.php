<?php

namespace App\Http\Controllers\Tenant;

use App\Events\TenantRoleUpdated;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

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
            'tables' => RestaurantTable::query()->with('occupiedOrder')->where('is_active', true)->orderBy('sort_order')->get(),
            'tableCount' => RestaurantTable::query()->where('is_active', true)->count(),
        ]);
    }

    public function storeTable(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('restaurant_tables', 'code')->where('tenant_id', tenant('id')),
            ],
        ]);

        $code = Str::upper(trim($data['code'] ?? '')) ?: $this->nextTableCode();

        $table = RestaurantTable::query()->create([
            'code' => $code,
            'qr_token' => $this->uniqueTableToken(),
            'sort_order' => (int) RestaurantTable::query()->max('sort_order') + 1,
            'is_active' => true,
        ]);

        $this->broadcastTenantUpdate(['admin', 'client'], 'tables', 'table.added', __('Table QR :code added.', ['code' => $table->code]), [
            'table' => ['id' => $table->id, 'code' => $table->code],
        ]);

        return back()->with('status', __('Table QR added.'));
    }

    public function tableQr(Request $request, RestaurantTable $restaurantTable): Response
    {
        return response(QrCodeSvg::make($this->tableQrPayload($request, $restaurantTable)), 200, [
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

        $category = Category::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'description' => $data['description'] ?? null,
            'sort_order' => Category::query()->max('sort_order') + 1,
        ]);

        $this->broadcastTenantUpdate(['admin', 'client'], 'menu', 'category.created', __('Menu category :name created.', ['name' => $category->name]), [
            'category' => ['id' => $category->id, 'name' => $category->name],
        ]);

        return back()->with('status', __('Category created.'));
    }

    public function storeMenuItem(StoreMenuItemRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $imageUrl = null;
        if ($request->filled('cropped_image')) {
            $base64Image = $request->input('cropped_image');
            
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                $extension = strtolower($type[1]);
                $extension = $extension === 'jpeg' ? 'jpg' : $extension;

                $image = base64_decode($base64Image);
                $filename = 'dishes/' . tenant('id') . '/' . Str::uuid() . '.' . $extension;
                
                Storage::disk('public')->put($filename, $image);
                $imageUrl = '/storage/' . $filename;
            }
        } elseif ($request->hasFile('image')) {
            $path = $request->file('image')->store(
                'dishes/'.tenant('id'),
                'public'
            );
            $imageUrl = '/storage/'.$path;
        }

        $menuItem = MenuItem::query()->create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_cents' => (int) round($data['price'] * 100),
            'prep_minutes' => $data['prep_minutes'],
            'is_active' => $request->boolean('is_active'),
            'image_url' => $imageUrl,
        ]);

        $this->broadcastTenantUpdate(['admin', 'client', 'kitchen'], 'menu', 'dish.created', __('Dish :name created.', ['name' => $menuItem->name]), [
            'dish' => ['id' => $menuItem->id, 'name' => $menuItem->name],
        ]);

        return back()->with('status', __('Menu item created.'));
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
            'note' => $request->input('note') ?: __('Manual stock adjustment'),
        ]);

        $this->broadcastTenantUpdate(['admin', 'kitchen'], 'stock', 'stock.adjusted', __('Stock adjusted for :name.', ['name' => $ingredient->name]), [
            'ingredient' => ['id' => $ingredient->id, 'name' => $ingredient->name],
        ]);

        return back()->with('status', __('Stock adjusted.'));
    }

    public function inviteStaff(InviteStaffRequest $request): RedirectResponse
    {
        $staff = User::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role' => $request->input('role'),
            'status' => 'active',
            'available' => $request->input('role') === 'driver',
            'password' => Hash::make($request->input('password')),
        ]);

        $this->broadcastTenantUpdate(['admin'], 'staff', 'staff.created', __('Staff account :name created.', ['name' => $staff->name]), [
            'staff' => ['id' => $staff->id, 'name' => $staff->name, 'role' => $staff->role],
        ]);

        return back()->with('status', __('Staff account created.'));
    }

    public function assign(Request $request, Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'driver_id' => ['required', Rule::exists('users', 'id')->where('tenant_id', tenant('id'))->where('role', 'driver')],
        ]);

        abort_unless($order->type === 'delivery' && in_array($order->status, ['ready', 'assigned'], true), 422);

        $driver = User::query()->where('role', 'driver')->findOrFail($data['driver_id']);
        $workflow->assignDriver($order, $driver);

        return back()->with('status', __(':code assigned to :driver.', ['code' => $order->public_code, 'driver' => $driver->name]));
    }

    private function uniqueTableToken(): string
    {
        do {
            $token = Str::upper(Str::random(24));
        } while (RestaurantTable::query()->withoutGlobalScopes()->where('qr_token', $token)->exists());

        return $token;
    }

    private function nextTableCode(): string
    {
        $nextNumber = (int) RestaurantTable::query()
            ->pluck('code')
            ->map(fn (string $code): int => (int) preg_replace('/\D+/', '', $code))
            ->max() + 1;

        do {
            $code = sprintf('T%03d', $nextNumber++);
        } while (RestaurantTable::query()->where('code', $code)->exists());

        return $code;
    }

    private function tableQrPayload(Request $request, RestaurantTable $restaurantTable): string
    {
        $url = route('tenant.menu', tenant('id')).'?'.http_build_query([
            'table' => $restaurantTable->qr_token,
        ]);

        if ($this->shouldPreferHttps($request, $url)) {
            return Str::replaceFirst('http://', 'https://', $url);
        }

        return $url;
    }

    private function shouldPreferHttps(Request $request, string $url): bool
    {
        if (! Str::startsWith($url, 'http://')) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || ($host && Str::endsWith($host, ['.test', '.localhost']));

        return ! $isLocalHost
            && ($request->isSecure() || Str::startsWith((string) config('app.url'), 'https://') || app()->isProduction());
    }

    /**
     * @param  array<int, string>  $roles
     * @param  array<string, mixed>  $payload
     */
    private function broadcastTenantUpdate(array $roles, string $area, string $type, string $message, array $payload = []): void
    {
        try {
            TenantRoleUpdated::dispatch((string) tenant('id'), $roles, $area, $type, ['message' => $message] + $payload);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
