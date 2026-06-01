<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('tenant.orders.index', [
            'orders' => Order::query()
                ->with(['items', 'delivery', 'driver'])
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(10),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        Gate::authorize('view', $order);

        return view('tenant.orders.show', [
            'order' => $order->load(['items', 'delivery.driver']),
            'statuses' => Order::STATUS_FLOW,
        ]);
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        return response()->json([
            'status' => $order->status,
            'label' => Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status),
            'payment_status' => $order->payment_status,
            'driver' => $order->driver?->name,
            'updated_at' => $order->updated_at?->diffForHumans(),
        ]);
    }
}
