<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->hasAnyRole(['admin', 'kitchen'])
            || $order->user_id === $user->id
            || $order->driver_id === $user->id
            || ($user->role === 'driver' && $order->status === 'ready');
    }

    public function manageKitchen(User $user, Order $order): bool
    {
        return $user->hasAnyRole('kitchen');
    }

    public function deliver(User $user, Order $order): bool
    {
        return $user->role === 'driver'
            && ($order->driver_id === null || $order->driver_id === $user->id);
    }
}
