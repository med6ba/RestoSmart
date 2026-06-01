@php
    $isRtl = app()->getLocale() === 'ar';
    $tenantId = function_exists('tenant') ? tenant('id') : null;
    $user = Auth::user();
    $workspaceName = $tenantId ? tenant('name') : config('app.name', 'RestoSmart');
    $routeTitle = match (true) {
        request()->routeIs('tenant.admin') => __('Restaurant admin dashboard'),
        request()->routeIs('tenant.menu') => __('Interactive menu'),
        request()->routeIs('tenant.checkout') => __('Checkout'),
        request()->routeIs('tenant.orders.index') => __('My orders'),
        request()->routeIs('tenant.orders.show') => __('Order details'),
        request()->routeIs('tenant.kitchen') => __('Kitchen display system'),
        request()->routeIs('tenant.driver') => __('Driver mobile dashboard'),
        request()->routeIs('tenant.settings', 'settings') => __('Settings'),
        request()->routeIs('tenant.restobot') => __('RestoBot'),
        request()->routeIs('restaurants.apply') => __('Restaurant onboarding'),
        request()->routeIs('dashboard') => __('Dashboard'),
        request()->routeIs('profile.*') => __('Profile'),
        default => null,
    };
    $browserTitle = $routeTitle ? $routeTitle.' · '.$workspaceName : $workspaceName;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $browserTitle }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo-light.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo-light.png') }}">
        @auth
            @if ($tenantId)
                <meta name="tenant-id" content="{{ $tenantId }}">
            @endif
            <meta name="user-role" content="{{ $user->role }}">
        @endauth

        <script>
            const storedTheme = localStorage.getItem('restosmart-theme');

            document.documentElement.classList.toggle('dark', storedTheme === 'dark');
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div
            class="min-h-screen bg-zinc-50 text-zinc-950 dark:bg-zinc-950 dark:text-zinc-50"
            x-data="sidebarShell()"
            x-init="init()"
            x-bind:style="'--sidebar-shell-width: ' + effectiveWidth() + 'px'"
        >
            @auth
                @include('layouts.sidebar')

                <div class="min-h-screen pb-20 transition-[padding] duration-200 lg:pb-0 lg:ps-[var(--sidebar-shell-width)]">
                    <header class="sticky top-0 z-20 border-b border-zinc-200 bg-zinc-50/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90 lg:hidden">
                        <div class="flex h-16 items-center justify-between px-4">
                            <button type="button" @click="sidebarOpen = true" class="grid h-10 w-10 place-items-center rounded-lg text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 app-focus dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white" aria-label="{{ __('Open sidebar') }}">
                                <x-icon name="menu" class="h-5 w-5" />
                            </button>

                            <a href="{{ $tenantId ? route('tenant.menu', $tenantId) : route('home') }}" class="flex items-center gap-2 font-semibold">
                                <x-application-logo class="h-8 w-8 text-brand-700 dark:text-brand-400" />
                                <span>{{ $tenantId ? tenant('name') : config('app.name', 'RestoSmart') }}</span>
                            </a>
                        </div>
                    </header>

                    @isset($header)
                        <section class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/70">
                            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </section>
                    @endisset

                    <main>
                        {{ $slot }}
                    </main>
                </div>
            @else
                <header class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                        <a href="{{ $tenantId ? route('tenant.menu', $tenantId) : route('home') }}" class="flex min-w-0 items-center gap-3">
                            <x-application-logo class="h-9 w-9 shrink-0 text-brand-700 dark:text-brand-400" />
                            <span class="truncate font-semibold">{{ $tenantId ? tenant('name') : config('app.name', 'RestoSmart') }}</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <x-locale-switcher compact />
                            <x-theme-switcher />
                            <a href="{{ $tenantId ? route('tenant.login', $tenantId) : route('login') }}" class="hidden items-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800 app-focus sm:inline-flex">
                                <x-icon name="log-in" class="h-4 w-4" />
                                {{ __('Log in') }}
                            </a>
                        </div>
                    </div>
                </header>

                <main>
                    {{ $slot }}
                </main>
            @endauth
        </div>
    </body>
</html>
