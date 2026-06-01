<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request, MenuItem $menuItem, CartService $cart): RedirectResponse
    {
        $this->authorizeClientOrdering($request);

        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:240'],
        ]);

        abort_unless($menuItem->is_active, 404);

        $cart->add($menuItem, $data['quantity'] ?? 1, $data['notes'] ?? null);

        return back()->with('status', __(':item added to cart.', ['item' => $menuItem->name]));
    }

    public function update(Request $request, MenuItem $menuItem, CartService $cart): RedirectResponse
    {
        $this->authorizeClientOrdering($request);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
            'notes' => ['nullable', 'string', 'max:240'],
        ]);

        $cart->update($menuItem->id, $data['quantity'], $data['notes'] ?? null);

        return back()->with('status', __('Cart updated.'));
    }

    public function clear(Request $request, CartService $cart): RedirectResponse
    {
        $this->authorizeClientOrdering($request);

        $cart->clear();

        return back()->with('status', __('Cart cleared.'));
    }

    private function authorizeClientOrdering(Request $request): void
    {
        abort_if($request->user() && ! $request->user()->hasAnyRole('client'), 403, __('Only client accounts can place orders.'));
    }
}
