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
    <body class="font-sans text-zinc-950 antialiased dark:text-zinc-50">
        <div class="min-h-screen bg-zinc-50 px-4 py-6 dark:bg-zinc-950">
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-4">
                <a href="{{ $tenantId ? route('tenant.menu', $tenantId) : route('home') }}" class="flex items-center gap-3">
                    <x-application-logo class="h-10 w-10 text-brand-700 dark:text-brand-400" />
                    <span class="font-semibold">{{ $workspaceName }}</span>
                </a>

                <div class="flex items-center gap-2">
                    <x-locale-switcher compact />
                    <x-theme-switcher />
                </div>
            </div>

            <div class="mx-auto mt-8 w-full max-w-xl rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
