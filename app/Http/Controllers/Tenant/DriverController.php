<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(Request $request): View
    {
        return view('tenant.driver.index', [
            'assigned' => Order::query()
                ->with(['items', 'delivery'])
                ->where('driver_id', $request->user()->id)
                ->whereIn('status', ['assigned', 'out_for_delivery'])
                ->latest()
                ->get(),
            'available' => Order::query()
                ->with('items')
                ->where('type', 'delivery')
                ->where('status', 'ready')
                ->latest()
                ->get(),
        ]);
    }

    public function pickup(Request $request, Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        abort_unless(in_array($order->status, ['ready', 'assigned'], true), 422);

        if ($order->status === 'ready') {
            $workflow->assignDriver($order, $request->user());
        }

        $workflow->pickUp($order->fresh(), $request->user());

        return back()->with('status', $order->public_code.' picked up.');
    }

    public function deliver(Request $request, Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        abort_unless($order->driver_id === $request->user()->id, 403);

        $workflow->deliver($order, $request->user());

        return back()->with('status', $order->public_code.' delivered.');
    }
}
