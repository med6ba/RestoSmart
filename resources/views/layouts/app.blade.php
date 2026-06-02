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
        request()->routeIs('platform.users.*') => __('Users'),
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
            <meta name="user-id" content="{{ $user->id }}">
        @endauth

        <script>
            const storedTheme = localStorage.getItem('restosmart-theme');

            document.documentElement.classList.toggle('dark', storedTheme === 'dark');
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

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

                <div class="flex min-h-screen flex-col pb-20 transition-[padding] duration-200 lg:pb-0 lg:ps-[var(--sidebar-shell-width)]">
                    <x-topbar />

                    <div class="flex flex-1 flex-col px-4 pb-4 sm:px-6 lg:pe-8 lg:ps-0 lg:pb-8">
                        <div class="relative flex flex-1">
                            <!-- Resize drag zone exactly on the card border -->
                            <div
                                class="absolute inset-y-0 -start-1 z-40 hidden w-2 cursor-ew-resize lg:block"
                                x-on:mousedown.prevent="startResize($event)"
                                x-on:dblclick.prevent="resetSidebarWidth()"
                                title="{{ __('Resize sidebar') }}"
                            ></div>
                            <div class="flex-1 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            @if (session()->has('impersonator_id'))
                                <section class="border-b border-amber-200 bg-amber-50 lg:rounded-t-2xl dark:border-amber-900/60 dark:bg-amber-950/30">
                                    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                                        <div class="flex min-w-0 items-center gap-3 text-amber-900 dark:text-amber-100">
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-100">
                                                <x-icon name="users" class="h-4 w-4" />
                                            </span>
                                            <p class="min-w-0 font-semibold">
                                                {{ __('Mode impersonation') }}:
                                                <span class="font-bold">{{ $user->name }}</span>
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('impersonation.stop') }}">
                                            @csrf
                                            <button class="inline-flex items-center justify-center gap-2 rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100 app-focus dark:border-amber-800 dark:bg-amber-950 dark:text-amber-100 dark:hover:bg-amber-900/50">
                                                <x-icon name="log-out" class="h-4 w-4" />
                                                {{ __('Retour super admin') }}
                                            </button>
                                        </form>
                                    </div>
                                </section>
                            @endif

                            <main class="h-full overflow-y-auto">
                                {{ $slot }}
                            </main>
                        </div>
                        </div>
                    </div>
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
