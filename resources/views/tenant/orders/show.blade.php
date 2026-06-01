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
                async refresh() {
                    const response = await fetch(@js(route('tenant.orders.status', [tenant('id'), $order])));
                    if (! response.ok) return;
                    const payload = await response.json();
                    this.label = payload.label;
                    this.status = payload.status;
                    this.driver = payload.driver;
                    this.updated = payload.updated_at;
                }
            }"
            x-init="setInterval(() => refresh(), 3000)"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-red-700">Live tracking</p>
                    <h2 class="mt-1 text-3xl font-bold" x-text="label"></h2>
                    <p class="mt-2 text-sm text-stone-600">Updated <span x-text="updated"></span></p>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ ucfirst(str_replace('_', ' ', $order->type)) }}</span>
            </div>

            <div class="mt-8 grid gap-3 sm:grid-cols-5">
                @foreach (['received', 'preparing', 'ready', 'out_for_delivery', 'delivered'] as $step)
                    <div class="rounded-lg border border-stone-200 p-3" :class="status === @js($step) ? 'border-red-600 bg-red-50' : ''">
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Step</p>
                        <p class="mt-1 text-sm font-semibold">{{ $statuses[$step] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                <h3 class="font-semibold">Items</h3>
                <div class="mt-3 divide-y divide-stone-100">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between gap-4 py-3 text-sm">
                            <span>{{ $item->quantity }} × {{ $item->name }}</span>
                            <span class="font-semibold">${{ number_format($item->total_price_cents / 100, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <aside class="h-fit rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Customer and delivery</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="font-semibold">Customer</dt><dd class="text-stone-600">{{ $order->customer_name }} · {{ $order->customer_phone }}</dd></div>
                <div><dt class="font-semibold">Address</dt><dd class="text-stone-600">{{ $order->delivery_address ?: 'Click & collect' }}</dd></div>
                <div><dt class="font-semibold">Driver</dt><dd class="text-stone-600" x-text="driver || 'Not assigned yet'"></dd></div>
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
