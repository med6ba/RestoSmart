<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __invoke(Request $request, CartService $cart): View
    {
        $canOrder = ! $request->user() || $request->user()->hasAnyRole('client');

        return view('tenant.menu', [
            'categories' => Category::query()
                ->with(['menuItems' => fn ($query) => $query->where('is_active', true)])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'cartLines' => $canOrder ? $cart->lines() : collect(),
            'cartCount' => $canOrder ? $cart->count() : 0,
            'canOrder' => $canOrder,
            'subtotalCents' => $canOrder ? $cart->subtotalCents() : 0,
        ]);
    }
}
