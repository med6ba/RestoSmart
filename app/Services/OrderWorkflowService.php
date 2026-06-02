<?php

namespace App\Services;

use App\Events\TenantRoleUpdated;
use App\Models\CustomerAddress;
use App\Models\Delivery;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class OrderWorkflowService
{
    public function placeOrder(User $customer, array $cart, array $payload): Order
    {
        $order = DB::transaction(function () use ($customer, $cart, $payload) {
            $items = $this->menuItemsForCart($cart);
            $type = $this->normalizeType($payload['type']);

            if ($items->isEmpty()) {
                abort(422, __('Your cart is empty.'));
            }

            $subtotal = $items->sum(fn (MenuItem $item) => $item->price_cents * $cart[$item->id]['quantity']);
            $deliveryFee = $type === 'delivery' ? 300 : 0;
            $address = null;
            $table = null;

            if ($type === 'delivery') {
                $address = CustomerAddress::query()->create([
                    'user_id' => $customer->id,
                    'label' => 'Order '.now()->format('M d'),
                    'address' => $payload['delivery_address'],
                    'phone' => $payload['customer_phone'],
                ]);
            }

            if ($type === 'local') {
                $table = RestaurantTable::query()
                    ->where('qr_token', $payload['restaurant_table_token'])
                    ->lockForUpdate()
                    ->first();

                if (! $table || ! $table->is_active) {
                    abort(422, __('This table QR is not registered for this restaurant.'));
                }

                if ($table->is_occupied) {
                    abort(422, __('This table is already occupied. Please choose another table.'));
                }
            }

            $order = Order::query()->create([
                'public_code' => 'RS-'.now()->format('His').'-'.Str::upper(Str::random(4)),
                'user_id' => $customer->id,
                'customer_address_id' => $address?->id,
                'restaurant_table_id' => $table?->id,
                'customer_name' => $payload['customer_name'],
                'customer_email' => $customer->email,
                'customer_phone' => $payload['customer_phone'],
                'delivery_address' => $type === 'delivery' ? $payload['delivery_address'] : null,
                'type' => $type,
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
                ] + $this->deliveryRouteAttributes($order));
            }

            if ($table) {
                $table->update([
                    'is_occupied' => true,
                    'occupied_order_id' => $order->id,
                    'occupied_at' => now(),
                ]);
            }

            $this->notify('kitchen', 'new_order', __('New order :code', ['code' => $order->public_code]), __('A :type order is waiting for preparation.', ['type' => $order->typeLabel()]));
            $this->notify('admin', 'new_order', __('New order :code', ['code' => $order->public_code]), __(':total from :customer', ['total' => $order->formattedTotal(), 'customer' => $order->customer_name]));

            return $order->load(['items', 'delivery', 'restaurantTable']);
        });

        $this->broadcastTenantUpdate(['admin', 'kitchen', 'client'], 'orders', 'order.placed', $order, __('New order :code received.', ['code' => $order->public_code]));

        if ($order->restaurantTable) {
            $this->broadcastTableUpdate($order->restaurantTable, 'table.occupied', __('Table :table is now occupied.', ['table' => $order->restaurantTable->code]));
        }

        return $order;
    }

    public function markPreparing(Order $order): Order
    {
        $order->update(['status' => 'preparing']);
        $this->notifyUser($order->user_id, 'order_update', __('Order in preparation'), __(':code is now in the kitchen.', ['code' => $order->public_code]));
        $this->broadcastTenantUpdate(['admin', 'kitchen', 'client'], 'orders', 'order.preparing', $order, __('Order :code is now preparing.', ['code' => $order->public_code]));

        return $order;
    }

    public function markReady(Order $order): Order
    {
        $order->update([
            'status' => 'ready',
            'ready_at' => now(),
        ]);

        match ($order->type) {
            'delivery' => $this->notify('driver', 'delivery_ready', __('Delivery ready'), __(':code is ready for dispatch.', ['code' => $order->public_code])),
            'takeaway' => $this->notifyUser($order->user_id, 'order_update', __('Takeaway ready'), __(':code is ready for pickup.', ['code' => $order->public_code])),
            'local' => $this->notifyUser($order->user_id, 'order_update', __('Order ready'), __(':code is ready for your table.', ['code' => $order->public_code])),
            default => null,
        };

        $this->notify('admin', 'order_ready', __('Order ready'), __(':code can now be assigned, served, or handed over.', ['code' => $order->public_code]));

        if (! in_array($order->type, ['local', 'takeaway'], true)) {
            $this->notifyUser($order->user_id, 'order_update', __('Order ready'), __(':code is ready.', ['code' => $order->public_code]));
        }

        $roles = $order->type === 'delivery'
            ? ['admin', 'kitchen', 'driver', 'client']
            : ['admin', 'kitchen', 'client'];

        $this->broadcastTenantUpdate($roles, 'orders', 'order.ready', $order, __('Order :code is ready.', ['code' => $order->public_code]));

        return $order;
    }

    public function markCollected(Order $order): Order
    {
        $order->update([
            'status' => 'collected',
            'payment_status' => 'paid',
            'collected_at' => now(),
        ]);

        if ($order->type === 'local' && $order->restaurantTable) {
            $order->restaurantTable->update([
                'is_occupied' => false,
                'occupied_order_id' => null,
                'occupied_at' => null,
            ]);
        }

        $this->notify(
            'admin',
            'order_collected',
            $order->type === 'local' ? __('Order collected') : __('Takeaway collected'),
            $order->type === 'local'
                ? __(':code was closed for table :table.', ['code' => $order->public_code, 'table' => $order->restaurantTable?->code ?? __('Table')])
                : __(':code was picked up by :customer.', ['code' => $order->public_code, 'customer' => $order->customer_name]),
        );
        $this->notifyUser($order->user_id, 'order_update', __('Order collected'), __('Enjoy your meal.'));
        $this->broadcastTenantUpdate(['admin', 'kitchen', 'client'], 'orders', 'order.collected', $order, __('Order :code was collected.', ['code' => $order->public_code]));

        if ($order->type === 'local' && $order->restaurantTable) {
            $this->broadcastTableUpdate($order->restaurantTable, 'table.available', __('Table :table is available again.', ['table' => $order->restaurantTable->code]));
        }

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
            ] + $this->deliveryRouteAttributes($order),
        );

        $this->notifyUser($driver->id, 'assigned_delivery', __('Delivery assigned'), __(':code is ready to pick up.', ['code' => $order->public_code]));
        $this->notifyUser($order->user_id, 'order_update', __('Driver assigned'), __('A driver is taking care of :code.', ['code' => $order->public_code]));
        $this->broadcastTenantUpdate(['admin', 'driver', 'client'], 'orders', 'order.assigned', $order->fresh(['driver']), __('Order :code was assigned.', ['code' => $order->public_code]));

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

        $this->notifyUser($order->user_id, 'order_update', __('Order on the way'), __(':code is out for delivery.', ['code' => $order->public_code]));
        $this->broadcastTenantUpdate(['admin', 'driver', 'client'], 'orders', 'order.out_for_delivery', $order, __('Order :code is out for delivery.', ['code' => $order->public_code]));

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

        $this->notify('admin', 'order_delivered', __('Order delivered'), __(':code was completed by :driver.', ['code' => $order->public_code, 'driver' => $driver->name]));
        $this->notifyUser($order->user_id, 'order_update', __('Order delivered'), __('Thanks for ordering with us.'));
        $this->broadcastTenantUpdate(['admin', 'driver', 'client'], 'orders', 'order.delivered', $order, __('Order :code was delivered.', ['code' => $order->public_code]));

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

    private function normalizeType(string $type): string
    {
        return $type === 'click_collect' ? 'takeaway' : $type;
    }

    /**
     * @return array<string, mixed>
     */
    private function deliveryRouteAttributes(Order $order): array
    {
        return [
            'route_summary' => __('Restaurant').' -> '.$order->delivery_address,
        ];
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
                'note' => __('Consumed for :code', ['code' => $order->public_code]),
            ]);

            $fresh = Ingredient::query()->find($ingredient->id);
            if ($fresh && $fresh->isLow()) {
                $this->notify('admin', 'low_stock', __('Low stock: :name', ['name' => $fresh->name]), __('Current stock is :stock :unit.', ['stock' => $fresh->current_stock, 'unit' => $fresh->unit]));
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

    /**
     * @param  array<int, string>  $roles
     */
    private function broadcastTenantUpdate(array $roles, string $area, string $type, Order $order, string $message): void
    {
        try {
            TenantRoleUpdated::dispatch((string) tenant('id'), $roles, $area, $type, [
                'message' => $message,
                'order' => [
                    'id' => $order->id,
                    'public_code' => $order->public_code,
                    'status' => $order->status,
                    'status_label' => __(Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status)),
                    'type' => $order->type,
                    'type_label' => $order->typeLabel(),
                    'driver' => $order->driver?->name,
                    'total' => $order->formattedTotal(),
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function broadcastTableUpdate(RestaurantTable $table, string $type, string $message): void
    {
        try {
            TenantRoleUpdated::dispatch((string) tenant('id'), ['admin', 'client'], 'tables', $type, [
                'message' => $message,
                'table' => [
                    'id' => $table->id,
                    'code' => $table->code,
                    'is_active' => $table->is_active,
                    'is_occupied' => $table->is_occupied,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
