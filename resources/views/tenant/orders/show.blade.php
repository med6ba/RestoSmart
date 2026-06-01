@php
    $steps = match ($order->type) {
        'delivery' => ['received', 'preparing', 'ready', 'assigned', 'out_for_delivery', 'delivered'],
        'local' => ['received', 'preparing', 'ready'],
        default => ['received', 'preparing', 'ready', 'collected'],
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">Order {{ $order->public_code }}</h1>
    </x-slot>

    <div class="max-w-6xl mx-auto grid gap-6 px-4 py-8 lg:grid-cols-[1fr_340px] sm:px-6 lg:px-8">
        <section
            class="rounded-lg border border-stone-200 bg-white p-5"
            x-data="{
                label: @js($statuses[$order->status] ?? ucfirst($order->status)),
                status: @js($order->status),
                driver: @js($order->driver?->name),
                updated: @js($order->updated_at?->diffForHumans()),
                tracking: null,
                async refresh() {
                    const response = await fetch(@js(route('tenant.orders.status', [tenant('id'), $order])));
                    if (! response.ok) return;
                    const payload = await response.json();
                    this.label = payload.label;
                    this.status = payload.status;
                    this.driver = payload.driver;
                    this.tracking = payload.delivery_tracking;
                    this.updated = payload.updated_at;
                }
            }"
            x-init="refresh(); setInterval(() => refresh(), 3000)"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-red-700">Live order status</p>
                    <h2 class="mt-1 text-3xl font-bold" x-text="label"></h2>
                    <p class="mt-2 text-sm text-stone-600">Updated <span x-text="updated"></span></p>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ $order->typeLabel() }}</span>
            </div>

            <div class="mt-8 grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));">
                @foreach ($steps as $step)
                    <div class="rounded-lg border border-stone-200 p-3" :class="status === @js($step) ? 'border-red-600 bg-red-50' : ''">
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Step</p>
                        <p class="mt-1 text-sm font-semibold">{{ $statuses[$step] }}</p>
                    </div>
                @endforeach
            </div>

            @if ($order->type === 'delivery')
                <div class="mt-8" x-show="tracking" x-cloak>
                    <h3 class="font-semibold">Delivery map</h3>
                    <div class="relative mt-3 h-80 overflow-hidden rounded-lg border border-stone-200 bg-stone-100">
                        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <pattern id="delivery-grid-{{ $order->id }}" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="#d6d3d1" stroke-width="0.35" />
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#delivery-grid-{{ $order->id }})" />
                            <line
                                :x1="tracking?.restaurant?.x || 12"
                                :y1="tracking?.restaurant?.y || 88"
                                :x2="tracking?.destination?.x || 88"
                                :y2="tracking?.destination?.y || 12"
                                stroke="#b91c1c"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-dasharray="3 3"
                            />
                            <circle :cx="tracking?.restaurant?.x || 12" :cy="tracking?.restaurant?.y || 88" r="3.5" fill="#111827" />
                            <circle :cx="tracking?.destination?.x || 88" :cy="tracking?.destination?.y || 12" r="3.5" fill="#b91c1c" />
                        </svg>

                        <div
                            class="absolute grid h-10 w-10 place-items-center rounded-full bg-white text-red-700 shadow-lg ring-2 ring-red-600 transition-all duration-500"
                            x-bind:style="`left: calc(${tracking?.driver?.x || 12}% - 20px); top: calc(${tracking?.driver?.y || 88}% - 20px);`"
                        >
                            <x-icon name="truck" class="h-5 w-5" />
                        </div>

                        <div class="absolute bottom-3 left-3 rounded-lg bg-white/95 px-3 py-2 text-xs font-semibold shadow">
                            <span x-text="tracking?.driver_name || 'Driver pending'"></span>
                            <span x-show="tracking?.last_seen"> - <span x-text="tracking?.last_seen"></span></span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-8">
                <h3 class="font-semibold">Items</h3>
                <div class="mt-3 divide-y divide-stone-100">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between gap-4 py-3 text-sm">
                            <span>{{ $item->quantity }} x {{ $item->name }}</span>
                            <span class="font-semibold">${{ number_format($item->total_price_cents / 100, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <aside class="h-fit rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Order details</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="font-semibold">Customer</dt><dd class="text-stone-600">{{ $order->customer_name }} - {{ $order->customer_phone }}</dd></div>
                <div><dt class="font-semibold">Mode</dt><dd class="text-stone-600">{{ $order->typeLabel() }}</dd></div>

                @if ($order->type === 'delivery')
                    <div><dt class="font-semibold">Address</dt><dd class="text-stone-600">{{ $order->delivery_address }}</dd></div>
                    <div><dt class="font-semibold">Driver</dt><dd class="text-stone-600" x-text="driver || 'Not assigned yet'"></dd></div>
                @elseif ($order->type === 'local')
                    <div><dt class="font-semibold">Table</dt><dd class="text-stone-600">{{ $order->restaurantTable?->code ?? 'Scanned table' }}</dd></div>
                @else
                    <div><dt class="font-semibold">Pickup</dt><dd class="text-stone-600">Watch this page for the ready and collected states.</dd></div>
                @endif

                <div><dt class="font-semibold">Total</dt><dd class="text-stone-950">{{ $order->formattedTotal() }}</dd></div>
            </dl>
            @if ($order->delivery)
                <div class="mt-5 rounded-lg bg-stone-100 p-4 text-sm text-stone-700">
                    {{ $order->delivery->route_summary }}
                </div>
            @endif
        </aside>
    </div>
</x-app-layout>
