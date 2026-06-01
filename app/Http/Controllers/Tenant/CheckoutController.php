<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\RestaurantTable;
use App\Services\CartService;
use App\Services\OrderWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(CartService $cart): View|RedirectResponse
    {
        if ($cart->count() === 0) {
            return redirect()->route('tenant.menu', tenant('id'))->with('status', __('Add at least one item before checkout.'));
        }

        return view('tenant.checkout', [
            'cartLines' => $cart->lines(),
            'subtotalCents' => $cart->subtotalCents(),
            'hasActiveTables' => RestaurantTable::query()->where('is_active', true)->exists(),
        ]);
    }

    public function store(CheckoutRequest $request, CartService $cart, OrderWorkflowService $orders): RedirectResponse
    {
        $order = $orders->placeOrder($request->user(), $cart->all(), $request->validated());

        $cart->clear();

        return redirect()->route('tenant.orders.show', [tenant('id'), $order])->with('status', __('Order placed. Your receipt PDF is ready.'));
    }

    public function validateTable(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:80'],
        ]);

        $table = RestaurantTable::query()
            ->where('qr_token', $data['token'])
            ->where('is_active', true)
            ->first();

        if (! $table) {
            return response()->json([
                'ok' => false,
                'message' => __('This table QR is not registered for this restaurant.'),
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'table' => $table->code,
            'message' => __('Table :table scanned.', ['table' => $table->code]),
        ]);
    }
}
