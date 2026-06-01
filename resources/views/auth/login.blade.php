@php
    $selectedDemoEmail = request()->query('demo');
    $selectedDemoEmail = in_array($selectedDemoEmail, array_column($demoAccounts, 'email'), true) ? $selectedDemoEmail : null;
@endphp

<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ fillDemo(email) { $refs.email.value = email; $refs.password.value = 'password'; $refs.email.dispatchEvent(new Event('input')); $refs.password.dispatchEvent(new Event('input')); $refs.email.focus(); } }" @if ($selectedDemoEmail) x-init="$nextTick(() => fillDemo(@js($selectedDemoEmail)))" @endif>
        @csrf

        <div class="mb-5 rounded-lg border border-brand-200 bg-brand-50 p-3 dark:border-brand-900/70 dark:bg-brand-950/30">
            <p class="text-sm font-semibold text-brand-900 dark:text-brand-100">{{ __('Auto fill') }}</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ($demoAccounts as $account)
                    <button type="button" x-on:click="fillDemo(@js($account['email']))" class="flex items-center justify-between gap-3 rounded-md border border-brand-200 bg-white px-3 py-2 text-left text-sm font-semibold text-zinc-800 transition hover:border-brand-300 hover:bg-brand-50 app-focus dark:border-brand-900/70 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-brand-950/40">
                        <span>{{ $account['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" required />
            <x-text-input id="email" x-ref="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" required />

            <x-text-input id="password" x-ref="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="gap-2">
                <x-icon name="log-in" class="h-4 w-4" />
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
