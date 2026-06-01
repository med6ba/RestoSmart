<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\RestaurantTable;
use App\Services\CartService;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(CartService $cart): View|RedirectResponse
    {
        if ($cart->count() === 0) {
            return redirect()->route('tenant.menu', tenant('id'))->with('status', 'Add at least one item before checkout.');
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

        return redirect()->route('tenant.orders.show', [tenant('id'), $order])->with('status', 'Order placed.');
    }
}
