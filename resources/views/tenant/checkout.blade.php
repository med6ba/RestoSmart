<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">Checkout</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto grid gap-6 px-4 py-8 lg:grid-cols-[1fr_320px] sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('tenant.checkout.store', tenant('id')) }}" class="rounded-lg border border-stone-200 bg-white p-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="rounded-lg border border-stone-200 p-4">
                    <input type="radio" name="type" value="delivery" class="text-red-700" checked>
                    <span class="ml-2 font-semibold">Delivery</span>
                    <p class="mt-1 text-sm text-stone-600">Driver dispatch with simulated route.</p>
                </label>
                <label class="rounded-lg border border-stone-200 p-4">
                    <input type="radio" name="type" value="click_collect" class="text-red-700">
                    <span class="ml-2 font-semibold">Click & collect</span>
                    <p class="mt-1 text-sm text-stone-600">Kitchen marks the order ready for pickup.</p>
                </label>
            </div>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />

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

            <div class="mt-5">
                <x-input-label for="delivery_address" value="Delivery address" required />
                <textarea id="delivery_address" name="delivery_address" rows="3" class="mt-1 block w-full rounded-md border-stone-300">{{ old('delivery_address', auth()->user()->default_address) }}</textarea>
                <x-input-error :messages="$errors->get('delivery_address')" class="mt-2" />
            </div>

            <div class="mt-5">
                <x-input-label for="kitchen_notes" value="Kitchen notes" />
                <textarea id="kitchen_notes" name="kitchen_notes" rows="3" class="mt-1 block w-full rounded-md border-stone-300">{{ old('kitchen_notes') }}</textarea>
                <x-input-error :messages="$errors->get('kitchen_notes')" class="mt-2" />
            </div>

            <button class="mt-6 inline-flex items-center gap-2 rounded-lg bg-red-700 px-5 py-3 text-sm font-semibold text-white app-focus hover:bg-red-800">
                <x-icon name="check-circle" class="h-4 w-4" />
                Place order
            </button>
        </form>

        <aside class="h-fit rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Order summary</h2>
            <div class="mt-4 space-y-3">
                @foreach ($cartLines as $line)
                    <div class="flex justify-between gap-4 border-t border-stone-100 pt-3 text-sm">
                        <span>{{ $line['quantity'] }} × {{ $line['item']->name }}</span>
                        <span class="font-semibold">${{ number_format($line['total_cents'] / 100, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between border-t border-stone-200 pt-3 font-semibold">
                    <span>Subtotal</span>
                    <span>${{ number_format($subtotalCents / 100, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-stone-600">
                    <span>Delivery fee</span>
                    <span>$3.00 when delivery is selected</span>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
