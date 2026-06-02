@php
    $stats = [
        ['value' => '5', 'label' => __('role-aware workspaces')],
        ['value' => '4', 'label' => __('supported languages')],
        ['value' => '30', 'label' => __('trial days included')],
        ['value' => '24/7', 'label' => __('order visibility')],
    ];

    $featureCards = [
        [
            'icon' => 'utensils',
            'eyebrow' => __('Digital menu'),
            'title' => __('A polished storefront for every table, pickup, and delivery order'),
            'body' => __('Publish dishes, categories, and checkout flows that feel familiar to guests while staying connected to staff operations.'),
        ],
        [
            'icon' => 'chef-hat',
            'eyebrow' => __('Kitchen flow'),
            'title' => __('A focused kitchen screen that keeps tickets moving'),
            'body' => __('Kitchen teams can see the queue, start preparation, and mark orders ready without sorting through admin-only tools.'),
        ],
        [
            'icon' => 'truck',
            'eyebrow' => __('Delivery dispatch'),
            'title' => __('Drivers get the next handoff without opening the whole back office'),
            'body' => __('Assign deliveries, track pickups, and close completed orders from a role-protected driver workspace.'),
        ],
        [
            'icon' => 'chart-bar',
            'eyebrow' => __('Admin cockpit'),
            'title' => __('Admins see the business without slowing down service'),
            'body' => __('Plans, tenants, applications, orders, billing history, staff limits, and alerts stay organized in one SaaS control layer.'),
        ],
    ];

    $workflow = [
        ['icon' => 'building-store', 'title' => __('Restaurant applies'), 'body' => __('Collect the restaurant profile, plan, admin details, and workspace slug. Super users approve when everything is ready.')],
        ['icon' => 'shield-check', 'title' => __('Tenant workspace opens'), 'body' => __('Each restaurant gets isolated data, guarded routes, and a workspace that matches its current subscription state.')],
        ['icon' => 'receipt', 'title' => __('Orders move by role'), 'body' => __('Guests order from the menu, admins manage the floor, kitchen sees tickets, and drivers see delivery work.')] ,
        ['icon' => 'languages', 'title' => __('Teams work in their language'), 'body' => __('English, French, Spanish, and Arabic RTL are available from a fast segmented language toggle.')] ,
    ];

    $planDescriptions = [
        'starter' => __('For small teams launching online ordering with a clean kitchen queue.'),
        'pro' => __('For restaurants that need delivery dispatch, stock alerts, and stronger analytics.'),
        'business' => __('For operators running multiple branches, bigger teams, and subscription-ready growth.'),
    ];

    $footerColumns = [
        [
            'title' => __('Explore'),
            'links' => [
                ['label' => __('Features'), 'url' => '#features'],
                ['label' => __('Workflow'), 'url' => '#workflow'],
                ['label' => __('Pricing'), 'url' => '#pricing'],
            ],
        ],
        [
            'title' => __('Access'),
            'links' => [
                ['label' => __('Start onboarding'), 'url' => route('restaurants.apply')],
                ['label' => __('Platform login'), 'url' => route('login')],
            ],
        ],
    ];

    $footerHighlights = [
        ['icon' => 'shield-check', 'label' => __('Role-based dashboards')],
        ['icon' => 'qr-code', 'label' => __('Table QR ordering')],
        ['icon' => 'messages-square', 'label' => __('Real-time delivery chat')],
        ['icon' => 'languages', 'label' => __('Multilingual interface')],
    ];

@endphp

