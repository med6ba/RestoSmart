@php
    $tenantId = function_exists('tenant') ? tenant('id') : null;
    $user = Auth::user();
    $homeRoute = $tenantId ? route('tenant.menu', $tenantId) : route('home');
    $logoutRoute = $tenantId ? route('tenant.logout', $tenantId) : route('logout');
    $navItems = [];

    $pushNav = function (string $label, string $href, array|string $active, string $icon) use (&$navItems) {
        $navItems[] = [
            'label' => $label,
            'href' => $href,
            'active' => collect((array) $active)->contains(fn ($pattern) => request()->routeIs($pattern)),
            'icon' => $icon,
        ];
    };

    if ($tenantId) {
        if ($user->hasAnyRole('client')) {
            $pushNav(__('Menu'), route('tenant.menu', $tenantId), 'tenant.menu', 'utensils');
            $pushNav(__('Dashboard'), route('tenant.dashboard', $tenantId), 'tenant.dashboard', 'layout-dashboard');
            $pushNav(__('My orders'), route('tenant.orders.index', $tenantId), 'tenant.orders.*', 'receipt');
        }

        if ($user->hasAnyRole('admin')) {
            $pushNav(__('Admin'), route('tenant.admin', $tenantId), 'tenant.admin', 'settings');
            $pushNav(__('Public menu'), route('tenant.menu', $tenantId), 'tenant.menu', 'utensils');
        }

        if ($user->hasAnyRole('kitchen')) {
            $pushNav(__('Kitchen'), route('tenant.kitchen', $tenantId), 'tenant.kitchen', 'chef-hat');
        }

        if ($user->hasAnyRole('driver')) {
            $pushNav(__('Driver'), route('tenant.driver', $tenantId), 'tenant.driver', 'truck');
        }
    } else {
        $pushNav(__('Dashboard'), route('dashboard'), 'dashboard', 'layout-dashboard');

        if ($user->hasAnyRole('admin')) {
            $pushNav(__('Apply for a restaurant'), route('restaurants.apply'), 'restaurants.apply', 'building-store');
        }

        $pushNav(__('Profile'), route('profile.edit'), 'profile.*', 'user');
    }
@endphp

<div x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" x-cloak>
    <div class="fixed inset-0 bg-zinc-950/60" @click="sidebarOpen = false"></div>
    <aside
        class="fixed inset-y-0 start-0 z-50 flex w-72 max-w-[calc(100vw-2rem)] flex-col border-e border-zinc-200 bg-zinc-50 shadow-xl dark:border-zinc-800 dark:bg-zinc-950"
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full rtl:translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full rtl:translate-x-full"
    >
        <div class="flex justify-end px-4 pt-4">
            <button type="button" @click="sidebarOpen = false" class="grid h-10 w-10 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950 app-focus dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-white" aria-label="{{ __('Close sidebar') }}">
                <x-icon name="x" class="h-5 w-5" />
            </button>
        </div>

        @include('layouts.sidebar-content', compact('navItems', 'tenantId', 'homeRoute', 'logoutRoute', 'user'))
    </aside>
</div>

<aside class="fixed inset-y-0 start-0 z-30 hidden w-72 flex-col border-e border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 lg:flex">
    @include('layouts.sidebar-content', compact('navItems', 'tenantId', 'homeRoute', 'logoutRoute', 'user'))
</aside>
