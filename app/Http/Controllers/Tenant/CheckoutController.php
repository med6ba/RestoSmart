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
    public function create(Request $request, CartService $cart): View|RedirectResponse
    {
        if ($cart->count() === 0) {
            return redirect()->route('tenant.menu', tenant('id'))->with('status', __('Add at least one item before checkout.'));
        }

        $tableToken = $this->initialTableToken($request);

        return view('tenant.checkout', [
            'cartLines' => $cart->lines(),
            'subtotalCents' => $cart->subtotalCents(),
            'hasActiveTables' => RestaurantTable::query()->available()->exists(),
            'initialTableToken' => $tableToken,
            'selectedType' => old('type', $tableToken !== '' ? 'local' : 'delivery'),
        ]);
    }

    public function store(CheckoutRequest $request, CartService $cart, OrderWorkflowService $orders): RedirectResponse
    {
        $order = $orders->placeOrder($request->user(), $cart->all(), $request->validated());

        $cart->clear();
        $request->session()->forget($this->tableSessionKey());

        return redirect()->route('tenant.orders.show', [tenant('id'), $order])->with('status', __('Order placed. Your receipt PDF is ready.'));
    }

    public function validateTable(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:80'],
        ]);

        $table = RestaurantTable::query()
            ->where('qr_token', $data['token'])
            ->first();

        if (! $table) {
            return response()->json([
                'ok' => false,
                'message' => __('This table QR is not registered for this restaurant.'),
            ], 404);
        }

        if (! $table->is_active) {
            return response()->json([
                'ok' => false,
                'message' => __('This table QR is not registered for this restaurant.'),
            ], 404);
        }

        if ($table->is_occupied) {
            return response()->json([
                'ok' => false,
                'message' => __('This table is already occupied. Please choose another table.'),
            ], 409);
        }

        return response()->json([
            'ok' => true,
            'table' => $table->code,
            'message' => __('Table :table scanned.', ['table' => $table->code]),
        ]);
    }

    private function initialTableToken(Request $request): string
    {
        $tableToken = trim((string) old(
            'restaurant_table_token',
            $request->query('table', $request->session()->get($this->tableSessionKey(), '')),
        ));

        if ($tableToken === '') {
            return '';
        }

        if (RestaurantTable::query()->where('qr_token', $tableToken)->available()->exists()) {
            $request->session()->put($this->tableSessionKey(), $tableToken);

            return $tableToken;
        }

        $request->session()->forget($this->tableSessionKey());

        return '';
    }

    private function tableSessionKey(): string
    {
        return 'table_qr.'.tenant('id');
    }
}
