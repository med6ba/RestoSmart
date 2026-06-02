<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Super admin') }}</p>
            <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Users') }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="status-toast mb-6">{{ session('status') }}</div>
        @endif

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($roles as $role => $roleMeta)
                <a
                    href="{{ route('platform.users.role', $role) }}"
                    @class([
                        'flex items-center justify-between gap-3 rounded-lg border p-4 transition app-focus',
                        'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-900/60 dark:bg-brand-950/40 dark:text-brand-200' => $selectedRole === $role,
                        'border-zinc-200 bg-white text-zinc-700 hover:border-brand-200 hover:bg-brand-50/60 hover:text-brand-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-brand-900 dark:hover:bg-brand-950/30 dark:hover:text-brand-200' => $selectedRole !== $role,
                    ])
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-white text-brand-700 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-950 dark:text-brand-200 dark:ring-zinc-800">
                            <x-icon :name="$roleMeta['icon']" class="h-5 w-5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate font-semibold">{{ __($roleMeta['label']) }}</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ trans_choice(':count user|:count users', $roleMeta['count'], ['count' => $roleMeta['count']]) }}</span>
                        </span>
                    </span>
                </a>
            @endforeach
        </section>

        <section class="mt-6 rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 p-5 dark:border-zinc-800">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __($selectedRoleMeta['label']) }}</p>
                        <h2 class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ __('User list') }}</h2>
                    </div>

                    <form method="GET" action="{{ route('platform.users.role', $selectedRole) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[220px_160px_180px_auto]">
                        <label class="grid gap-1 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            {{ __('Search') }}
                            <input name="q" value="{{ $filters['q'] ?? '' }}" type="search" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700" placeholder="{{ __('Name, email, phone') }}">
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            {{ __('Status') }}
                            <select name="status" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700">
                                <option value="">{{ __('All') }}</option>
                                @foreach (['active', 'inactive', 'suspended'] as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __(ucfirst($status)) }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            {{ __('Restaurant') }}
                            <select name="tenant_id" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700">
                                <option value="">{{ __('All') }}</option>
                                <option value="none" @selected(($filters['tenant_id'] ?? '') === 'none')>{{ __('Without restaurant') }}</option>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" @selected(($filters['tenant_id'] ?? '') === $tenant->id)>{{ $tenant->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="flex items-end gap-2">
                            <x-primary-button class="h-10 justify-center gap-2">
                                <x-icon name="check" class="h-4 w-4" />
                                {{ __('Filter') }}
                            </x-primary-button>
                            <a href="{{ route('platform.users.role', $selectedRole) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-300 px-3 text-sm font-semibold text-zinc-700 hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">{{ __('Reset') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                    <thead class="bg-zinc-50 text-start text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
                        <tr>
                            <th class="px-5 py-3">{{ __('User') }}</th>
                            <th class="px-5 py-3">{{ __('Restaurant') }}</th>
                            <th class="px-5 py-3">{{ __('Status') }}</th>
                            <th class="px-5 py-3">{{ __('Created on') }}</th>
                            <th class="px-5 py-3 text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($users as $managedUser)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                                            <x-icon :name="$selectedRoleMeta['icon']" class="h-5 w-5" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-zinc-950 dark:text-white">{{ $managedUser->name }}</p>
                                            <p class="truncate text-zinc-600 dark:text-zinc-300">{{ $managedUser->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($managedUser->tenant)
                                        <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $managedUser->tenant->name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">/{{ $managedUser->tenant_id }}</p>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Platform') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' => $managedUser->status === 'active',
                                        'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-200' => $managedUser->status !== 'active',
                                    ])>{{ __(ucfirst($managedUser->status)) }}</span>
                                </td>
                                <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $managedUser->created_at?->format('Y-m-d') }}</td>
                                <td class="px-5 py-4 text-end">
                                    @if ($managedUser->is(auth()->user()))
                                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ __('Current account') }}</span>
                                    @else
                                        <form method="POST" action="{{ route('platform.users.impersonate', $managedUser) }}" class="inline-flex">
                                            @csrf
                                            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-700 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-800 app-focus">
                                                <x-icon name="log-in" class="h-4 w-4" />
                                                {{ __('Enter as') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-zinc-600 dark:text-zinc-300">{{ __('No user found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
