<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Super admin') }}</p>
            <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Paiements et Abonnements') }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 p-5 dark:border-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Liste des restaurants') }}</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                    <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
                        <tr>
                            <th class="px-5 py-3">{{ __('Restaurant') }}</th>
                            <th class="px-5 py-3">{{ __('Type d\'abonnement') }}</th>
                            <th class="px-5 py-3">{{ __('Dernier paiement') }}</th>
                            <th class="px-5 py-3">{{ __('Prochain paiement') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Historique') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($tenants as $tenant)
                            <tr x-data="{ open: false }">
                                <td class="px-5 py-4 align-top">
                                    <div class="flex items-center gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-zinc-950 dark:text-white">{{ $tenant->name }}</p>
                                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $tenant->owner_email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    @if ($tenant->plan)
                                        <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                                            {{ $tenant->plan->name }}
                                        </span>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Aucun') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-top">
                                    @php
                                        $lastPayment = $tenant->billingHistories->where('status', 'paid')->first();
                                    @endphp
                                    @if ($lastPayment && $lastPayment->paid_at)
                                        <span class="text-zinc-800 dark:text-zinc-200">{{ $lastPayment->paid_at->format('Y-m-d') }}</span>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-top">
                                    @if ($tenant->current_period_ends_at)
                                        <span class="text-zinc-800 dark:text-zinc-200">{{ $tenant->current_period_ends_at->format('Y-m-d') }}</span>
                                    @elseif ($tenant->subscription && $tenant->subscription->current_period_ends_at)
                                        <span class="text-zinc-800 dark:text-zinc-200">{{ $tenant->subscription->current_period_ends_at->format('Y-m-d') }}</span>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-top text-right">
                                    @if($tenant->billingHistories->isNotEmpty())
                                        <button @click="open = !open" class="text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition">
                                            {{ trans_choice(':count paiement|:count paiements', $tenant->billingHistories->count(), ['count' => $tenant->billingHistories->count()]) }}
                                        </button>
                                        
                                        <div x-show="open" x-collapse x-cloak class="mt-3 text-left bg-zinc-50 dark:bg-zinc-950 p-3 rounded border border-zinc-200 dark:border-zinc-800 text-xs">
                                            <ul class="space-y-2">
                                                @foreach($tenant->billingHistories as $history)
                                                    <li class="flex justify-between border-b border-zinc-200 dark:border-zinc-800 pb-1 last:border-0 last:pb-0">
                                                        <span class="text-zinc-600 dark:text-zinc-400">{{ $history->issued_at ? $history->issued_at->format('Y-m-d') : '-' }}</span>
                                                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($history->amount_cents / 100, 2) }} €</span>
                                                        <span @class([
                                                            'font-semibold',
                                                            'text-emerald-600 dark:text-emerald-400' => $history->status === 'paid',
                                                            'text-amber-600 dark:text-amber-400' => $history->status === 'pending',
                                                            'text-rose-600 dark:text-rose-400' => $history->status === 'failed',
                                                        ])>{{ ucfirst($history->status) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Aucun paiement') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-zinc-600 dark:text-zinc-300">{{ __('Aucun restaurant trouvé.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tenants->hasPages())
                <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    {{ $tenants->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
