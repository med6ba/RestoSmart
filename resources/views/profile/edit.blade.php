<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Account') }}</p>
            <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Profile') }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="space-y-6">
                <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </section>

                @if ($user->hasAnyRole('admin'))
                    <section class="rounded-lg border border-red-200 bg-white p-5 shadow-sm dark:border-red-900/60 dark:bg-zinc-900">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </section>
                @endif
            </div>

            <aside class="h-fit rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950">
                <div class="grid h-12 w-12 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                    <x-icon name="user" class="h-6 w-6" />
                </div>
                <h2 class="mt-4 text-lg font-semibold text-zinc-950 dark:text-white">{{ $user->name }}</h2>
                <p class="mt-1 break-all text-sm text-zinc-600 dark:text-zinc-300">{{ $user->email }}</p>
                <span class="mt-4 inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                    {{ __(str_replace('_', ' ', ucfirst($user->role))) }}
                </span>
            </aside>
        </div>
    </div>
</x-app-layout>
