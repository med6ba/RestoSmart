<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">Kitchen display system</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8" data-realtime-scope="orders,stock,menu">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-brand-200 bg-brand-50 p-4 text-sm text-brand-800">{{ session('status') }}</div>
        @endif

        <div class="grid gap-4 lg:grid-cols-3">
            @forelse ($orders as $order)
                @php
                    $tone = match ($order->type) {
                        'delivery' => ['border' => 'border-brand-300', 'text' => 'text-brand-700'],
                        'local' => ['border' => 'border-emerald-300', 'text' => 'text-emerald-700'],
                        default => ['border' => 'border-amber-300', 'text' => 'text-amber-700'],
                    };
                @endphp

                <article class="rounded-lg border bg-white p-4 {{ $tone['border'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide {{ $tone['text'] }}">{{ $order->typeLabel() }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ $order->public_code }}</h2>
                            <p class="text-sm text-stone-600">{{ $order->created_at->diffForHumans() }}</p>
                            @if ($order->type === 'local' && $order->restaurantTable)
                                <p class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $order->restaurantTable->code }}</p>
                            @endif
                        </div>
                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ __(App\Models\Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status)) }}</span>
                    </div>

                    <div class="mt-4 divide-y divide-stone-100">
                        @foreach ($order->items as $item)
                            <div class="py-2">
                                <p class="font-semibold">{{ $item->quantity }} x {{ $item->name }}</p>
                                @if ($item->notes)
                                    <p class="text-sm text-stone-600">{{ $item->notes }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($order->kitchen_notes)
                        <p class="mt-4 rounded-lg bg-stone-100 p-3 text-sm text-stone-700">{{ $order->kitchen_notes }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('tenant.orders.receipt', [tenant('id'), $order]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 app-focus">
                            <x-icon name="receipt" class="h-4 w-4" />
                            {{ __('Print receipt') }}
                        </a>
                        @if ($order->status === 'received')
                            <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'start-order-{{ $order->id }}')" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Start</button>
                        @endif
                        @if ($order->status === 'preparing')
                            <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'ready-order-{{ $order->id }}')" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white">Ready</button>
                        @endif
                        @if (in_array($order->type, ['takeaway', 'click_collect'], true) && $order->status === 'ready')
                            <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'collect-order-{{ $order->id }}')" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white">Collected</button>
                        @endif
                    </div>

                    @if ($order->status === 'received')
                        <x-modal name="start-order-{{ $order->id }}" maxWidth="md" focusable>
                            <form method="POST" action="{{ route('tenant.kitchen.preparing', [tenant('id'), $order]) }}" class="p-6">
                                @csrf
                                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">Start {{ $order->public_code }}?</h3>
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">This moves the order into preparation.</p>
                                <div class="mt-6 flex justify-end gap-3">
                                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                                    <x-primary-button>Start order</x-primary-button>
                                </div>
                            </form>
                        </x-modal>
                    @endif

                    @if ($order->status === 'preparing')
                        <x-modal name="ready-order-{{ $order->id }}" maxWidth="md" focusable>
                            <form method="POST" action="{{ route('tenant.kitchen.ready', [tenant('id'), $order]) }}" class="p-6">
                                @csrf
                                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">Mark {{ $order->public_code }} ready?</h3>
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Drivers, admins, and guests will see the ready status.</p>
                                <div class="mt-6 flex justify-end gap-3">
                                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                                    <x-primary-button>Mark ready</x-primary-button>
                                </div>
                            </form>
                        </x-modal>
                    @endif

                    @if (in_array($order->type, ['takeaway', 'click_collect'], true) && $order->status === 'ready')
                        <x-modal name="collect-order-{{ $order->id }}" maxWidth="md" focusable>
                            <form method="POST" action="{{ route('tenant.kitchen.collected', [tenant('id'), $order]) }}" class="p-6">
                                @csrf
                                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">Mark {{ $order->public_code }} collected?</h3>
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">This closes the pickup order.</p>
                                <div class="mt-6 flex justify-end gap-3">
                                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                                    <x-primary-button>Mark collected</x-primary-button>
                                </div>
                            </form>
                        </x-modal>
                    @endif
                </article>
            @empty
                <div class="rounded-lg border border-stone-200 bg-white p-6 text-sm text-stone-600 lg:col-span-3">No kitchen orders waiting.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
