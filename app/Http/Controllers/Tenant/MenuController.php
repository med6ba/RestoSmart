<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\RestaurantTable;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __invoke(Request $request, CartService $cart): View
    {
        $canOrder = ! $request->user() || $request->user()->hasAnyRole('client');
        $tableToken = trim((string) $request->query('table', ''));

        if ($tableToken !== '') {
            $table = RestaurantTable::query()->where('qr_token', $tableToken)->first();

            if ($table && $table->is_active && ! $table->is_occupied) {
                $request->session()->put($this->tableSessionKey(), $tableToken);
            } else {
                $request->session()->forget($this->tableSessionKey());
                $request->session()->flash('status', $table
                    ? __('This table is already occupied. Please choose another table.')
                    : __('This table QR is not registered for this restaurant.'));
            }
        }

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

    private function tableSessionKey(): string
    {
        return 'table_qr.'.tenant('id');
    }
}
