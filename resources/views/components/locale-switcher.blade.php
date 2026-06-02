@props(['compact' => false, 'showIcon' => true])

@php
    $locales = [
        'en' => ['label' => __('English'), 'short' => 'EN'],
        'fr' => ['label' => __('French'), 'short' => 'FR'],
        'es' => ['label' => __('Spanish'), 'short' => 'ES'],
        'ar' => ['label' => __('Arabic'), 'short' => 'AR'],
    ];

    $currentLocale = app()->getLocale();
    $currentLocaleData = $locales[$currentLocale] ?? $locales['en'];
@endphp

<div
    x-data="{ open: false }"
    x-on:click.outside="open = false"
    {{ $attributes->merge(['class' => 'relative inline-block text-start']) }}
>
    <button
        type="button"
        x-on:click="open = ! open"
        x-bind:aria-expanded="open.toString()"
        class="grid h-10 w-10 place-items-center rounded-lg border border-zinc-200 bg-white/90 text-sm font-bold text-zinc-800 shadow-sm backdrop-blur-sm transition hover:bg-white hover:shadow-md app-focus dark:border-zinc-700 dark:bg-transparent dark:text-zinc-200 dark:shadow-none dark:backdrop-blur-none dark:hover:bg-zinc-800"
        aria-haspopup="menu"
        aria-label="{{ __('Language') }}"
    >
        <span class="truncate">{{ $compact ? $currentLocaleData['short'] : $currentLocaleData['label'] }}</span>
    </button>

    <form
        method="POST"
        action="{{ route('locale.switch') }}"
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute end-0 z-50 mt-2 w-48 ltr:origin-top-right rtl:origin-top-left overflow-hidden rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-800 dark:bg-zinc-950"
        style="display: none;"
        role="menu"
    >
        @csrf

        @foreach ($locales as $locale => $localeData)
            <button
                type="submit"
                name="locale"
                value="{{ $locale }}"
                role="menuitem"
                @class([
                    'flex w-full items-center justify-between gap-3 rounded-md px-3 py-2 text-start text-sm font-semibold transition app-focus',
                    'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-200' => $currentLocale === $locale,
                    'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white' => $currentLocale !== $locale,
                ])
            >
                <span>{{ $localeData['label'] }}</span>
                <span class="text-xs font-bold">{{ $localeData['short'] }}</span>
            </button>
        @endforeach
    </form>
</div>
