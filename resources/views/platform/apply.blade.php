<x-guest-layout>
    <form method="POST" action="{{ route('restaurants.apply.store') }}" class="space-y-5">
        @csrf

        <div>
            <h1 class="text-2xl font-bold text-stone-950">{{ __('Restaurant onboarding') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('Submit a restaurant request. Super approval provisions the tenant workspace and 30-day trial.') }}</p>
        </div>

        <div>
            <x-input-label for="restaurant_name" :value="__('Restaurant name')" required />
            <x-text-input id="restaurant_name" name="restaurant_name" type="text" class="mt-1 block w-full" :value="old('restaurant_name')" required autofocus />
            <x-input-error :messages="$errors->get('restaurant_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="desired_slug" :value="__('Workspace slug')" />
            <x-text-input id="desired_slug" name="desired_slug" type="text" class="mt-1 block w-full" :value="old('desired_slug')" placeholder="my-restaurant" />
            <x-input-error :messages="$errors->get('desired_slug')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="owner_name" :value="__('Admin name')" required />
                <x-text-input id="owner_name" name="owner_name" type="text" class="mt-1 block w-full" :value="old('owner_name')" required />
                <x-input-error :messages="$errors->get('owner_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="owner_email" :value="__('Admin email')" required />
                <x-text-input id="owner_email" name="owner_email" type="email" class="mt-1 block w-full" :value="old('owner_email')" required />
                <x-input-error :messages="$errors->get('owner_email')" class="mt-2" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="plan_id" :value="__('Plan')" />
                <select id="plan_id" name="plan_id" class="mt-1 block w-full rounded-md border-stone-300">
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} - {{ $plan->formattedPrice() }}/mo</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('plan_id')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="address" :value="__('Restaurant address')" />
            <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-stone-300">{{ old('address') }}</textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end gap-3">
            @guest
                <a href="{{ route('home') }}" class="me-auto text-sm text-stone-600 hover:text-stone-950">{{ __('Back') }}</a>
            @endguest
            <x-primary-button class="gap-2">
                <x-icon name="building-store" class="h-4 w-4" />
                {{ __('Submit application') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
