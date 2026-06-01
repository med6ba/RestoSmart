<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Models\Delivery;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderWorkflowService
{
    public function placeOrder(User $customer, array $cart, array $payload): Order
    {
        return DB::transaction(function () use ($customer, $cart, $payload) {
            $items = $this->menuItemsForCart($cart);

            if ($items->isEmpty()) {
                abort(422, 'Your cart is empty.');
            }

            $subtotal = $items->sum(fn (MenuItem $item) => $item->price_cents * $cart[$item->id]['quantity']);
            $deliveryFee = $payload['type'] === 'delivery' ? 300 : 0;
            $address = null;

            if ($payload['type'] === 'delivery') {
                $address = CustomerAddress::query()->create([
                    'user_id' => $customer->id,
                    'label' => 'Order '.now()->format('M d'),
                    'address' => $payload['delivery_address'],
                    'phone' => $payload['customer_phone'],
                ]);
            }

            $order = Order::query()->create([
                'public_code' => 'RS-'.now()->format('His').'-'.Str::upper(Str::random(4)),
                'user_id' => $customer->id,
                'customer_address_id' => $address?->id,
                'customer_name' => $payload['customer_name'],
                'customer_email' => $customer->email,
                'customer_phone' => $payload['customer_phone'],
                'delivery_address' => $payload['type'] === 'delivery' ? $payload['delivery_address'] : null,
                'type' => $payload['type'],
                'status' => 'received',
                'payment_status' => 'pending',
                'subtotal_cents' => $subtotal,
                'delivery_fee_cents' => $deliveryFee,
                'total_cents' => $subtotal + $deliveryFee,
                'kitchen_notes' => $payload['kitchen_notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                $quantity = $cart[$item->id]['quantity'];

                $order->items()->create([
                    'menu_item_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $quantity,
                    'unit_price_cents' => $item->price_cents,
                    'total_price_cents' => $item->price_cents * $quantity,
                    'notes' => $cart[$item->id]['notes'] ?? null,
                ]);

                $this->consumeStock($item, $quantity, $order);
            }

            if ($order->type === 'delivery') {
                $order->delivery()->create([
                    'status' => 'waiting',
                    'route_summary' => 'Restaurant -> '.$order->delivery_address.' (simulated 14 min route)',
                ]);
            }

            $this->notify('kitchen', 'new_order', 'New order '.$order->public_code, 'A '.$order->type.' order is waiting for preparation.');
            $this->notify('admin', 'new_order', 'New order '.$order->public_code, $order->formattedTotal().' from '.$order->customer_name);

            return $order->load(['items', 'delivery']);
        });
    }

    public function markPreparing(Order $order): Order
    {
        $order->update(['status' => 'preparing']);
        $this->notifyUser($order->user_id, 'order_update', 'Order in preparation', $order->public_code.' is now in the kitchen.');

        return $order;
    }

    public function markReady(Order $order): Order
    {
        $order->update([
            'status' => 'ready',
            'ready_at' => now(),
        ]);

        if ($order->type === 'delivery') {
            $this->notify('driver', 'delivery_ready', 'Delivery ready', $order->public_code.' is ready for dispatch.');
        }

        $this->notify('admin', 'order_ready', 'Order ready', $order->public_code.' can now be assigned or handed over.');
        $this->notifyUser($order->user_id, 'order_update', 'Order ready', $order->public_code.' is ready.');

        return $order;
    }

    public function assignDriver(Order $order, User $driver): Order
    {
        $order->update([
            'driver_id' => $driver->id,
            'status' => 'assigned',
        ]);

        Delivery::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'driver_id' => $driver->id,
                'status' => 'assigned',
                'assigned_at' => now(),
                'route_summary' => 'Restaurant -> '.$order->delivery_address.' (simulated 14 min route)',
            ],
        );

        $this->notifyUser($driver->id, 'assigned_delivery', 'Delivery assigned', $order->public_code.' is ready to pick up.');
        $this->notifyUser($order->user_id, 'order_update', 'Driver assigned', 'A driver is taking care of '.$order->public_code.'.');

        return $order;
    }

    public function pickUp(Order $order, User $driver): Order
    {
        $order->update([
            'driver_id' => $driver->id,
            'status' => 'out_for_delivery',
        ]);

        $order->delivery()->update([
            'driver_id' => $driver->id,
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        $this->notifyUser($order->user_id, 'order_update', 'Order on the way', $order->public_code.' is out for delivery.');

        return $order;
    }

    public function deliver(Order $order, User $driver): Order
    {
        $order->update([
            'driver_id' => $driver->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivered_at' => now(),
        ]);

        $order->delivery()->update([
            'driver_id' => $driver->id,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $this->notify('admin', 'order_delivered', 'Order delivered', $order->public_code.' was completed by '.$driver->name.'.');
        $this->notifyUser($order->user_id, 'order_update', 'Order delivered', 'Thanks for ordering with us.');

        return $order;
    }

    private function menuItemsForCart(array $cart): Collection
    {
        return MenuItem::query()
            ->with('ingredients')
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->get();
    }

    private function consumeStock(MenuItem $item, int $quantity, Order $order): void
    {
        if (! $item->stock_tracked) {
            return;
        }

        foreach ($item->ingredients as $ingredient) {
            $required = (float) $ingredient->pivot->quantity_required * $quantity;

            $ingredient->decrement('current_stock', $required);
            $ingredient->stockMovements()->create([
                'order_id' => $order->id,
                'type' => 'usage',
                'quantity' => -1 * $required,
                'note' => 'Consumed for '.$order->public_code,
            ]);

            $fresh = Ingredient::query()->find($ingredient->id);
            if ($fresh && $fresh->isLow()) {
                $this->notify('admin', 'low_stock', 'Low stock: '.$fresh->name, 'Current stock is '.$fresh->current_stock.' '.$fresh->unit.'.');
            }
        }
    }

    private function notify(?string $role, string $type, string $title, ?string $body = null): void
    {
        Notification::query()->create(compact('role', 'type', 'title', 'body'));
    }

    private function notifyUser(?int $userId, string $type, string $title, ?string $body = null): void
    {
        if (! $userId) {
            return;
        }

        Notification::query()->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);
    }
}
