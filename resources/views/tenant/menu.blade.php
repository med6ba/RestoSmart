@php
    $modalToShow = old('_modal');
    $isPreview = ! $canOrder;
@endphp

<x-app-layout>
    <div class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="status-toast mb-5">{{ session('status') }}</div>
            @endif

            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ tenant('name') }}</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">{{ __('Interactive menu') }}</h1>
                    <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-300">
                        {{ $isPreview ? __('Preview the public guest menu without creating an order.') : __('Build the order with a cleaner menu, custom notes, and a focused cart before checkout.') }}
                    </p>
                </div>
                @if ($canOrder)
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950">
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Cart') }}</p>
                        <p class="text-2xl font-bold text-zinc-950 dark:text-white">{{ trans_choice(':count item|:count items', $cartCount, ['count' => $cartCount]) }} · {{ \App\Support\Money::mad($subtotalCents) }}</p>
                    </div>
                @else
                    <div class="rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-brand-800 dark:border-brand-900/50 dark:bg-brand-950/30 dark:text-brand-100">
                        <p class="text-sm font-semibold">{{ __('Preview mode') }}</p>
                        <p class="text-sm">{{ __('Ordering actions are hidden for staff accounts.') }}</p>
                    </div>
                @endif
            </div>

            @if ($categories->isNotEmpty())
                <nav class="mt-6 flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('Menu categories') }}">
                    @foreach ($categories as $category)
                        <a href="#category-{{ $category->id }}" class="shrink-0 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-semibold text-zinc-700 transition hover:border-brand-300 hover:text-brand-700 app-focus dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-brand-800 dark:hover:text-brand-300">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[1fr_360px] sm:px-6 lg:px-8" data-realtime-scope="menu,tables">
        <section class="space-y-8">
            @forelse ($categories as $category)
                <section id="category-{{ $category->id }}" class="scroll-mt-24">
                    <div class="mb-3 flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $category->name }}</h2>
                            @if ($category->description)
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $category->description }}</p>
                            @endif
                        </div>
                        <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ trans_choice(':count item|:count items', $category->menuItems->count(), ['count' => $category->menuItems->count()]) }}</span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @forelse ($category->menuItems as $item)
                            <article class="group relative z-0 flex min-h-52 flex-col justify-between rounded-xl border border-zinc-200 bg-white p-2.5 transition-all duration-300 hover:z-50 hover:scale-[1.03] hover:border-brand-300 hover:shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-800">
                                @if ($item->image_url)
                                    <div class="relative w-full overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800" style="aspect-ratio: 16/9;">
                                        <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" />
                                    </div>
                                @endif
                                <div class="flex flex-1 flex-col justify-between p-3">
                                <div>
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="font-semibold text-zinc-950 dark:text-white">{{ $item->name }}</h3>
                                            <p class="mt-1 min-h-12 line-clamp-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $item->description ?: __('No description yet.') }}</p>
                                        </div>
                                        <p class="shrink-0 font-bold text-brand-700 dark:text-brand-300">{{ $item->formattedPrice() }}</p>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 dark:bg-zinc-800">{{ __(':minutes min', ['minutes' => $item->prep_minutes]) }}</span>
                                        @if ($item->stock_tracked)
                                            <span class="rounded-full bg-zinc-100 px-2.5 py-1 dark:bg-zinc-800">{{ __('Stock tracked') }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($canOrder)
                                    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-item-{{ $item->id }}')" class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800 app-focus">
                                        <x-icon name="plus" class="h-4 w-4" />
                                        {{ __('Add to order') }}
                                    </button>
                                @else
                                    <div class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                                        <x-icon name="utensils" class="h-4 w-4" />
                                        {{ __('Public preview') }}
                                    </div>
                                @endif
                                </div>
                            </article>

                            @if ($canOrder)
                                <x-modal name="add-item-{{ $item->id }}" :show="$modalToShow === 'add-item-'.$item->id" maxWidth="md" focusable>
                                    <form method="POST" action="{{ route('tenant.cart.add', [tenant('id'), $item]) }}" class="p-6">
                                        @csrf
                                        <input type="hidden" name="_modal" value="add-item-{{ $item->id }}">
                                        <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $item->name }}</h3>
                                        <p class="mt-1 text-sm font-semibold text-brand-700 dark:text-brand-300">{{ $item->formattedPrice() }}</p>
                                        <div class="mt-5 grid gap-4">
                                            <div>
                                                <x-input-label for="qty-{{ $item->id }}" value="{{ __('Quantity') }}" required />
                                                <x-text-input id="qty-{{ $item->id }}" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="20" type="number" class="mt-1 block w-full" required />
                                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="notes-{{ $item->id }}" value="{{ __('Kitchen notes') }}" />
                                                <textarea id="notes-{{ $item->id }}" name="notes" rows="3" class="mt-1 block w-full rounded-md border-zinc-300 text-sm" placeholder="{{ __('No onions, extra sauce...') }}">{{ old('notes') }}</textarea>
                                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                            </div>
                                        </div>
                                        <div class="mt-6 flex justify-end gap-3">
                                            <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                            <x-primary-button class="gap-2">
                                                <x-icon name="shopping-cart" class="h-4 w-4" />
                                                {{ __('Add to cart') }}
                                            </x-primary-button>
                                        </div>
                                    </form>
                                </x-modal>
                            @endif
                        @empty
                            <div class="rounded-lg border border-dashed border-zinc-300 p-5 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300 md:col-span-2">{{ __('No active dishes in this category yet.') }}</div>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="rounded-lg border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">{{ __('No menu categories are available yet.') }}</div>
            @endforelse
        </section>

        @if ($canOrder)
        <aside class="h-fit rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 lg:sticky lg:top-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Order') }}</p>
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Cart summary') }}</h2>
                </div>
                <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ trans_choice(':count item|:count items', $cartCount, ['count' => $cartCount]) }}</span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($cartLines as $line)
                    <article class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">{{ $line['item']->name }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Qty :quantity', ['quantity' => $line['quantity']]) }} · {{ \App\Support\Money::mad($line['total_cents']) }}</p>
                                @if ($line['notes'])
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $line['notes'] }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 gap-1">
                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-cart-line-{{ $line['item']->id }}')" class="rounded-md border border-zinc-300 px-2 py-1 text-xs font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Edit') }}</button>
                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'remove-cart-line-{{ $line['item']->id }}')" class="rounded-md border border-zinc-300 px-2 py-1 text-xs font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Remove') }}</button>
                            </div>
                        </div>
                    </article>

                    <x-modal name="edit-cart-line-{{ $line['item']->id }}" :show="$modalToShow === 'edit-cart-line-'.$line['item']->id" maxWidth="md" focusable>
                        <form method="POST" action="{{ route('tenant.cart.update', [tenant('id'), $line['item']]) }}" class="p-6">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="_modal" value="edit-cart-line-{{ $line['item']->id }}">
                            <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Edit :item', ['item' => $line['item']->name]) }}</h3>
                            <div class="mt-5 grid gap-4">
                                <div>
                                    <x-input-label for="cart-qty-{{ $line['item']->id }}" value="{{ __('Quantity') }}" required />
                                    <x-text-input id="cart-qty-{{ $line['item']->id }}" name="quantity" value="{{ old('quantity', $line['quantity']) }}" min="0" max="20" type="number" class="mt-1 block w-full" required />
                                    <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="cart-notes-{{ $line['item']->id }}" value="{{ __('Kitchen notes') }}" />
                                    <textarea id="cart-notes-{{ $line['item']->id }}" name="notes" rows="3" class="mt-1 block w-full rounded-md border-zinc-300 text-sm">{{ old('notes', $line['notes']) }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                <x-primary-button>{{ __('Update cart') }}</x-primary-button>
                            </div>
                        </form>
                    </x-modal>

                    <x-modal name="remove-cart-line-{{ $line['item']->id }}" maxWidth="md" focusable>
                        <form method="POST" action="{{ route('tenant.cart.update', [tenant('id'), $line['item']]) }}" class="p-6">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="quantity" value="0">
                            <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Remove :item?', ['item' => $line['item']->name]) }}</h3>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('This removes the item from the current cart.') }}</p>
                            <div class="mt-6 flex justify-end gap-3">
                                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                <x-danger-button>{{ __('Remove item') }}</x-danger-button>
                            </div>
                        </form>
                    </x-modal>
                @empty
                    <p class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Your cart is empty.') }}</p>
                @endforelse
            </div>

            <div class="mt-5 flex flex-col gap-2">
                @auth
                    @if (auth()->user()->hasAnyRole('client'))
                        <a href="{{ route('tenant.checkout', tenant('id')) }}" class="rounded-lg bg-brand-700 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-brand-800 app-focus">{{ __('Checkout') }}</a>
                        <a href="{{ route('tenant.orders.index', tenant('id')) }}" class="rounded-lg border border-zinc-300 px-4 py-3 text-center text-sm font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('My orders') }}</a>
                    @else
                        <a href="{{ route('tenant.dashboard', tenant('id')) }}" class="rounded-lg bg-brand-700 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-brand-800 app-focus">{{ __('Go to workspace') }}</a>
                    @endif
                @else
                    <a href="{{ route('tenant.login', tenant('id')) }}" class="rounded-lg bg-brand-700 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-brand-800 app-focus">{{ __('Log in to checkout') }}</a>
                    <a href="{{ route('tenant.register', tenant('id')) }}" class="rounded-lg border border-zinc-300 px-4 py-3 text-center text-sm font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Create client account') }}</a>
                @endauth

                @if ($cartCount > 0)
                    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'clear-cart')" class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Clear cart') }}</button>

                    <x-modal name="clear-cart" maxWidth="md" focusable>
                        <form method="POST" action="{{ route('tenant.cart.clear', tenant('id')) }}" class="p-6">
                            @csrf
                            @method('DELETE')
                            <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Clear cart') }}</h3>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('This removes every item currently saved in your cart.') }}</p>
                            <div class="mt-6 flex justify-end gap-3">
                                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                <x-danger-button>{{ __('Clear cart') }}</x-danger-button>
                            </div>
                        </form>
                    </x-modal>
                @endif
            </div>
        </aside>
        @else
        <aside class="h-fit rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 lg:sticky lg:top-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Staff preview') }}</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Menu only') }}</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ __('Admins and staff can inspect the guest menu here, but only client accounts can create orders.') }}</p>
            <a href="{{ route('tenant.dashboard', tenant('id')) }}" class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-800 app-focus">{{ __('Go to workspace') }}</a>
        </aside>
        @endif
    </div>
</x-app-layout>
