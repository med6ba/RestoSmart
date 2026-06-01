<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Super command center') }}</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="status-toast mb-6">{{ session('status') }}</div>
        @endif

        <section class="grid gap-4 md:grid-cols-5">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"><p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Restaurants') }}</p><p class="mt-2 text-3xl font-bold">{{ $stats['restaurants'] }}</p></div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"><p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Active') }}</p><p class="mt-2 text-3xl font-bold text-red-700 dark:text-red-300">{{ $stats['active'] }}</p></div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"><p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Trials') }}</p><p class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-300">{{ $stats['trial'] }}</p></div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"><p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Pending') }}</p><p class="mt-2 text-3xl font-bold">{{ $stats['pending'] }}</p></div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"><p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('MRR') }}</p><p class="mt-2 text-3xl font-bold">${{ number_format($stats['mrr'] / 100, 0) }}</p></div>
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">{{ __('Restaurant approvals') }}</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                        <thead>
                            <tr class="text-left text-zinc-600 dark:text-zinc-300">
                                <th class="py-2">{{ __('Restaurant') }}</th>
                                <th>{{ __('Plan') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($applications as $application)
                                <tr>
                                    <td class="py-3">
                                        <p class="font-semibold">{{ $application->restaurant_name }}</p>
                                        <p class="text-zinc-600 dark:text-zinc-300">{{ $application->owner_email }} · /{{ $application->desired_slug }}</p>
                                    </td>
                                    <td>{{ $application->plan?->name ?? 'Starter' }}</td>
                                    <td><span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ ucfirst($application->status) }}</span></td>
                                    <td class="py-3 text-right">
                                        @if ($application->status === 'pending')
                                            <div class="inline-flex gap-2">
                                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'approve-application-{{ $application->id }}')" class="rounded-lg bg-red-700 px-3 py-2 text-xs font-semibold text-white hover:bg-red-800 app-focus">{{ __('Approve') }}</button>
                                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'reject-application-{{ $application->id }}')" class="rounded-lg border border-zinc-300 px-3 py-2 text-xs font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Reject') }}</button>
                                            </div>

                                            <x-modal name="approve-application-{{ $application->id }}" maxWidth="lg" focusable>
                                                <form method="POST" action="{{ route('applications.approve', $application) }}" class="p-6">
                                                    @csrf
                                                    <h3 class="text-lg font-semibold">{{ __('Approve restaurant') }}</h3>
                                                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('Confirm the tenant workspace and choose the plan that should be applied.') }}</p>
                                                    <div class="mt-5 grid gap-4">
                                                        <label class="grid gap-1 text-sm font-semibold">
                                                            {{ __('Plan') }}
                                                            <select name="plan_id" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700">
                                                                @foreach ($plans as $plan)
                                                                    <option value="{{ $plan->id }}" @selected($application->plan_id === $plan->id)>{{ $plan->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </label>
                                                        <label class="grid gap-1 text-sm font-semibold">
                                                            {{ __('Decision note') }}
                                                            <textarea name="decision_note" rows="3" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700" placeholder="{{ __('Optional note') }}"></textarea>
                                                        </label>
                                                    </div>
                                                    <div class="mt-6 flex justify-end gap-3">
                                                        <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                                        <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                                    </div>
                                                </form>
                                            </x-modal>

                                            <x-modal name="reject-application-{{ $application->id }}" maxWidth="lg" focusable>
                                                <form method="POST" action="{{ route('applications.reject', $application) }}" class="p-6">
                                                    @csrf
                                                    <h3 class="text-lg font-semibold">{{ __('Reject application') }}</h3>
                                                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('Add a short reason so the admin can understand the decision.') }}</p>
                                                    <label class="mt-5 grid gap-1 text-sm font-semibold">
                                                        {{ __('Decision note') }}
                                                        <textarea name="decision_note" rows="4" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700" required></textarea>
                                                    </label>
                                                    <div class="mt-6 flex justify-end gap-3">
                                                        <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                                        <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                                    </div>
                                                </form>
                                            </x-modal>
                                        @elseif ($application->tenant_id)
                                            <a class="rounded-lg border border-zinc-300 px-3 py-2 text-xs font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800" href="{{ route('tenant.menu', $application->tenant_id) }}">{{ __('Open') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">{{ __('Platform alerts') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($notifications as $notification)
                        <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800">
                            <p class="font-semibold">{{ $notification->title }}</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $notification->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('No alerts yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-8 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold">{{ __('Tenants and subscriptions') }}</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($tenants as $tenant)
                    <article class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold">{{ $tenant->name }}</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">/{{ $tenant->id }} · {{ $tenant->plan?->name }}</p>
                            </div>
                            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ ucfirst($tenant->status) }}</span>
                        </div>
                        <form method="POST" action="{{ route('tenants.lifecycle.update', $tenant) }}" class="mt-4 grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700">
                                @foreach (['trial', 'active', 'expired', 'suspended'] as $status)
                                    <option value="{{ $status }}" @selected($tenant->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <select name="plan_id" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected($tenant->plan_id === $plan->id)>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-lg bg-zinc-900 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-800 app-focus dark:bg-red-700 dark:hover:bg-red-800">{{ __('Save') }}</button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
