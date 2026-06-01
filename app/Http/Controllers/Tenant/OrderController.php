<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\ReceiptPdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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
            'order' => $order->load(['items', 'delivery.driver', 'restaurantTable']),
            'statuses' => collect(Order::STATUS_FLOW)->map(fn (string $label): string => __($label))->all(),
        ]);
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        return response()->json([
            'status' => $order->status,
            'label' => __(Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status)),
            'payment_status' => $order->payment_status,
            'driver' => $order->driver?->name,
            'updated_at' => $order->updated_at?->diffForHumans(),
        ]);
    }

    public function receipt(Request $request, Order $order): Response
    {
        Gate::authorize('view', $order);

        $filename = 'receipt-'.Str::slug($order->public_code).'.pdf';

        return response(ReceiptPdf::make($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
