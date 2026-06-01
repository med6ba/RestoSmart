@props(['navItems', 'tenantId', 'homeRoute', 'logoutRoute', 'user'])

<div class="flex min-h-full flex-col">
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-zinc-200 px-5 dark:border-zinc-800">
        <a href="{{ $homeRoute }}" class="flex min-w-0 items-center gap-3">
            <x-application-logo class="h-9 w-9 shrink-0 text-red-700 dark:text-red-400" />
            <span class="truncate text-base font-semibold text-zinc-950 dark:text-zinc-50">{{ $tenantId ? tenant('name') : config('app.name', 'RestoSmart') }}</span>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
        <p class="px-3 text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ __('Workspace') }}</p>

        <nav class="mt-3 space-y-1">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['href'] }}"
                    @class([
                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition app-focus',
                        'bg-red-700 text-white shadow-sm shadow-red-700/20' => $item['active'],
                        'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => ! $item['active'],
                    ])
                >
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md border text-xs font-bold {{ $item['active'] ? 'border-red-500 bg-red-600 text-white' : 'border-zinc-200 bg-white text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400' }}">
                        <x-icon :name="$item['icon']" class="h-4 w-4" />
                    </span>
                    <span class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="space-y-4 border-t border-zinc-200 p-4 dark:border-zinc-800">
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
            <p class="truncate text-sm font-semibold text-zinc-950 dark:text-zinc-50">{{ $user->name }}</p>
            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
            <span class="mt-3 inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/50 dark:text-red-200">
                {{ __(str_replace('_', ' ', ucfirst($user->role))) }}
            </span>
        </div>

        <div class="grid gap-3">
            <div>
                <p class="mb-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ __('Language') }}</p>
                <x-locale-switcher class="w-full justify-between" />
            </div>

            <div>
                <p class="mb-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ __('Theme') }}</p>
                <x-theme-switcher segmented />
            </div>
        </div>

        <form method="POST" action="{{ $logoutRoute }}">
            @csrf
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-900">
                <x-icon name="log-out" class="h-4 w-4" />
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</div>
