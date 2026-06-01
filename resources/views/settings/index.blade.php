<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Settings') }}</p>
            <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Theme and language') }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Appearance') }}</p>
                <h2 class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Choose your workspace style') }}</h2>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ __('Theme and language are saved for your session and applied across the SaaS workspace.') }}</p>

                <div class="mt-6 grid gap-5">
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-200">
                                <x-icon name="sun" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="font-semibold text-zinc-950 dark:text-white">{{ __('Theme') }}</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Switch between light and dark mode.') }}</p>
                            </div>
                        </div>
                        <x-theme-switcher segmented />
                    </div>

                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-200">
                                <x-icon name="languages" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="font-semibold text-zinc-950 dark:text-white">{{ __('Language') }}</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Use one language consistently across the interface.') }}</p>
                            </div>
                        </div>
                        <x-locale-switcher class="w-full justify-between" />
                    </div>
                </div>
            </section>

            <aside class="h-fit rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950">
                <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Account') }}</p>
                <h2 class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ auth()->user()->name }}</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ auth()->user()->email }}</p>
                <span class="mt-4 inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                    {{ __(str_replace('_', ' ', ucfirst(auth()->user()->role))) }}
                </span>
            </aside>
        </div>
    </div>
</x-app-layout>