<x-app-layout>
    <section id="top" class="relative isolate overflow-hidden border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
        <img src="{{ asset('images/restaurant-dining-hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center opacity-90 dark:hidden">
        <img src="{{ asset('images/restaurant-dining-night-hero.png') }}" alt="" class="absolute inset-0 hidden h-full w-full object-cover object-center opacity-75 dark:block">
        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-white/35 dark:from-zinc-950 dark:via-zinc-950/88 dark:to-zinc-950/42"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-white/20 dark:from-zinc-950 dark:to-zinc-950/20"></div>

        <div class="relative mx-auto flex min-h-[68svh] max-w-7xl items-center px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 text-sm font-semibold uppercase text-brand-700 dark:text-brand-300">
                    <x-icon name="sparkles" class="h-4 w-4" />
                    {{ __('Restaurant operations SaaS') }}
                </p>
                <h1 class="mt-4 text-4xl font-bold text-zinc-950 sm:text-5xl lg:text-6xl dark:text-white">{{ __('RestoSmart') }}</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-700 dark:text-zinc-200">
                    {{ __('Run menus, orders, kitchen tickets, drivers, subscriptions, and multilingual tenant workspaces from one clean restaurant platform.') }}
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('restaurants.apply') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-brand-700/20 transition hover:bg-brand-800 app-focus">
                        <x-icon name="building-store" class="h-4 w-4" />
                        {{ __('Start onboarding') }}
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-5 py-3 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800">
                        <x-icon name="log-in" class="h-4 w-4" />
                        {{ __('Platform login') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-zinc-200 bg-white py-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto grid max-w-7xl gap-3 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ($stats as $stat)
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/70">
                    <p class="text-2xl font-bold text-zinc-950 dark:text-white">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="features" class="scroll-mt-20 bg-zinc-50 py-16 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase text-brand-700 dark:text-brand-300">{{ __('Built for restaurant service') }}</p>
                <h2 class="mt-3 text-3xl font-bold text-zinc-950 dark:text-white">{{ __('From dining room to delivery route, every role gets a focused view') }}</h2>
                <p class="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-300">{{ __('RestoSmart keeps the SaaS platform calm for admins while giving service teams the direct screens they need during a rush.') }}</p>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($featureCards as $feature)
                    <article class="rounded-lg border border-zinc-200 bg-white p-5 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-zinc-800 dark:bg-zinc-900/80 dark:hover:border-brand-900 dark:hover:bg-brand-950/20">
                        <span class="grid h-10 w-10 place-items-center rounded-lg border border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-900 dark:bg-brand-950/40 dark:text-brand-200">
                            <x-icon :name="$feature['icon']" class="h-5 w-5" />
                        </span>
                        <p class="mt-5 text-sm font-semibold text-brand-700 dark:text-brand-300">{{ $feature['eyebrow'] }}</p>
                        <h3 class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $feature['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="workflow" class="scroll-mt-20 border-y border-zinc-200 bg-white py-16 dark:border-zinc-800 dark:bg-zinc-900/60">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase text-brand-700 dark:text-brand-300">{{ __('How service stays organized') }}</p>
                <h2 class="mt-3 text-3xl font-bold text-zinc-950 dark:text-white">{{ __('One platform, separated work areas, fewer handoff mistakes') }}</h2>
                <p class="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-300">{{ __('Each restaurant runs inside its own protected tenant, so menus, orders, staff roles, billing, and delivery work do not bleed across locations.') }}</p>
            </div>

            <div class="grid gap-3">
                @foreach ($workflow as $index => $step)
                    <article class="flex gap-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-zinc-200 bg-white text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                            <x-icon :name="$step['icon']" class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-zinc-500 dark:text-zinc-400">{{ __('Step') }} {{ $index + 1 }}</p>
                            <h3 class="mt-1 text-base font-semibold text-zinc-950 dark:text-white">{{ $step['title'] }}</h3>
                            <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $step['body'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="pricing" class="scroll-mt-20 bg-zinc-50 py-16 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase text-brand-700 dark:text-brand-300">{{ __('Pricing') }}</p>
                    <h2 class="mt-3 text-3xl font-bold text-zinc-950 dark:text-white">{{ __('Choose the service tier that matches the floor') }}</h2>
                    <p class="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-300">{{ __('Start with a 30-day trial, then grow into delivery, analytics, and larger staff limits as the restaurant gets busier.') }}</p>
                </div>
                <a href="{{ route('restaurants.apply') }}" class="inline-flex w-fit items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800">
                    {{ __('Apply now') }}
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-10 grid gap-4 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    @php
                        $slug = data_get($plan, 'slug', str($plan->name)->slug()->toString());
                        $features = is_array(data_get($plan, 'features')) ? data_get($plan, 'features') : [];
                        $isFeatured = $slug === 'pro';
                    @endphp

                    <article @class([
                        'rounded-lg border bg-white p-6 shadow-sm dark:bg-zinc-900/80',
                        'border-brand-300 ring-2 ring-brand-700/10 dark:border-brand-800 dark:ring-brand-400/10' => $isFeatured,
                        'border-zinc-200 dark:border-zinc-800' => ! $isFeatured,
                    ])>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xl font-bold text-zinc-950 dark:text-white">{{ __(data_get($plan, 'name')) }}</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $planDescriptions[$slug] ?? __('For restaurants that want a cleaner operating rhythm.') }}</p>
                            </div>
                            @if ($isFeatured)
                                <span class="rounded-full border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700 dark:border-brand-900 dark:bg-brand-950/50 dark:text-brand-200">{{ __('Popular') }}</span>
                            @endif
                        </div>

                        <div class="mt-6 flex items-end gap-1">
                            <span class="text-4xl font-bold text-zinc-950 dark:text-white">{{ \App\Support\Money::mad(data_get($plan, 'monthly_price_cents', 0), 0) }}</span>
                            <span class="pb-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('per month') }}</span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 divide-x divide-zinc-200 border-y border-zinc-200 py-3 text-sm dark:divide-zinc-800 dark:border-zinc-800 rtl:divide-x-reverse">
                            <div class="px-3 text-zinc-700 dark:text-zinc-300">
                                <p class="font-bold">{{ data_get($plan, 'max_staff') }}</p>
                                <p>{{ __('staff seats') }}</p>
                            </div>
                            <div class="px-3 text-zinc-700 dark:text-zinc-300">
                                <p class="font-bold">{{ data_get($plan, 'max_active_orders') }}</p>
                                <p>{{ __('active orders') }}</p>
                            </div>
                        </div>

                        <ul class="mt-6 space-y-3">
                            @foreach ($features as $feature)
                                <li class="flex gap-3 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
                                    <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-700 dark:text-brand-300" />
                                    <span>{{ __($feature) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('restaurants.apply') }}" class="{{ $isFeatured ? 'bg-brand-700 text-white hover:bg-brand-800' : 'border border-zinc-300 text-zinc-800 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800' }} mt-7 inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold transition app-focus">
                            {{ __('Start trial') }}
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="border-t border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950" aria-label="{{ __('Footer') }}">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="min-w-0">
                    <a href="{{ route('home') }}" class="inline-flex max-w-full items-center gap-3 rounded-lg app-focus">
                        <x-application-logo class="h-11 w-11 shrink-0" alt="{{ __('RestoSmart') }}" />
                        <span class="min-w-0 truncate text-lg font-bold text-zinc-950 dark:text-white">{{ __('RestoSmart') }}</span>
                    </a>

                    <p class="mt-5 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                        {{ __('RestoSmart brings tenant onboarding, menu ordering, kitchen preparation, delivery dispatch, table QR flows, and multilingual access into one restaurant SaaS workspace.') }}
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($footerHighlights as $highlight)
                            <div class="flex min-w-0 items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm font-semibold text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900/70 dark:text-zinc-200">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-brand-700 dark:bg-zinc-950 dark:text-brand-300">
                                    <x-icon :name="$highlight['icon']" class="h-4 w-4" />
                                </span>
                                <span class="min-w-0 break-words">{{ $highlight['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-8 sm:grid-cols-2">
                    @foreach ($footerColumns as $column)
                        <nav aria-label="{{ $column['title'] }}">
                            <h2 class="text-sm font-bold uppercase tracking-wide text-zinc-950 dark:text-white">{{ $column['title'] }}</h2>
                            <ul class="mt-4 space-y-3">
                                @foreach ($column['links'] as $link)
                                    <li>
                                        <a href="{{ $link['url'] }}" class="inline-flex max-w-full items-center gap-2 rounded-md text-sm font-medium text-zinc-600 transition hover:text-brand-700 app-focus dark:text-zinc-300 dark:hover:text-brand-200">
                                            <span class="min-w-0 break-words">{{ $link['label'] }}</span>
                                            <x-icon name="arrow-right" class="h-3.5 w-3.5 shrink-0 rtl:rotate-180" />
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    @endforeach
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-4 border-t border-zinc-200 pt-6 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:text-zinc-400">
                <p class="break-words">&copy; {{ now()->year }} {{ __('RestoSmart') }}. {{ __('All rights reserved.') }}</p>
                <a href="#top" class="inline-flex w-fit items-center gap-2 rounded-md font-semibold text-zinc-700 transition hover:text-brand-700 app-focus dark:text-zinc-200 dark:hover:text-brand-200">
                    {{ __('Back to top') }}
                    <x-icon name="arrow-right" class="h-3.5 w-3.5 -rotate-90" />
                </a>
            </div>
        </div>
    </footer>
</x-app-layout>
