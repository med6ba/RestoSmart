<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">My orders</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-lg border border-stone-200 bg-white">
            @forelse ($orders as $order)
                <a href="{{ route('tenant.orders.show', [tenant('id'), $order]) }}" class="flex flex-col gap-3 border-b border-stone-100 p-4 hover:bg-stone-50 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold">{{ $order->public_code }}</p>
                        <p class="text-sm text-stone-600">{{ ucfirst(str_replace('_', ' ', $order->type)) }} · {{ $order->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ App\Models\Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status) }}</span>
                        <span class="font-semibold">{{ $order->formattedTotal() }}</span>
                    </div>
                </a>
            @empty
                <div class="p-6 text-sm text-stone-600">No orders yet.</div>
            @endforelse
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
