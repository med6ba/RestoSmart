<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Collection;

class CartService
{
    public function key(): string
    {
        return 'cart.'.tenant('id');
    }

    public function all(): array
    {
        return session($this->key(), []);
    }

    public function add(MenuItem $item, int $quantity = 1, ?string $notes = null): void
    {
        $cart = $this->all();
        $current = $cart[$item->id]['quantity'] ?? 0;

        $cart[$item->id] = [
            'quantity' => min(20, $current + max(1, $quantity)),
            'notes' => $notes,
        ];

        session([$this->key() => $cart]);
    }

    public function update(int $itemId, int $quantity, ?string $notes = null): void
    {
        $cart = $this->all();

        if ($quantity <= 0) {
            unset($cart[$itemId]);
        } else {
            $cart[$itemId] = [
                'quantity' => min(20, $quantity),
                'notes' => $notes,
            ];
        }

        session([$this->key() => $cart]);
    }

    public function clear(): void
    {
        session()->forget($this->key());
    }

    public function lines(): Collection
    {
        $cart = $this->all();

        if ($cart === []) {
            return collect();
        }

        return MenuItem::query()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->map(function (MenuItem $item) use ($cart) {
                $quantity = $cart[$item->id]['quantity'];

                return [
                    'item' => $item,
                    'quantity' => $quantity,
                    'notes' => $cart[$item->id]['notes'] ?? null,
                    'total_cents' => $item->price_cents * $quantity,
                ];
            });
    }

    public function subtotalCents(): int
    {
        return $this->lines()->sum('total_cents');
    }

    public function count(): int
    {
        return collect($this->all())->sum('quantity');
    }
}
