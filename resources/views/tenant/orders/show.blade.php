@php
    $steps = match ($order->type) {
        'delivery' => ['received', 'preparing', 'ready', 'assigned', 'out_for_delivery', 'delivered'],
        'local' => ['received', 'preparing', 'ready'],
        default => ['received', 'preparing', 'ready', 'collected'],
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">{{ __('Order :code', ['code' => $order->public_code]) }}</h1>
    </x-slot>

    <div class="max-w-6xl mx-auto grid gap-6 px-4 py-8 lg:grid-cols-[1fr_340px] sm:px-6 lg:px-8" data-realtime-scope="orders">
        @if (session('status'))
            <div class="status-toast lg:col-span-2">{{ session('status') }}</div>
        @endif

        <section
            class="rounded-lg border border-stone-200 bg-white p-5"
            x-data="{
                label: @js($statuses[$order->status] ?? ucfirst($order->status)),
                status: @js($order->status),
                driver: @js($order->driver?->name),
                driverFallback: @js(__('Not assigned yet')),
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
            x-init="refresh(); setInterval(() => refresh(), 3000)"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-brand-700">{{ __('Order status') }}</p>
                    <h2 class="mt-1 text-3xl font-bold" x-text="label"></h2>
                    <p class="mt-2 text-sm text-stone-600">{{ __('Updated') }} <span x-text="updated"></span></p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('tenant.orders.receipt', [tenant('id'), $order]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800 app-focus">
                        <x-icon name="receipt" class="h-4 w-4" />
                        {{ __('Receipt PDF') }}
                    </a>
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ $order->typeLabel() }}</span>
                </div>
            </div>

            <div class="mt-8 grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));">
                @foreach ($steps as $step)
                    <div class="rounded-lg border border-stone-200 p-3" :class="status === @js($step) ? 'border-brand-600 bg-brand-50' : ''">
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">{{ __('Step') }}</p>
                        <p class="mt-1 text-sm font-semibold">{{ $statuses[$step] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                <h3 class="font-semibold">{{ __('Items') }}</h3>
                <div class="mt-3 divide-y divide-stone-100">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between gap-4 py-3 text-sm">
                            <span>{{ $item->quantity }} x {{ $item->name }}</span>
                            <span class="font-semibold">{{ \App\Support\Money::mad($item->total_price_cents) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <aside class="h-fit rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">{{ __('Order details') }}</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="font-semibold">{{ __('Customer') }}</dt><dd class="text-stone-600">{{ $order->customer_name }} - {{ $order->customer_phone }}</dd></div>
                <div><dt class="font-semibold">{{ __('Mode') }}</dt><dd class="text-stone-600">{{ $order->typeLabel() }}</dd></div>

                @if ($order->type === 'delivery')
                    <div><dt class="font-semibold">{{ __('Address') }}</dt><dd class="text-stone-600">{{ $order->delivery_address }}</dd></div>
                    <div><dt class="font-semibold">{{ __('Driver') }}</dt><dd class="text-stone-600" x-text="driver || driverFallback"></dd></div>
                @elseif ($order->type === 'local')
                    <div><dt class="font-semibold">{{ __('Table') }}</dt><dd class="text-stone-600">{{ $order->restaurantTable?->code ?? __('Scanned table') }}</dd></div>
                @else
                    <div><dt class="font-semibold">{{ __('Pickup') }}</dt><dd class="text-stone-600">{{ __('Watch this page for the ready and collected states.') }}</dd></div>
                @endif

                <div><dt class="font-semibold">{{ __('Total') }}</dt><dd class="text-stone-950">{{ $order->formattedTotal() }}</dd></div>
            </dl>
            @if ($order->delivery)
                <div class="mt-5 rounded-lg bg-stone-100 p-4 text-sm text-stone-700">
                    {{ $order->delivery->route_summary ?: __('Delivery details are attached to this order.') }}
                </div>
            @endif

            <a href="{{ route('tenant.orders.receipt', [tenant('id'), $order]) }}" target="_blank" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-800 app-focus">
                <x-icon name="receipt" class="h-4 w-4" />
                {{ __('Open receipt PDF') }}
            </a>
        </aside>
    </div>
</x-app-layout>
