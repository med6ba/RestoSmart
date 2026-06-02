@props(['segmented' => false])

@if ($segmented)
    <div x-data="themeSwitcher()" {{ $attributes->merge(['class' => 'grid grid-cols-2 rounded-lg border border-zinc-200 bg-zinc-100 p-1 dark:border-zinc-800 dark:bg-zinc-950']) }}>
        <button
            type="button"
            @click="setTheme('light')"
            :aria-pressed="theme === 'light'"
            :class="theme === 'light' ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400'"
            class="inline-flex items-center justify-center gap-2 rounded-md px-3 py-2 text-sm font-semibold transition app-focus"
        >
            <x-icon name="sun" class="h-4 w-4" />
            <span>{{ __('Light') }}</span>
        </button>

        <button
            type="button"
            @click="setTheme('dark')"
            :aria-pressed="theme === 'dark'"
            :class="theme === 'dark' ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400'"
            class="inline-flex items-center justify-center gap-2 rounded-md px-3 py-2 text-sm font-semibold transition app-focus"
        >
            <x-icon name="moon" class="h-4 w-4" />
            <span>{{ __('Dark') }}</span>
        </button>
    </div>
@else
    <div x-data="themeSwitcher()" {{ $attributes->merge(['class' => 'inline-flex']) }}>
        <button
            type="button"
            @click="setTheme(theme === 'dark' ? 'light' : 'dark')"
            class="grid h-10 w-10 place-items-center rounded-lg border border-zinc-200 bg-white/90 text-zinc-800 shadow-sm backdrop-blur-sm transition hover:bg-white hover:shadow-md app-focus dark:border-zinc-700 dark:bg-transparent dark:text-zinc-200 dark:shadow-none dark:backdrop-blur-none dark:hover:bg-zinc-800"
            aria-label="{{ __('Toggle theme') }}"
            title="{{ __('Toggle theme') }}"
        >
            <x-icon name="moon" x-show="theme === 'light'" class="h-4 w-4" />
            <x-icon name="sun" x-show="theme === 'dark'" x-cloak class="h-4 w-4" />
        </button>
    </div>
@endif
