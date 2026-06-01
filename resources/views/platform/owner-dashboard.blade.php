<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Restaurant admin dashboard') }}</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="status-toast mb-6">{{ session('status') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">{{ __('Your restaurants') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($tenants as $tenant)
                        <div class="flex flex-col gap-4 border-t border-zinc-100 pt-3 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                            <div>
                                <p class="font-semibold">{{ $tenant->name }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst($tenant->status) }} {{ __('until') }} {{ optional($tenant->current_period_ends_at)->toFormattedDateString() }}</p>
                            </div>
                            <a href="{{ route('tenant.admin', $tenant->id) }}" class="inline-flex justify-center rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 app-focus">{{ __('Open') }}</a>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('No approved restaurant yet.') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">{{ __('Applications') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($applications as $application)
                        <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-semibold">{{ $application->restaurant_name }}</p>
                                <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ ucfirst($application->status) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $application->decision_note ?: __('Waiting for platform review.') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('No application yet.') }}</p>
                    @endforelse
                </div>
                <a href="{{ route('restaurants.apply') }}" class="mt-5 inline-flex rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Submit another restaurant') }}</a>
            </section>
        </div>
    </div>
</x-app-layout>
