@props(['compact' => false, 'showIcon' => true])

@php
    $locales = [
        'en' => ['label' => __('English'), 'short' => 'EN'],
        'fr' => ['label' => __('French'), 'short' => 'FR'],
        'es' => ['label' => __('Spanish'), 'short' => 'ES'],
        'ar' => ['label' => __('Arabic'), 'short' => 'AR'],
    ];

    $currentLocale = app()->getLocale();
@endphp

<form method="POST" action="{{ route('locale.switch') }}" {{ $attributes->merge(['class' => 'inline-flex h-10 items-center gap-1 rounded-lg border border-zinc-200 bg-zinc-100 p-1 dark:border-zinc-800 dark:bg-zinc-950']) }}>
    @csrf

    @if ($showIcon)
        <span class="{{ $compact ? 'hidden sm:grid' : 'grid' }} h-8 w-8 shrink-0 place-items-center rounded-md text-zinc-500 dark:text-zinc-400" title="{{ __('Language') }}">
            <x-icon name="languages" class="h-4 w-4" />
        </span>
    @endif

    @foreach ($locales as $locale => $localeData)
        <button
            type="submit"
            name="locale"
            value="{{ $locale }}"
            title="{{ $localeData['label'] }}"
            aria-label="{{ $localeData['label'] }}"
            aria-pressed="{{ $currentLocale === $locale ? 'true' : 'false' }}"
            @class([
                'grid h-8 min-w-8 place-items-center rounded-md px-2 text-xs font-bold transition app-focus',
                'bg-white text-red-700 shadow-sm dark:bg-zinc-800 dark:text-red-200' => $currentLocale === $locale,
                'text-zinc-500 hover:bg-white/70 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-white' => $currentLocale !== $locale,
            ])
        >
            {{ $localeData['short'] }}
        </button>
    @endforeach
</form>
