@php
    $isRtl = app()->getLocale() === 'ar';
    $tenantId = function_exists('tenant') ? tenant('id') : null;
    $workspaceName = $tenantId ? tenant('name') : config('app.name', 'RestoSmart');
    $routeTitle = match (true) {
        request()->routeIs('tenant.login', 'login') => __('Log in'),
        request()->routeIs('tenant.register', 'register') => __('Register'),
        request()->routeIs('password.*') => __('Password'),
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

        <script>
            const storedTheme = localStorage.getItem('restosmart-theme');

            document.documentElement.classList.toggle('dark', storedTheme === 'dark');
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative min-h-screen overflow-hidden">
            {{-- Background images --}}
            <div class="absolute inset-0">
                <img
                    src="{{ asset('images/restaurant-dining-hero.png') }}"
                    alt=""
                    class="h-full w-full object-cover dark:hidden"
                />
                <img
                    src="{{ asset('images/restaurant-dining-night-hero.png') }}"
                    alt=""
                    class="hidden h-full w-full object-cover dark:block"
                />
            </div>

            {{-- Overlay for readability --}}
            <div class="absolute inset-0 bg-white/50 backdrop-blur-[3px] dark:bg-zinc-950/70"></div>

            {{-- Top bar with theme/locale switchers --}}
            <div class="relative z-20 flex items-center justify-end gap-2 px-4 pt-4 sm:px-6 sm:pt-6">
                <x-locale-switcher compact />
                <x-theme-switcher />
            </div>

            {{-- Main content --}}
            <div class="relative z-10 flex min-h-[calc(100vh-4rem)] items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
                <div class="w-full max-w-md">
                    {{-- Glassmorphism card --}}
                    <div class="rounded-2xl border border-white/30 bg-white/70 p-8 shadow-2xl shadow-black/5 backdrop-blur-2xl transition-all duration-300 sm:p-10 dark:border-zinc-700/40 dark:bg-zinc-900/70 dark:shadow-black/20">
                        {{-- Branding --}}
                        <div class="mb-8 text-center">
                            <a
                                href="{{ $tenantId ? route('tenant.menu', $tenantId) : route('home') }}"
                                class="mx-auto inline-flex items-center justify-center"
                            >
                                <x-application-logo class="h-14 w-14" />
                            </a>
                            <h1 class="mt-4 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                                RestoSmart
                            </h1>
                            <p class="mt-1.5 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                                {{ __('Smart Restaurant Management for the Next Generation.') }}
                            </p>
                        </div>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
