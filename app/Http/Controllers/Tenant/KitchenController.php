<?php

namespace App\Http\Controllers\Tenant;

use App\Events\TenantRoleUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\StockMovement;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class KitchenController extends Controller
{
    public function index(): View
    {
        return view('tenant.kitchen.index', [
            'orders' => Order::query()
                ->with(['items', 'restaurantTable'])
                ->whereIn('status', ['received', 'preparing', 'ready'])
                ->orderByRaw("case status when 'received' then 1 when 'preparing' then 2 else 3 end")
                ->oldest()
                ->get(),
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
        ]);
    }

    public function preparing(Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        $workflow->markPreparing($order);

        return back()->with('status', __(':code is now in preparation.', ['code' => $order->public_code]));
    }

    public function ready(Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        $workflow->markReady($order);

        return back()->with('status', __(':code marked ready.', ['code' => $order->public_code]));
    }

    public function collected(Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        abort_unless(in_array($order->type, ['local', 'takeaway', 'click_collect'], true) && $order->status === 'ready', 422);

        $workflow->markCollected($order);

        return back()->with('status', __(':code marked collected.', ['code' => $order->public_code]));
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

        $this->broadcastTenantUpdate(['kitchen'], 'stock', 'stock.adjusted', __('Stock adjusted for :name.', ['name' => $ingredient->name]), [
            'ingredient' => ['id' => $ingredient->id, 'name' => $ingredient->name],
        ]);

        return back()->with('status', __('Stock adjusted.'));
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
