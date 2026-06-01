<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">Driver mobile dashboard</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('status') }}</div>
        @endif

        <section>
            <h2 class="text-lg font-semibold">My route</h2>
            <div class="mt-4 space-y-4">
                @forelse ($assigned as $order)
                    <article class="rounded-lg border border-red-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xl font-bold">{{ $order->public_code }}</p>
                                <p class="mt-1 text-sm text-stone-600">{{ $order->customer_name }} · {{ $order->customer_phone }}</p>
                            </div>
                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-800">{{ App\Models\Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status) }}</span>
                        </div>
                        <p class="mt-4 rounded-lg bg-stone-100 p-3 text-sm text-stone-700">{{ $order->delivery_address }}</p>
                        <p class="mt-2 text-sm text-stone-600">{{ $order->delivery?->route_summary }}</p>
                        <div class="mt-4 flex gap-2">
                            @if ($order->status === 'assigned')
                                <form method="POST" action="{{ route('tenant.driver.pickup', [tenant('id'), $order]) }}">
                                    @csrf
                                    <button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Pick up</button>
                                </form>
                            @endif
                            @if ($order->status === 'out_for_delivery')
                                <form method="POST" action="{{ route('tenant.driver.deliver', [tenant('id'), $order]) }}">
                                    @csrf
                                    <button class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white">Delivered</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-stone-200 bg-white p-5 text-sm text-stone-600">No assigned delivery right now.</div>
                @endforelse
            </div>
        </section>

        <section class="mt-8">
            <h2 class="text-lg font-semibold">Ready for dispatch</h2>
            <div class="mt-4 space-y-4">
                @forelse ($available as $order)
                    <article class="rounded-lg border border-stone-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xl font-bold">{{ $order->public_code }}</p>
                                <p class="mt-1 text-sm text-stone-600">{{ $order->customer_name }} · {{ $order->customer_phone }}</p>
                            </div>
                            <p class="font-semibold">{{ $order->formattedTotal() }}</p>
                        </div>
                        <p class="mt-4 rounded-lg bg-stone-100 p-3 text-sm text-stone-700">{{ $order->delivery_address }}</p>
                        <form method="POST" action="{{ route('tenant.driver.pickup', [tenant('id'), $order]) }}" class="mt-4">
                            @csrf
                            <button class="w-full rounded-lg bg-red-700 px-4 py-3 text-sm font-semibold text-white">Take delivery</button>
                        </form>
                    </article>
                @empty
                    <div class="rounded-lg border border-stone-200 bg-white p-5 text-sm text-stone-600">No ready delivery orders.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
