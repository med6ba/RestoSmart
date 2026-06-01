<x-app-layout>
    <div class="bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('status') }}</div>
            @endif
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-red-700">{{ tenant('name') }}</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight">Interactive menu</h1>
                    <p class="mt-2 text-stone-600">Choose delivery or click & collect at checkout. Kitchen and driver screens update from the same order flow.</p>
                </div>
                <div class="rounded-lg border border-stone-200 bg-stone-50 px-4 py-3">
                    <p class="text-sm text-stone-600">Cart</p>
                    <p class="text-2xl font-bold">{{ $cartCount }} items · ${{ number_format($subtotalCents / 100, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto grid gap-6 px-4 py-8 lg:grid-cols-[1fr_360px] sm:px-6 lg:px-8">
        <section class="space-y-8">
            @foreach ($categories as $category)
                <div>
                    <div class="mb-3">
                        <h2 class="text-xl font-semibold">{{ $category->name }}</h2>
                        @if ($category->description)
                            <p class="text-sm text-stone-600">{{ $category->description }}</p>
                        @endif
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($category->menuItems as $item)
                            <article class="rounded-lg border border-stone-200 bg-white p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold">{{ $item->name }}</h3>
                                        <p class="mt-1 min-h-10 text-sm text-stone-600">{{ $item->description }}</p>
                                    </div>
                                    <p class="font-bold text-red-700">{{ $item->formattedPrice() }}</p>
                                </div>
                                <form method="POST" action="{{ route('tenant.cart.add', [tenant('id'), $item]) }}" class="mt-4 flex items-end gap-2">
                                    @csrf
                                    <div class="w-20">
                                        <label class="text-xs font-semibold text-stone-600" for="qty-{{ $item->id }}">Qty</label>
                                        <input id="qty-{{ $item->id }}" name="quantity" value="1" min="1" max="20" type="number" class="mt-1 w-full rounded-md border-stone-300 text-sm">
                                    </div>
                                    <button class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white">Add</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>

        <aside class="h-fit rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Cart summary</h2>
            <div class="mt-4 space-y-3">
                @forelse ($cartLines as $line)
                    <form method="POST" action="{{ route('tenant.cart.update', [tenant('id'), $line['item']]) }}" class="border-t border-stone-100 pt-3">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $line['item']->name }}</p>
                                <p class="text-sm text-stone-600">${{ number_format($line['total_cents'] / 100, 2) }}</p>
                            </div>
                            <input name="quantity" value="{{ $line['quantity'] }}" min="0" max="20" type="number" class="w-20 rounded-md border-stone-300 text-sm">
                        </div>
                        <button class="mt-2 text-xs font-semibold text-stone-700 hover:text-stone-950">Update</button>
                    </form>
                @empty
                    <p class="text-sm text-stone-600">Your cart is empty.</p>
                @endforelse
            </div>

            <div class="mt-5 flex flex-col gap-2">
                @auth
                    @if (auth()->user()->hasAnyRole('client'))
                        <a href="{{ route('tenant.checkout', tenant('id')) }}" class="rounded-lg bg-red-700 px-4 py-3 text-center text-sm font-semibold text-white">{{ __('Checkout') }}</a>
                        <a href="{{ route('tenant.orders.index', tenant('id')) }}" class="rounded-lg border border-stone-300 px-4 py-3 text-center text-sm font-semibold">{{ __('My orders') }}</a>
                    @else
                        <a href="{{ route('tenant.dashboard', tenant('id')) }}" class="rounded-lg bg-red-700 px-4 py-3 text-center text-sm font-semibold text-white">{{ __('Go to workspace') }}</a>
                    @endif
                @else
                    <a href="{{ route('tenant.login', tenant('id')) }}" class="rounded-lg bg-red-700 px-4 py-3 text-center text-sm font-semibold text-white">{{ __('Log in to checkout') }}</a>
                    <a href="{{ route('tenant.register', tenant('id')) }}" class="rounded-lg border border-stone-300 px-4 py-3 text-center text-sm font-semibold">{{ __('Create client account') }}</a>
                @endauth
                @if ($cartCount > 0)
                    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'clear-cart')" class="w-full rounded-lg border border-stone-300 px-4 py-3 text-sm font-semibold">{{ __('Clear cart') }}</button>

                    <x-modal name="clear-cart" maxWidth="md" focusable>
                        <form method="POST" action="{{ route('tenant.cart.clear', tenant('id')) }}" class="p-6">
                            @csrf
                            @method('DELETE')
                            <h3 class="text-lg font-semibold">{{ __('Clear cart') }}</h3>
                            <p class="mt-2 text-sm text-stone-600">{{ __('This removes every item currently saved in your cart.') }}</p>
                            <div class="mt-6 flex justify-end gap-3">
                                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                <x-danger-button>{{ __('Clear cart') }}</x-danger-button>
                            </div>
                        </form>
                    </x-modal>
                @endif
            </div>
        </aside>
    </div>
</x-app-layout>
