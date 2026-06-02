<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
}
