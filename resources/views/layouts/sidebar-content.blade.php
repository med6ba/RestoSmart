@props(['navItems', 'tenantId', 'homeRoute', 'logoutRoute', 'user', 'collapsible' => false])

@php
    $roleLabels = [
        'super' => __('Super admin'),
        'admin' => __('Admin'),
        'client' => __('Client'),
        'kitchen' => __('Kitchen'),
        'driver' => __('Driver'),
    ];
    $userRoleLabel = $roleLabels[$user->role] ?? __(ucfirst(str_replace('_', ' ', $user->role)));
    $logoutModalName = $collapsible ? 'confirm-logout-sidebar-desktop' : 'confirm-logout-sidebar-mobile';
@endphp

<div class="flex min-h-0 flex-1 flex-col">
    <div class="flex shrink-0 flex-col gap-3 border-b border-zinc-200 p-4 dark:border-zinc-800" x-bind:class="{{ $collapsible ? "collapsed ? 'items-center px-2' : ''" : "''" }}">
        <a href="{{ $homeRoute }}" x-show="!collapsed" class="flex min-w-0 items-center gap-3 rounded-lg px-1 py-2 transition hover:bg-zinc-100 app-focus dark:hover:bg-zinc-900" @if ($collapsible) x-transition.opacity @endif>
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                <span class="text-sm font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <p class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                    <span class="shrink-0 whitespace-nowrap rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-700 dark:bg-brand-900/50 dark:text-brand-300" title="{{ $userRoleLabel }}">
                        {{ $userRoleLabel }}
                    </span>
                </div>
                <p class="mt-0.5 truncate text-xs font-medium text-zinc-500 dark:text-zinc-400" title="{{ $user->email }}">{{ $user->email }}</p>
            </div>
        </a>

        @if ($collapsible)
            <div x-show="collapsed" class="mt-2 grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-100 to-brand-200 text-brand-700 shadow-sm transition hover:scale-105 dark:from-brand-900/50 dark:to-brand-800/50 dark:text-brand-300" style="display: none;" title="{{ $user->name }} · {{ $userRoleLabel }}">
                <span class="text-sm font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            </div>
        @endif
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto scrollbar-hide px-3 py-5" x-bind:class="{{ $collapsible ? "collapsed ? 'px-2' : ''" : "''" }}">
        <p @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Workspace') }}</p>

        <nav class="space-y-2">
            @foreach ($navItems as $item)
                @if (isset($item['children']))
                    <div x-data="{ open: @js($item['active']) }">
                        <button
                            type="button"
                            x-on:click="open = ! open"
                            @class([
                                'flex w-full items-center gap-3 rounded-lg border border-transparent py-3 text-sm font-semibold transition app-focus',
                                'border-brand-200 bg-brand-50 text-brand-700 shadow-sm dark:border-brand-900/60 dark:bg-brand-950/40 dark:text-brand-200' => $item['active'],
                                'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $item['active'],
                            ])
                            x-bind:class="{{ $collapsible ? "collapsed ? 'justify-center px-2' : 'px-3 me-2'" : "'px-3 me-2'" }}"
                            x-bind:title="collapsed ? '{{ $item['label'] }}' : ''"
                            aria-label="{{ $item['label'] }}"
                        >
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md text-xs font-bold {{ $item['active'] ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-200' : 'border border-zinc-200 bg-white text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400' }}">
                                <x-icon :name="$item['icon']" class="h-4 w-4" />
                            </span>
                            <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="min-w-0 flex-1 truncate text-start">{{ $item['label'] }}</span>
                            <x-icon name="chevron-down" class="h-4 w-4 shrink-0 text-zinc-400 transition" @if ($collapsible) x-show="!collapsed" @endif x-bind:class="open ? 'rotate-180' : ''" />
                        </button>

                        <div class="mt-2 space-y-1 border-s border-zinc-200 ps-3 dark:border-zinc-800" x-show="open" @if ($collapsible) x-bind:class="collapsed ? 'hidden' : ''" @endif>
                            @foreach ($item['children'] as $child)
                                <a
                                    href="{{ $child['href'] }}"
                                    @class([
                                        'flex items-center gap-3 rounded-lg border border-transparent py-2.5 text-sm font-semibold transition app-focus',
                                        'border-brand-200 bg-brand-50 text-brand-700 shadow-sm dark:border-brand-900/60 dark:bg-brand-950/40 dark:text-brand-200' => $child['active'],
                                        'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $child['active'],
                                    ])
                                    x-bind:class="{{ $collapsible ? "collapsed ? 'px-2' : 'px-3 me-2'" : "'px-3 me-2'" }}"
                                    x-bind:title="collapsed ? '{{ $child['label'] }}' : ''"
                                >
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md text-brand-700 dark:text-brand-200">
                                        <x-icon :name="$child['icon']" class="h-4 w-4" />
                                    </span>
                                    <span class="truncate">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'flex items-center gap-3 rounded-lg border border-transparent py-3 text-sm font-semibold transition app-focus',
                            'border-brand-200 bg-brand-50 text-brand-700 shadow-sm dark:border-brand-900/60 dark:bg-brand-950/40 dark:text-brand-200' => $item['active'],
                            'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $item['active'],
                        ])
                        x-bind:class="{{ $collapsible ? "collapsed ? 'justify-center px-2' : 'px-3 me-2'" : "'px-3 me-2'" }}"
                        x-bind:title="collapsed ? '{{ $item['label'] }}' : ''"
                    >
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md text-xs font-bold {{ $item['active'] ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-200' : 'border border-zinc-200 bg-white text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400' }}">
                            <x-icon :name="$item['icon']" class="h-4 w-4" />
                        </span>
                        <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
    </div>

    <!-- Footer: Controls & Logout -->
    <div class="shrink-0 space-y-3 p-4" x-bind:class="{{ $collapsible ? "collapsed ? 'p-3' : ''" : "''" }}">
        @if ($collapsible)
            <!-- Collapse Button -->
            <button type="button" x-show="!collapsed" x-on:click="toggleSidebarLabels()" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 app-focus dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white" aria-label="{{ __('Collapse') }}" title="{{ __('Collapse') }}">
                <x-icon name="panel-left-close" class="h-4 w-4" />
                <span>{{ __('Collapse') }}</span>
            </button>

            <!-- Expand Button -->
            <button type="button" x-show="collapsed" x-on:click="toggleSidebarLabels()" class="mx-auto grid h-10 w-10 place-items-center rounded-lg border border-zinc-200 bg-white text-zinc-600 hover:text-brand-700 app-focus dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:text-brand-200" aria-label="{{ __('Expand') }}" title="{{ __('Expand') }}" style="display: none;">
                <x-icon name="panel-left-open" class="h-4 w-4" />
            </button>
        @endif

        <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', '{{ $logoutModalName }}')" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-900" x-bind:class="{{ $collapsible ? "collapsed ? 'mx-auto h-10 w-10 px-0' : ''" : "''" }}" title="{{ __('Log Out') }}">
            <x-icon name="log-out" class="h-4 w-4" />
            <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif>{{ __('Log Out') }}</span>
        </button>
    </div>

    <x-logout-confirmation :action="$logoutRoute" :name="$logoutModalName" />
</div>
