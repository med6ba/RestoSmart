@php
    $selectedType = $selectedType ?? old('type', 'delivery');
    $initialTableToken = $initialTableToken ?? old('restaurant_table_token', '');

    if ($selectedType === 'local' && ! $hasActiveTables) {
        $selectedType = 'delivery';
        $initialTableToken = '';
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">{{ __('Checkout') }}</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto grid gap-6 px-4 py-8 lg:grid-cols-[1fr_320px] sm:px-6 lg:px-8" data-realtime-scope="tables,menu">
        <form
            id="checkout-form"
            method="POST"
            action="{{ route('tenant.checkout.store', tenant('id')) }}"
            class="rounded-lg border border-stone-200 bg-white p-5"
            x-data="checkoutFlow(@js($selectedType), @js($initialTableToken), @js([
                'scanned' => __('Table QR scanned.'),
                'scanning' => __('Scanning table QR...'),
                'unsupported' => __('QR scanning is not available in this browser.'),
                'insecure' => __('Camera scanning requires HTTPS or localhost. Open this restaurant with a secure URL, or enter the table token manually.'),
                'camera' => __('Camera access was not available.'),
                'unreadable' => __('The QR code could not be read.'),
                'notTable' => __('This QR code is not a table QR.'),
                'notRegistered' => __('This table QR is not registered for this restaurant.'),
                'validationFailed' => __('The table QR could not be validated. Please try again.'),
                'tableScanned' => __('Table :table scanned.'),
            ]))"
            x-init="setValidateUrl(@js(route('tenant.checkout.table-qr', tenant('id'))))"
            x-on:submit="stopScanner()"
        >
            @csrf

            <div class="grid gap-4 lg:grid-cols-3">
                <label class="rounded-lg border border-stone-200 p-4" :class="type === 'local' ? 'border-brand-500 bg-brand-50' : ''">
                    <input type="radio" name="type" value="local" class="text-brand-700" @change="setType('local')" @checked($selectedType === 'local') @disabled(! $hasActiveTables)>
                    <span class="ml-2 font-semibold">{{ __('Local') }}</span>
                    <p class="mt-1 text-sm text-stone-600">{{ __('Dine-in table QR scan.') }}</p>
                </label>
                <label class="rounded-lg border border-stone-200 p-4" :class="type === 'takeaway' ? 'border-brand-500 bg-brand-50' : ''">
                    <input type="radio" name="type" value="takeaway" class="text-brand-700" @change="setType('takeaway')" @checked($selectedType === 'takeaway')>
                    <span class="ml-2 font-semibold">{{ __('Takeaway') }}</span>
                    <p class="mt-1 text-sm text-stone-600">{{ __('Pickup after the kitchen marks it ready.') }}</p>
                </label>
                <label class="rounded-lg border border-stone-200 p-4" :class="type === 'delivery' ? 'border-brand-500 bg-brand-50' : ''">
                    <input type="radio" name="type" value="delivery" class="text-brand-700" @change="setType('delivery')" @checked($selectedType === 'delivery')>
                    <span class="ml-2 font-semibold">{{ __('Delivery') }}</span>
                    <p class="mt-1 text-sm text-stone-600">{{ __('Driver dispatch with delivery status.') }}</p>
                </label>
            </div>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />

            <input type="hidden" name="restaurant_table_token" x-model="tableToken">

            <div x-show="type === 'local'" x-cloak class="mt-5 rounded-lg border border-stone-200 bg-stone-50 p-4">
                @if ($hasActiveTables)
                    <div class="grid gap-4 md:grid-cols-[260px_1fr]">
                        <div class="relative grid aspect-square place-items-center overflow-hidden rounded-lg bg-stone-950 text-white">
                            <video x-ref="tableVideo" x-show="scanning" playsinline muted class="h-full w-full object-cover"></video>
                            <div x-show="! scanning" class="grid place-items-center gap-3 text-center">
                                <x-icon name="qr-code" class="h-12 w-12" />
                                <span class="text-sm font-semibold">{{ __('Table QR scanner') }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col justify-center">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="startScanner()" class="inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white app-focus hover:bg-brand-800">
                                    <x-icon name="qr-code" class="h-4 w-4" />
                                    {{ __('Scan table QR') }}
                                </button>
                                <button type="button" x-show="scanning" @click="stopScanner()" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 app-focus">
                                    {{ __('Stop') }}
                                </button>
                            </div>

                            <p x-show="scanStatus" x-text="scanStatus" class="mt-3 text-sm font-semibold text-brand-700"></p>
                            <p x-show="scanError" x-text="scanError" class="mt-3 text-sm font-semibold text-amber-700"></p>

                            <div class="mt-4">
                                <x-input-label for="restaurant_table_manual" value="{{ __('Scanned table token') }}" required />
                                <input id="restaurant_table_manual" type="text" x-model="tableToken" x-on:input="tableValidated = false; scanStatus = ''; scanError = ''" x-on:input.debounce.500ms="validateManualTableToken()" class="mt-1 block w-full rounded-md border-stone-300 text-sm" placeholder="{{ __('Scan or enter the table QR token') }}">
                                <x-input-error :messages="$errors->get('restaurant_table_token')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3 text-sm text-amber-800">
                        <x-icon name="qr-code" class="h-5 w-5" />
                        <span>{{ __('No available dining tables right now.') }}</span>
                    </div>
                @endif
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="customer_name" value="{{ __('Name') }}" required />
                    <x-text-input id="customer_name" name="customer_name" class="mt-1 block w-full" :value="old('customer_name', auth()->user()->name)" required />
                    <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="customer_phone" value="{{ __('Phone') }}" required />
                    <x-text-input id="customer_phone" name="customer_phone" class="mt-1 block w-full" :value="old('customer_phone', auth()->user()->phone)" required />
                    <x-input-error :messages="$errors->get('customer_phone')" class="mt-2" />
                </div>
            </div>

            <div class="mt-5" x-show="type === 'delivery'" x-cloak>
                <x-input-label for="delivery_address" value="{{ __('Delivery address') }}" required />
                <textarea id="delivery_address" name="delivery_address" rows="3" class="mt-1 block w-full rounded-md border-stone-300" x-bind:required="type === 'delivery'">{{ old('delivery_address', auth()->user()->default_address) }}</textarea>
                <x-input-error :messages="$errors->get('delivery_address')" class="mt-2" />
            </div>

            <div class="mt-5">
                <x-input-label for="kitchen_notes" value="{{ __('Kitchen notes') }}" />
                <textarea id="kitchen_notes" name="kitchen_notes" rows="3" class="mt-1 block w-full rounded-md border-stone-300">{{ old('kitchen_notes') }}</textarea>
                <x-input-error :messages="$errors->get('kitchen_notes')" class="mt-2" />
            </div>

            <button
                type="button"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-brand-700 px-5 py-3 text-sm font-semibold text-white app-focus hover:bg-brand-800 disabled:cursor-not-allowed disabled:bg-stone-400"
                x-bind:disabled="type === 'local' && ! tableValidated"
                x-on:click.prevent="$el.form.reportValidity() && $dispatch('open-modal', 'place-order')"
            >
                <x-icon name="check-circle" class="h-4 w-4" />
                {{ __('Place order') }}
            </button>
        </form>

        <x-modal name="place-order" maxWidth="md" focusable>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Place this order?') }}</h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('Kitchen and dispatch screens will receive the order right away.') }}</p>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button form="checkout-form" class="gap-2">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        {{ __('Confirm order') }}
                    </x-primary-button>
                </div>
            </div>
        </x-modal>

        <aside class="h-fit rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">{{ __('Order summary') }}</h2>
            <div class="mt-4 space-y-3">
                @foreach ($cartLines as $line)
                    <div class="flex justify-between gap-4 border-t border-stone-100 pt-3 text-sm">
                        <span>{{ $line['quantity'] }} x {{ $line['item']->name }}</span>
                        <span class="font-semibold">{{ \App\Support\Money::mad($line['total_cents']) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between border-t border-stone-200 pt-3 font-semibold">
                    <span>{{ __('Subtotal') }}</span>
                    <span>{{ \App\Support\Money::mad($subtotalCents) }}</span>
                </div>
                <div class="flex justify-between text-sm text-stone-600">
                    <span>{{ __('Delivery fee') }}</span>
                    <span>{{ __(':amount for delivery', ['amount' => \App\Support\Money::mad(300)]) }}</span>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
