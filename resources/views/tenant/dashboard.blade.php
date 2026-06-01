<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">{{ tenant('name') }}</p>
            <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Client dashboard') }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="status-toast mb-6">{{ session('status') }}</div>
        @endif

        <section class="grid gap-4 md:grid-cols-2">
            <a href="{{ route('tenant.menu', tenant('id')) }}" class="rounded-lg border border-zinc-200 bg-white p-5 transition hover:border-red-300 hover:bg-red-50/40 app-focus dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-red-900 dark:hover:bg-red-950/20">
                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Order channel') }}</p>
                <p class="mt-2 text-2xl font-bold">{{ __('Menu and cart') }}</p>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Browse the live menu and continue your checkout when you are ready.') }}</p>
            </a>

            <a href="{{ route('tenant.orders.index', tenant('id')) }}" class="rounded-lg border border-zinc-200 bg-white p-5 transition hover:border-red-300 hover:bg-red-50/40 app-focus dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-red-900 dark:hover:bg-red-950/20">
                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('My history') }}</p>
                <p class="mt-2 text-2xl font-bold">{{ trans_choice(':count recent order|:count recent orders', $myOrders->count(), ['count' => $myOrders->count()]) }}</p>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Track your latest orders without seeing staff-only operations.') }}</p>
            </a>
        </section>

        <section class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">{{ __('Recent orders') }}</h2>
                <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($myOrders as $order)
                        <a href="{{ route('tenant.orders.show', [tenant('id'), $order]) }}" class="flex flex-col gap-2 py-3 transition hover:text-red-700 app-focus sm:flex-row sm:items-center sm:justify-between dark:hover:text-red-300">
                            <div>
                                <p class="font-semibold">{{ $order->public_code }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst(str_replace('_', ' ', $order->type)) }} · {{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="w-fit rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ App\Models\Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status) }}</span>
                        </a>
                    @empty
                        <p class="py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ __('No orders yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <aside class="h-fit rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">{{ __('Alerts') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($notifications as $notification)
                        <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800">
                            <p class="font-semibold">{{ $notification->title }}</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $notification->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('No alerts for your role.') }}</p>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>
</x-app-layout>
