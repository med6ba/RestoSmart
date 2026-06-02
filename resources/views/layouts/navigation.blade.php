@php
    $tenantId = function_exists('tenant') ? tenant('id') : null;
    $user = Auth::user();
    $homeRoute = $tenantId ? route('tenant.menu', $tenantId) : route('home');
    $dashboardRoute = $tenantId ? route('tenant.dashboard', $tenantId) : route('dashboard');
    $profileRoute = $tenantId ? route('tenant.profile.edit', $tenantId) : route('profile.edit');
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-stone-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ $homeRoute }}" class="flex items-center gap-3">
                        <x-application-logo class="h-9 w-9 shrink-0" />
                        <span class="font-semibold tracking-tight">RestoSmart</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if ($user)
                        <x-nav-link :href="$dashboardRoute" :active="request()->routeIs('dashboard') || request()->routeIs('tenant.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        @if ($tenantId)
                            <x-nav-link :href="route('tenant.menu', $tenantId)" :active="request()->routeIs('tenant.menu')">
                                {{ __('Menu') }}
                            </x-nav-link>

                            @if ($user->hasAnyRole(['admin']))
                                <x-nav-link :href="route('tenant.admin', $tenantId)" :active="request()->routeIs('tenant.admin')">
                                    {{ __('Admin') }}
                                </x-nav-link>
                            @endif

                            @if ($user->hasAnyRole(['admin', 'kitchen']))
                                <x-nav-link :href="route('tenant.kitchen', $tenantId)" :active="request()->routeIs('tenant.kitchen')">
                                    {{ __('Kitchen') }}
                                </x-nav-link>
                            @endif

                            @if ($user->hasAnyRole(['admin', 'driver']))
                                <x-nav-link :href="route('tenant.driver', $tenantId)" :active="request()->routeIs('tenant.driver')">
                                    {{ __('Driver') }}
                                </x-nav-link>
                            @endif
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if ($user)
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-stone-600 bg-white hover:text-stone-900 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ $user->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="$profileRoute">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ $tenantId ? route('tenant.logout', $tenantId) : route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="$tenantId ? route('tenant.logout', $tenantId) : route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ $tenantId ? route('tenant.login', $tenantId) : route('login') }}" class="text-sm font-semibold text-stone-700 hover:text-stone-950">{{ __('Log in') }}</a>
                @endif
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
            @if ($user)
                <x-responsive-nav-link :href="$dashboardRoute" :active="request()->routeIs('dashboard') || request()->routeIs('tenant.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="$tenantId ? route('tenant.login', $tenantId) : route('login')">
                    {{ __('Log in') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        @if ($user)
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ $user->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ $user->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="$profileRoute">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ $tenantId ? route('tenant.logout', $tenantId) : route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="$tenantId ? route('tenant.logout', $tenantId) : route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endif
    </div>
</nav>
