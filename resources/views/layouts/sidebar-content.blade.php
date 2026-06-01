@props(['navItems', 'tenantId', 'homeRoute', 'logoutRoute', 'user', 'collapsible' => false])

<div class="flex min-h-full flex-col">
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-zinc-200 px-4 dark:border-zinc-800" x-bind:class="{{ $collapsible ? "collapsed ? 'justify-center' : 'justify-between'" : "'justify-between'" }}">
        <a href="{{ $homeRoute }}" class="flex min-w-0 items-center gap-3">
            <x-application-logo class="h-9 w-9 shrink-0 text-brand-700 dark:text-brand-400" />
            <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="truncate text-base font-semibold text-zinc-950 dark:text-zinc-50">{{ $tenantId ? tenant('name') : config('app.name', 'RestoSmart') }}</span>
        </a>

        @if ($collapsible)
            <button type="button" x-show="!collapsed" x-on:click="toggleSidebarLabels()" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950 app-focus dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-white" aria-label="{{ __('Show icons only') }}" title="{{ __('Show icons only') }}">
                <x-icon name="panel-left-close" class="h-4 w-4" />
            </button>
        @endif
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
        <p @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="px-3 text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ __('Workspace') }}</p>

        <nav class="mt-3 space-y-1">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['href'] }}"
                    @class([
                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition app-focus',
                        'bg-brand-700 text-white shadow-sm shadow-brand-700/20' => $item['active'],
                        'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $item['active'],
                    ])
                    x-bind:class="{{ $collapsible ? "collapsed ? 'justify-center px-2' : ''" : "''" }}"
                    title="{{ $item['label'] }}"
                >
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md border text-xs font-bold {{ $item['active'] ? 'border-brand-500 bg-brand-600 text-white' : 'border-zinc-200 bg-white text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400' }}">
                        <x-icon :name="$item['icon']" class="h-4 w-4" />
                    </span>
                    <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="space-y-4 border-t border-zinc-200 p-4 dark:border-zinc-800">
        @if ($collapsible)
            <button type="button" x-show="collapsed" x-on:click="toggleSidebarLabels()" class="grid h-10 w-10 place-items-center rounded-lg border border-zinc-200 bg-white text-zinc-600 hover:text-brand-700 app-focus dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:text-brand-200" aria-label="{{ __('Show labels') }}" title="{{ __('Show labels') }}">
                <x-icon name="panel-left-open" class="h-4 w-4" />
            </button>
        @endif

        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950" x-bind:class="{{ $collapsible ? "collapsed ? 'grid place-items-center px-2' : ''" : "''" }}">
            <p @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="truncate text-sm font-semibold text-zinc-950 dark:text-zinc-50">{{ $user->name }}</p>
            <p @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
            <span class="mt-3 inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/50 dark:text-brand-200" @if ($collapsible) x-bind:class="collapsed ? 'mt-0 h-9 w-9 items-center justify-center rounded-lg px-0' : ''" @endif title="{{ __(str_replace('_', ' ', ucfirst($user->role))) }}">
                @if ($collapsible)
                    <span x-show="collapsed">{{ strtoupper(substr($user->role, 0, 1)) }}</span>
                @endif
                <span @if ($collapsible) x-show="!collapsed" @endif>
                {{ __(str_replace('_', ' ', ucfirst($user->role))) }}
                </span>
            </span>
        </div>

        <form method="POST" action="{{ $logoutRoute }}">
            @csrf
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-900" x-bind:class="{{ $collapsible ? "collapsed ? 'h-10 px-0' : ''" : "''" }}" title="{{ __('Log Out') }}">
                <x-icon name="log-out" class="h-4 w-4" />
                <span @if ($collapsible) x-show="!collapsed" x-transition.opacity @endif>{{ __('Log Out') }}</span>
            </button>
        </form>
    </div>
</div>
