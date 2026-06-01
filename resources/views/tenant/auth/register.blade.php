<x-guest-layout>
    <form method="POST" action="{{ route('tenant.register.store', tenant('id')) }}" class="space-y-5">
        @csrf

        <div>
            <h1 class="text-2xl font-bold text-stone-950">{{ __('Create client account') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('Save your delivery address and track every order live.') }}</p>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" required />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="email" :value="__('Email')" required />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="default_address" :value="__('Default delivery address')" />
            <textarea id="default_address" name="default_address" rows="3" class="mt-1 block w-full rounded-md border-stone-300">{{ old('default_address') }}</textarea>
            <x-input-error :messages="$errors->get('default_address')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="password" :value="__('Password')" required />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" required />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('tenant.login', tenant('id')) }}" class="text-sm text-stone-600 hover:text-stone-950">{{ __('Already registered?') }}</a>
            <x-primary-button class="gap-2">
                <x-icon name="user-plus" class="h-4 w-4" />
                {{ __('Create account') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
