@props(['navItems', 'tenantId', 'homeRoute', 'logoutRoute', 'user', 'collapsible' => false])

<div class="flex min-h-0 flex-1 flex-col">
    <!-- Header: Logo & User Profile -->
    <div class="flex shrink-0 flex-col gap-1 p-3" x-bind:class="{{ $collapsible ? "collapsed ? 'items-center px-2' : ''" : "''" }}">
        
        <!-- Application Logo & Name -->
        <a href="{{ $homeRoute }}" class="mb-3 flex min-w-0 items-center gap-3 px-1" x-bind:class="{{ $collapsible ? "collapsed ? 'justify-center' : ''" : "''" }}">
            <x-application-logo class="h-8 w-8 shrink-0 text-brand-700 dark:text-brand-400" />
            <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="truncate text-lg font-bold tracking-tight text-zinc-950 dark:text-zinc-50">{{ $tenantId ? tenant('name') : config('app.name', 'RestoSmart') }}</span>
        </a>

        <!-- Expanded User Profile (Framed) -->
        <div x-show="!collapsed" class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-2.5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                <span class="text-sm font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <p class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                    <span class="shrink-0 rounded bg-brand-50 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-widest text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                        {{ substr($user->role, 0, 3) }}
                    </span>
                </div>
                <p class="mt-0.5 truncate text-xs font-medium text-zinc-500 dark:text-zinc-400" title="{{ $user->email }}">{{ $user->email }}</p>
            </div>
        </div>

        <!-- Collapsed User Profile -->
        @if ($collapsible)
            <div x-show="collapsed" class="mt-2 grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-100 to-brand-200 text-brand-700 shadow-sm transition hover:scale-105 dark:from-brand-900/50 dark:to-brand-800/50 dark:text-brand-300" style="display: none;" title="{{ $user->name }}">
                <span class="text-sm font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            </div>
        @endif
    </div>

    <!-- Navigation -->
    <div class="min-h-0 flex-1 overflow-y-auto scrollbar-hide ps-3 py-4 pt-6" x-bind:class="{{ $collapsible ? "collapsed ? 'px-2' : 'pe-0'" : "'pe-0'" }}">
        <p @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Workspace') }}</p>

        <nav class="mt-3 space-y-1">
            @foreach ($navItems as $item)
                @if (isset($item['children']))
                    <div x-data="{ open: @js($item['active']) }">
                        <button
                            type="button"
                            x-on:click="open = ! open"
                            @class([
                                'flex w-full items-center gap-3 py-2.5 text-sm font-semibold transition app-focus',
                                'bg-brand-500 text-white' => $item['active'],
                                'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $item['active'],
                            ])
                            x-bind:class="{{ $collapsible ? "collapsed ? 'justify-center px-2 rounded-lg' : '" . ($item['active'] ? 'rounded-s-lg rounded-e-none ps-3 pe-5 -mr-px relative z-10' : 'rounded-lg px-3 mr-3') . "'" : "'" . ($item['active'] ? 'rounded-s-lg rounded-e-none ps-3 pe-5 -mr-px relative z-10' : 'rounded-lg px-3 mr-3') . "'" }}"
                            x-bind:title="collapsed ? '{{ $item['label'] }}' : ''"
                            aria-label="{{ $item['label'] }}"
                        >
                            <span class="grid h-8 w-8 shrink-0 place-items-center text-xs font-bold {{ $item['active'] ? 'text-white' : 'rounded-md border border-zinc-200 bg-white text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400' }}">
                                <x-icon :name="$item['icon']" class="h-4 w-4" />
                            </span>
                            <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="min-w-0 flex-1 truncate text-start">{{ $item['label'] }}</span>
                            <x-icon name="chevron-down" class="h-4 w-4 shrink-0 text-zinc-400 transition" @if ($collapsible) x-show="!collapsed" @endif x-bind:class="open ? 'rotate-180' : ''" />
                        </button>

                        <div class="mt-1 space-y-1 ps-7" x-show="open" @if ($collapsible) x-bind:class="collapsed ? 'hidden' : ''" @endif>
                            @foreach ($item['children'] as $child)
                                <a
                                    href="{{ $child['href'] }}"
                                    @class([
                                        'flex items-center gap-3 py-2.5 text-sm font-semibold transition app-focus',
                                        'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-200' => $child['active'],
                                        'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $child['active'],
                                    ])
                                    x-bind:class="{{ $collapsible ? "collapsed ? 'rounded-lg px-2' : '" . ($child['active'] ? 'rounded-s-lg rounded-e-none ps-3 pe-5 -mr-px relative z-10' : 'rounded-lg px-3 mr-3') . "'" : "'" . ($child['active'] ? 'rounded-s-lg rounded-e-none ps-3 pe-5 -mr-px relative z-10' : 'rounded-lg px-3 mr-3') . "'" }}"
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
                            'flex items-center gap-3 py-2.5 text-sm font-semibold transition app-focus',
                            'bg-brand-500 text-white' => $item['active'],
                            'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $item['active'],
                        ])
                        x-bind:class="{{ $collapsible ? "collapsed ? 'justify-center px-2 rounded-lg' : '" . ($item['active'] ? 'rounded-s-lg rounded-e-none ps-3 pe-5 -mr-px relative z-10' : 'rounded-lg px-3 mr-3') . "'" : "'" . ($item['active'] ? 'rounded-s-lg rounded-e-none ps-3 pe-5 -mr-px relative z-10' : 'rounded-lg px-3 mr-3') . "'" }}"
                        x-bind:title="collapsed ? '{{ $item['label'] }}' : ''"
                    >
                        <span class="grid h-8 w-8 shrink-0 place-items-center text-xs font-bold {{ $item['active'] ? 'text-white' : 'rounded-md border border-zinc-200 bg-white text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400' }}">
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

        <form method="POST" action="{{ $logoutRoute }}">
            @csrf
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-900" x-bind:class="{{ $collapsible ? "collapsed ? 'mx-auto h-10 w-10 px-0' : ''" : "''" }}" title="{{ __('Log Out') }}">
                <x-icon name="log-out" class="h-4 w-4" />
                <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif>{{ __('Log Out') }}</span>
            </button>
        </form>
    </div>
</div>
