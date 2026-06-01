<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CartService;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __invoke(CartService $cart): View
    {
        return view('tenant.menu', [
            'categories' => Category::query()
                ->with(['menuItems' => fn ($query) => $query->where('is_active', true)])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'cartLines' => $cart->lines(),
            'cartCount' => $cart->count(),
            'subtotalCents' => $cart->subtotalCents(),
        ]);
    }
}
