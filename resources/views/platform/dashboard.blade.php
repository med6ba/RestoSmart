@php
    $modalToShow = old('_modal');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-950 dark:text-white">{{ __('Super command center') }}</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Gérez la plateforme globale et surveillez les performances.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="status-toast mb-6">{{ session('status') }}</div>
        @endif

        <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <!-- MRR -->
            <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl transition-all group-hover:bg-emerald-500/20"></div>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('MRR Global') }}</p>
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-icon name="badge-dollar" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-950 dark:text-white">{{ \App\Support\Money::mad($stats['mrr'], 0) }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold">
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <x-icon name="check" class="h-3 w-3" />
                        +{{ $stats['mrr_trend'] }}%
                    </span>
                    <span class="text-zinc-500 dark:text-zinc-500">{{ __('vs mois dernier') }}</span>
                </div>
            </div>

            <!-- Restaurants -->
            <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-brand-500/10 blur-2xl transition-all group-hover:bg-brand-500/20"></div>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Restaurants') }}</p>
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <x-icon name="building-store" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-950 dark:text-white">{{ $stats['restaurants'] }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold">
                    <span class="inline-flex items-center gap-0.5 rounded-full {{ $stats['restaurants_trend'] > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400' }} px-2 py-0.5">
                        <x-icon name="check" class="h-3 w-3" />
                        {{ $stats['restaurants_trend'] > 0 ? '+' : '' }}{{ $stats['restaurants_trend'] }}%
                    </span>
                    <span class="text-zinc-500 dark:text-zinc-500">{{ __('nouveaux inscrits') }}</span>
                </div>
            </div>

            <!-- Active -->
            <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-blue-500/10 blur-2xl transition-all group-hover:bg-blue-500/20"></div>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Actifs') }}</p>
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <x-icon name="play" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-950 dark:text-white">{{ $stats['active'] }}</p>
                <p class="mt-3 text-xs font-medium text-zinc-500">{{ __('Restaurants en ligne') }}</p>
            </div>

            <!-- Trials -->
            <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl transition-all group-hover:bg-amber-500/20"></div>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('En essai') }}</p>
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <x-icon name="clock" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-950 dark:text-white">{{ $stats['trial'] }}</p>
                <p class="mt-3 text-xs font-medium text-zinc-500">{{ __('Abonnement non activé') }}</p>
            </div>

            <!-- Pending -->
            <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-rose-500/10 blur-2xl transition-all group-hover:bg-rose-500/20"></div>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('En attente') }}</p>
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        <x-icon name="clipboard-list" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold text-zinc-950 dark:text-white">{{ $stats['pending'] }}</p>
                <p class="mt-3 text-xs font-medium text-zinc-500">{{ __('Approbation requise') }}</p>
            </div>
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <!-- Restaurant Approvals -->
            <div class="flex flex-col rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="border-b border-zinc-100 p-5 dark:border-zinc-800/50">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Restaurant approvals') }}</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Gérez les nouvelles demandes d\'inscription à la plateforme.') }}</p>
                </div>
                <div class="flex-1 overflow-x-auto p-0">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-950/50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                <th class="px-5 py-3">{{ __('Restaurant') }}</th>
                                <th class="px-5 py-3">{{ __('Plan') }}</th>
                                <th class="px-5 py-3">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                            @forelse ($applications as $application)
                                <tr class="transition-colors hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $application->restaurant_name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $application->owner_email }} · <span class="font-medium text-brand-600 dark:text-brand-400">/{{ $application->desired_slug }}</span></p>
                                    </td>
                                    <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300 font-medium">{{ $application->plan?->name ?? 'Starter' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $application->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                            @if($application->status === 'pending')
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            @endif
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if ($application->status === 'pending')
                                            <div class="inline-flex gap-2">
                                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'approve-application-{{ $application->id }}')" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 app-focus shadow-sm shadow-emerald-500/20 transition-all">{{ __('Approve') }}</button>
                                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'reject-application-{{ $application->id }}')" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 app-focus dark:border-rose-900/50 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20 transition-all">{{ __('Reject') }}</button>
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
                                            <a class="inline-flex rounded-lg border border-zinc-300 px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 app-focus dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-all" href="{{ route('tenant.menu', $application->tenant_id) }}">{{ __('Open') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('Aucune demande en attente.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Platform Alerts -->
            <div class="flex flex-col rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="border-b border-zinc-100 p-5 dark:border-zinc-800/50 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Platform alerts') }}</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Notifications système récentes.') }}</p>
                    </div>
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        <x-icon name="bell" class="h-4 w-4" />
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-5">
                    <div class="space-y-4">
                        @forelse ($notifications as $notification)
                            <div class="flex gap-4">
                                <div class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    <x-icon name="info" class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $notification->title }}</p>
                                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">{{ $notification->body }}</p>
                                    <p class="mt-1.5 text-[10px] uppercase text-zinc-400 dark:text-zinc-500">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <x-icon name="check-circle" class="h-8 w-8 text-emerald-500 opacity-50 mb-3" />
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('No alerts yet. Everything is fine.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <!-- Tenants and Subscriptions Grid -->
        <section class="mt-8 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Tenants and subscriptions') }}</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Gérez les abonnements de tous les restaurants.') }}</p>
                </div>
                <a href="{{ route('platform.users.role', 'admin') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">{{ __('Voir tout') }} &rarr;</a>
            </div>
            
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($tenants as $tenant)
                    <article class="group relative flex flex-col justify-between rounded-xl border border-zinc-200 bg-white p-5 transition-all hover:border-brand-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-900/50">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $tenant->name }}</h3>
                                    <p class="mt-0.5 truncate text-sm text-zinc-500 dark:text-zinc-400">/{{ $tenant->id }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    {{ ucfirst($tenant->status) }}
                                </span>
                            </div>
                            
                            <div class="mt-4 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <x-icon name="package" class="h-4 w-4 shrink-0 text-zinc-400" />
                                <span class="truncate font-medium">{{ $tenant->plan?->name ?? __('Aucun plan') }}</span>
                            </div>
                        </div>
                        
                        <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'update-tenant-{{ $tenant->id }}')" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-zinc-50 px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-100 app-focus dark:bg-zinc-800/50 dark:text-zinc-300 dark:ring-zinc-700/50 dark:hover:bg-zinc-800 transition-all group-hover:bg-brand-50 group-hover:text-brand-700 group-hover:ring-brand-200 dark:group-hover:bg-brand-900/20 dark:group-hover:text-brand-300 dark:group-hover:ring-brand-800">
                            <x-icon name="settings" class="h-4 w-4" />
                            {{ __('Gérer l\'abonnement') }}
                        </button>
                    </article>

                    <x-modal name="update-tenant-{{ $tenant->id }}" :show="$modalToShow === 'update-tenant-'.$tenant->id" maxWidth="lg" focusable>
                        <form method="POST" action="{{ route('tenants.lifecycle.update', $tenant) }}" class="p-6">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="_modal" value="update-tenant-{{ $tenant->id }}">
                            <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Update subscription') }}</h3>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $tenant->name }} · /{{ $tenant->id }}</p>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                <label class="grid gap-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ __('Status') }}
                                    <select name="status" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                                        @foreach (['trial', 'active', 'expired', 'suspended'] as $status)
                                            <option value="{{ $status }}" @selected(old('status', $tenant->status) === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="grid gap-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ __('Plan') }}
                                    <select name="plan_id" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}" @selected((int) old('plan_id', $tenant->plan_id) === $plan->id)>{{ $plan->name }} · {{ $plan->formattedPrice() }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                            </div>
                        </form>
                    </x-modal>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
