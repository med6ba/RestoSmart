@php
    $selectedType = old('type', 'delivery');

    if ($selectedType === 'local' && ! $hasActiveTables) {
        $selectedType = 'delivery';
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">Checkout</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto grid gap-6 px-4 py-8 lg:grid-cols-[1fr_320px] sm:px-6 lg:px-8">
        <form
            method="POST"
            action="{{ route('tenant.checkout.store', tenant('id')) }}"
            class="rounded-lg border border-stone-200 bg-white p-5"
            x-data="checkoutFlow(@js($selectedType), @js(old('restaurant_table_token', '')))"
            x-on:submit="stopScanner()"
        >
            @csrf

            <div class="grid gap-4 lg:grid-cols-3">
                <label class="rounded-lg border border-stone-200 p-4" :class="type === 'local' ? 'border-red-500 bg-red-50' : ''">
                    <input type="radio" name="type" value="local" class="text-red-700" @change="setType('local')" @checked($selectedType === 'local') @disabled(! $hasActiveTables)>
                    <span class="ml-2 font-semibold">Local</span>
                    <p class="mt-1 text-sm text-stone-600">Dine-in table QR scan.</p>
                </label>
                <label class="rounded-lg border border-stone-200 p-4" :class="type === 'takeaway' ? 'border-red-500 bg-red-50' : ''">
                    <input type="radio" name="type" value="takeaway" class="text-red-700" @change="setType('takeaway')" @checked($selectedType === 'takeaway')>
                    <span class="ml-2 font-semibold">Takeaway</span>
                    <p class="mt-1 text-sm text-stone-600">Pickup after the kitchen marks it ready.</p>
                </label>
                <label class="rounded-lg border border-stone-200 p-4" :class="type === 'delivery' ? 'border-red-500 bg-red-50' : ''">
                    <input type="radio" name="type" value="delivery" class="text-red-700" @change="setType('delivery')" @checked($selectedType === 'delivery')>
                    <span class="ml-2 font-semibold">Delivery</span>
                    <p class="mt-1 text-sm text-stone-600">Driver dispatch with live tracking.</p>
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
                                <span class="text-sm font-semibold">Table QR scanner</span>
                            </div>
                        </div>
                        <div class="flex flex-col justify-center">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="startScanner()" class="inline-flex items-center gap-2 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white app-focus hover:bg-red-800">
                                    <x-icon name="qr-code" class="h-4 w-4" />
                                    Scan table QR
                                </button>
                                <button type="button" x-show="scanning" @click="stopScanner()" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 app-focus">
                                    Stop
                                </button>
                            </div>

                            <p x-show="scanStatus" x-text="scanStatus" class="mt-3 text-sm font-semibold text-red-700"></p>
                            <p x-show="scanError" x-text="scanError" class="mt-3 text-sm font-semibold text-amber-700"></p>

                            <div class="mt-4">
                                <x-input-label for="restaurant_table_manual" value="Scanned table token" required />
                                <input id="restaurant_table_manual" type="text" x-model="tableToken" class="mt-1 block w-full rounded-md border-stone-300 text-sm" placeholder="Scan or enter the table QR token">
                                <x-input-error :messages="$errors->get('restaurant_table_token')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3 text-sm text-amber-800">
                        <x-icon name="qr-code" class="h-5 w-5" />
                        <span>No active dining tables are configured yet.</span>
                    </div>
                @endif
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="customer_name" value="Name" required />
                    <x-text-input id="customer_name" name="customer_name" class="mt-1 block w-full" :value="old('customer_name', auth()->user()->name)" required />
                    <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="customer_phone" value="Phone" required />
                    <x-text-input id="customer_phone" name="customer_phone" class="mt-1 block w-full" :value="old('customer_phone', auth()->user()->phone)" required />
                    <x-input-error :messages="$errors->get('customer_phone')" class="mt-2" />
                </div>
            </div>

            <div class="mt-5" x-show="type === 'delivery'" x-cloak>
                <x-input-label for="delivery_address" value="Delivery address" required />
                <textarea id="delivery_address" name="delivery_address" rows="3" class="mt-1 block w-full rounded-md border-stone-300" x-bind:required="type === 'delivery'">{{ old('delivery_address', auth()->user()->default_address) }}</textarea>
                <x-input-error :messages="$errors->get('delivery_address')" class="mt-2" />
            </div>

            <div class="mt-5">
                <x-input-label for="kitchen_notes" value="Kitchen notes" />
                <textarea id="kitchen_notes" name="kitchen_notes" rows="3" class="mt-1 block w-full rounded-md border-stone-300">{{ old('kitchen_notes') }}</textarea>
                <x-input-error :messages="$errors->get('kitchen_notes')" class="mt-2" />
            </div>

            <button
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-red-700 px-5 py-3 text-sm font-semibold text-white app-focus hover:bg-red-800 disabled:cursor-not-allowed disabled:bg-stone-400"
                x-bind:disabled="type === 'local' && ! tableToken"
            >
                <x-icon name="check-circle" class="h-4 w-4" />
                Place order
            </button>
        </form>

        <aside class="h-fit rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Order summary</h2>
            <div class="mt-4 space-y-3">
                @foreach ($cartLines as $line)
                    <div class="flex justify-between gap-4 border-t border-stone-100 pt-3 text-sm">
                        <span>{{ $line['quantity'] }} x {{ $line['item']->name }}</span>
                        <span class="font-semibold">${{ number_format($line['total_cents'] / 100, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between border-t border-stone-200 pt-3 font-semibold">
                    <span>Subtotal</span>
                    <span>${{ number_format($subtotalCents / 100, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-stone-600">
                    <span>Delivery fee</span>
                    <span>$3.00 for delivery</span>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
