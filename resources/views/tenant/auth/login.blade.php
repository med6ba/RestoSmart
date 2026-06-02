@php
    $selectedDemoEmail = request()->query('demo');
    $selectedDemoEmail = in_array($selectedDemoEmail, array_column($demoAccounts, 'email'), true) ? $selectedDemoEmail : null;
@endphp

<x-auth-layout>
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form
        method="POST"
        action="{{ route('tenant.login.store', tenant('id')) }}"
        class="space-y-5"
        x-data="{
            fillDemo(email) {
                $refs.email.value = email;
                $refs.password.value = 'password';
                $refs.email.dispatchEvent(new Event('input'));
                $refs.password.dispatchEvent(new Event('input'));
                $refs.email.focus();
            }
        }"
        @if ($selectedDemoEmail)
            x-init="$nextTick(() => fillDemo(@js($selectedDemoEmail)))"
        @endif
    >
        @csrf

        <div class="text-center">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                {{ __('Restaurant Access') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Sign in to manage :name.', ['name' => tenant('name') ?? __('your restaurant')]) }}
            </p>
        </div>

        @if ($demoAccounts !== [])
            <div class="rounded-xl border border-brand-200/60 bg-brand-50/80 p-4 dark:border-brand-900/50 dark:bg-brand-950/30">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">
                    {{ __('Quick fill') }}
                </p>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($demoAccounts as $account)
                        <button
                            type="button"
                            x-on:click="fillDemo(@js($account['email']))"
                            class="flex items-center gap-2 rounded-lg border border-brand-200/80 bg-white/80 px-3 py-2.5 text-left text-sm font-semibold text-zinc-800 shadow-sm transition-all duration-200 hover:border-brand-300 hover:bg-white hover:shadow-md hover:shadow-brand-500/10 app-focus dark:border-brand-900/60 dark:bg-zinc-900/80 dark:text-zinc-100 dark:hover:border-brand-700 dark:hover:bg-zinc-900"
                        >
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                                {{ substr($account['label'], 0, 1) }}
                            </span>
                            <span>{{ $account['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <x-input-label for="email" :value="__('Email')" required />
            <x-text-input
                id="email"
                x-ref="email"
                class="mt-1.5 block w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" required />
            <x-text-input
                id="password"
                x-ref="password"
                class="mt-1.5 block w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a
                href="{{ route('tenant.register', tenant('id')) }}"
                class="text-sm font-medium text-brand-600 transition-colors duration-200 hover:text-brand-700 app-focus rounded-md dark:text-brand-400 dark:hover:text-brand-300"
            >
                {{ __('Create client account') }}
            </a>
            <x-primary-button class="gap-2 px-6 py-2.5 text-sm">
                <x-icon name="log-in" class="h-4 w-4" />
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-auth-layout>
