<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">Driver mobile dashboard</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8" data-realtime-scope="orders">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-brand-200 bg-brand-50 p-4 text-sm text-brand-800">{{ session('status') }}</div>
        @endif

        <section>
            <h2 class="text-lg font-semibold">My route</h2>
            <div class="mt-4 space-y-4">
                @forelse ($assigned as $order)
                    <article class="rounded-lg border border-brand-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xl font-bold">{{ $order->public_code }}</p>
                                <p class="mt-1 text-sm text-stone-600">{{ $order->customer_name }} · {{ $order->customer_phone }}</p>
                            </div>
                            <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-800">{{ __(App\Models\Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status)) }}</span>
                        </div>
                        <p class="mt-4 rounded-lg bg-stone-100 p-3 text-sm text-stone-700">{{ $order->delivery_address }}</p>
                        <p class="mt-2 text-sm text-stone-600">{{ $order->delivery?->route_summary }}</p>
                        <div class="mt-4 flex gap-2">
                            @if ($order->status === 'assigned')
                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'pickup-order-{{ $order->id }}')" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Pick up</button>
                            @endif
                            @if ($order->status === 'out_for_delivery')
                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'deliver-order-{{ $order->id }}')" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white">Delivered</button>
                            @endif
                        </div>

                        @if ($order->status === 'assigned')
                            <x-modal name="pickup-order-{{ $order->id }}" maxWidth="md" focusable>
                                <form method="POST" action="{{ route('tenant.driver.pickup', [tenant('id'), $order]) }}" class="p-6">
                                    @csrf
                                    <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">Pick up {{ $order->public_code }}?</h3>
                                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">This starts the delivery route and updates the customer.</p>
                                    <div class="mt-6 flex justify-end gap-3">
                                        <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                                        <x-primary-button>Pick up order</x-primary-button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif

                        @if ($order->status === 'out_for_delivery')
                            <x-modal name="deliver-order-{{ $order->id }}" maxWidth="md" focusable>
                                <form method="POST" action="{{ route('tenant.driver.deliver', [tenant('id'), $order]) }}" class="p-6">
                                    @csrf
                                    <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">Mark {{ $order->public_code }} delivered?</h3>
                                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">This closes the delivery for the guest and admin dashboard.</p>
                                    <div class="mt-6 flex justify-end gap-3">
                                        <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                                        <x-primary-button>Mark delivered</x-primary-button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif
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
                        <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'take-delivery-{{ $order->id }}')" class="mt-4 w-full rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white">Take delivery</button>

                        <x-modal name="take-delivery-{{ $order->id }}" maxWidth="md" focusable>
                            <form method="POST" action="{{ route('tenant.driver.pickup', [tenant('id'), $order]) }}" class="p-6">
                                @csrf
                                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">Take {{ $order->public_code }}?</h3>
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">This assigns the delivery to you and starts pickup.</p>
                                <div class="mt-6 flex justify-end gap-3">
                                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                                    <x-primary-button>Take delivery</x-primary-button>
                                </div>
                            </form>
                        </x-modal>
                    </article>
                @empty
                    <div class="rounded-lg border border-stone-200 bg-white p-5 text-sm text-stone-600">No ready delivery orders.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
