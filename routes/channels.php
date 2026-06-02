<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tenant.{tenantId}.role.{role}', function ($user, string $tenantId, string $role) {
    return (string) $user->tenant_id === $tenantId && $user->hasAnyRole($role);
});

Broadcast::channel('delivery-chat.{orderId}', function ($user, int|string $orderId) {
    if (! $user->hasAnyRole(['client', 'driver']) || ! $user->tenant_id) {
        return false;
    }

    $order = Order::query()
        ->with('delivery')
        ->whereKey($orderId)
        ->where('tenant_id', $user->tenant_id)
        ->where('type', 'delivery')
        ->first();

    if (! $order?->delivery?->driver_id) {
        return false;
    }

    return ($user->role === 'client' && (int) $order->user_id === (int) $user->id)
        || ($user->role === 'driver' && (int) $order->delivery->driver_id === (int) $user->id);
});
