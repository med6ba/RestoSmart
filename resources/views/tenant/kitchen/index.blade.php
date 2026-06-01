<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">Kitchen display system</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('status') }}</div>
        @endif

        <div class="grid gap-4 lg:grid-cols-3">
            @forelse ($orders as $order)
                @php
                    $tone = match ($order->type) {
                        'delivery' => ['border' => 'border-red-300', 'text' => 'text-red-700'],
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
                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ App\Models\Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status) }}</span>
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
                        @if ($order->status === 'received')
                            <form method="POST" action="{{ route('tenant.kitchen.preparing', [tenant('id'), $order]) }}">
                                @csrf
                                <button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Start</button>
                            </form>
                        @endif
                        @if (in_array($order->status, ['received', 'preparing'], true))
                            <form method="POST" action="{{ route('tenant.kitchen.ready', [tenant('id'), $order]) }}">
                                @csrf
                                <button class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white">Ready</button>
                            </form>
                        @endif
                        @if (in_array($order->type, ['takeaway', 'click_collect'], true) && $order->status === 'ready')
                            <form method="POST" action="{{ route('tenant.kitchen.collected', [tenant('id'), $order]) }}">
                                @csrf
                                <button class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white">Collected</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-stone-200 bg-white p-6 text-sm text-stone-600 lg:col-span-3">No kitchen orders waiting.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
