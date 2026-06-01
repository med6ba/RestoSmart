@php
    $tenantId = function_exists('tenant') ? tenant('id') : null;
    $user = Auth::user();
    $homeRoute = $tenantId ? route('tenant.menu', $tenantId) : route('home');
    $logoutRoute = $tenantId ? route('tenant.logout', $tenantId) : route('logout');
    $settingsRoute = $tenantId ? route('tenant.settings', $tenantId) : route('settings');
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
            $pushNav(__('RestoBot'), route('tenant.restobot', $tenantId), 'tenant.restobot*', 'sparkles');
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

    $pushNav(__('Settings'), $settingsRoute, $tenantId ? 'tenant.settings' : 'settings', 'settings');

    $bottomNavItems = collect($navItems)
        ->filter(fn ($item) => $item['active'] || ! str_contains($item['label'], __('Profile')))
        ->take(5)
        ->values();
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

        @include('layouts.sidebar-content', ['navItems' => $navItems, 'tenantId' => $tenantId, 'homeRoute' => $homeRoute, 'logoutRoute' => $logoutRoute, 'user' => $user, 'collapsible' => false])
    </aside>
</div>

<aside
    class="fixed inset-y-0 start-0 z-30 hidden flex-col border-e border-zinc-200 bg-zinc-50 transition-[width] duration-200 dark:border-zinc-800 dark:bg-zinc-950 lg:flex"
    x-bind:style="'width: ' + effectiveWidth() + 'px'"
>
    @include('layouts.sidebar-content', ['navItems' => $navItems, 'tenantId' => $tenantId, 'homeRoute' => $homeRoute, 'logoutRoute' => $logoutRoute, 'user' => $user, 'collapsible' => true])

    <button
        type="button"
        class="absolute inset-y-0 -end-2 hidden w-4 cursor-ew-resize items-center justify-center text-zinc-400 transition hover:text-brand-700 app-focus lg:flex"
        x-on:mousedown.prevent="startResize($event)"
        x-on:dblclick.prevent="resetSidebarWidth()"
        aria-label="{{ __('Resize sidebar') }}"
        title="{{ __('Resize sidebar') }}"
    >
        <span class="grid h-12 w-3 place-items-center rounded-full border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <x-icon name="grip-vertical" class="h-4 w-4" />
        </span>
    </button>
</aside>

<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-2 py-2 shadow-[0_-12px_30px_rgba(15,23,42,0.08)] backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 lg:hidden" aria-label="{{ __('Mobile navigation') }}">
    <div class="mx-auto grid max-w-xl gap-1" style="grid-template-columns: repeat({{ max(1, min(5, $bottomNavItems->count())) }}, minmax(0, 1fr));">
        @foreach ($bottomNavItems as $item)
            <a
                href="{{ $item['href'] }}"
                @class([
                    'flex min-w-0 flex-col items-center justify-center gap-1 rounded-lg px-2 py-2 text-[11px] font-semibold transition app-focus',
                    'bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-200' => $item['active'],
                    'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $item['active'],
                ])
            >
                <x-icon :name="$item['icon']" class="h-5 w-5" />
                <span class="max-w-full truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
