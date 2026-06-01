<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-stone-950">Restaurant admin dashboard</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('status') }}</div>
        @endif

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-stone-200 bg-white p-4"><p class="text-sm text-stone-600">Today orders</p><p class="mt-2 text-3xl font-bold">{{ $stats['today_orders'] }}</p></div>
            <div class="rounded-lg border border-stone-200 bg-white p-4"><p class="text-sm text-stone-600">Paid revenue</p><p class="mt-2 text-3xl font-bold">${{ number_format($stats['revenue'] / 100, 0) }}</p></div>
            <div class="rounded-lg border border-stone-200 bg-white p-4"><p class="text-sm text-stone-600">Active orders</p><p class="mt-2 text-3xl font-bold text-red-700">{{ $stats['active_orders'] }}</p></div>
            <div class="rounded-lg border border-stone-200 bg-white p-4"><p class="text-sm text-stone-600">Low stock</p><p class="mt-2 text-3xl font-bold text-amber-700">{{ $stats['low_stock'] }}</p></div>
        </section>

        <section class="mt-8 rounded-lg border border-stone-200 bg-white p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Dining room tables</h2>
                    <p class="mt-1 text-sm text-stone-600">{{ $tableCount }} active tables with unique IDs and QR codes.</p>
                </div>
                <form method="POST" action="{{ route('tenant.admin.tables.configure', tenant('id')) }}" class="grid gap-2 sm:grid-cols-[160px_auto]">
                    @csrf
                    <div>
                        <x-input-label for="table_count" value="Number of tables" required />
                        <input id="table_count" name="table_count" type="number" min="1" max="200" value="{{ old('table_count', $tableCount ?: 12) }}" class="mt-1 w-full rounded-md border-stone-300 text-sm" required>
                        <x-input-error :messages="$errors->get('table_count')" class="mt-2" />
                    </div>
                    <button class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white app-focus hover:bg-red-800">
                        <x-icon name="qr-code" class="h-4 w-4" />
                        Generate
                    </button>
                </form>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($tables as $table)
                    <article class="rounded-lg border border-stone-200 p-4">
                        <div class="flex items-start gap-4">
                            <img src="{{ route('tenant.admin.tables.qr', [tenant('id'), $table]) }}" alt="{{ $table->code }} QR code" class="h-24 w-24 rounded-md border border-stone-200 bg-white p-1">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Table ID</p>
                                <p class="mt-1 text-2xl font-bold">{{ $table->code }}</p>
                                <p class="mt-2 break-all text-xs text-stone-500">{{ $table->qr_token }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-stone-200 p-5 text-sm text-stone-600 sm:col-span-2 lg:col-span-4">No active tables yet.</div>
                @endforelse
            </div>
        </section>

        <section class="mt-8 rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Dispatch and order control</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead>
                        <tr class="text-left text-stone-600"><th class="py-2">Order</th><th>Type</th><th>Status</th><th>Total</th><th>Driver</th><th class="text-right">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="py-3">
                                    <a href="{{ route('tenant.orders.show', [tenant('id'), $order]) }}" class="font-semibold hover:underline">{{ $order->public_code }}</a>
                                    <p class="text-stone-600">{{ $order->customer_name }}</p>
                                </td>
                                <td>
                                    {{ $order->typeLabel() }}
                                    @if ($order->type === 'local' && $order->restaurantTable)
                                        <p class="text-xs text-stone-500">{{ $order->restaurantTable->code }}</p>
                                    @endif
                                </td>
                                <td><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ App\Models\Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status) }}</span></td>
                                <td>{{ $order->formattedTotal() }}</td>
                                <td>{{ $order->driver?->name ?? 'Unassigned' }}</td>
                                <td class="text-right">
                                    @if ($order->type === 'delivery' && in_array($order->status, ['ready', 'assigned'], true))
                                        <form method="POST" action="{{ route('tenant.admin.orders.assign', [tenant('id'), $order]) }}" class="inline-flex gap-2">
                                            @csrf
                                            <select name="driver_id" class="rounded-md border-stone-300 text-xs">
                                                @foreach ($drivers as $driver)
                                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="rounded-lg bg-red-700 px-3 py-2 text-xs font-semibold text-white">Assign</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-2">
            <div class="rounded-lg border border-stone-200 bg-white p-5">
                <h2 class="text-lg font-semibold">Menu management</h2>
                <form method="POST" action="{{ route('tenant.admin.categories.store', tenant('id')) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
                    @csrf
                    <input name="name" placeholder="New category" class="rounded-md border-stone-300 text-sm" required>
                    <button class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white">Add category</button>
                </form>

                <form method="POST" action="{{ route('tenant.admin.menu-items.store', tenant('id')) }}" class="mt-5 grid gap-3">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <select name="category_id" class="rounded-md border-stone-300 text-sm" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <input name="name" placeholder="Dish name" class="rounded-md border-stone-300 text-sm" required>
                    </div>
                    <textarea name="description" rows="2" placeholder="Description" class="rounded-md border-stone-300 text-sm"></textarea>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <input name="price" type="number" step="0.01" min="0.5" placeholder="Price" class="rounded-md border-stone-300 text-sm" required>
                        <input name="prep_minutes" type="number" min="1" value="12" class="rounded-md border-stone-300 text-sm" required>
                        <label class="flex items-center gap-2 rounded-md border border-stone-300 px-3 text-sm"><input name="is_active" value="1" type="checkbox" checked> Active</label>
                    </div>
                    <button class="w-fit rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white">Create dish</button>
                </form>

                <div class="mt-5 divide-y divide-stone-100">
                    @foreach ($categories as $category)
                        <div class="py-3">
                            <p class="font-semibold">{{ $category->name }}</p>
                            <p class="text-sm text-stone-600">{{ $category->menuItems->count() }} items</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-stone-200 bg-white p-5">
                <h2 class="text-lg font-semibold">Stock management</h2>
                <form method="POST" action="{{ route('tenant.admin.stock.adjust', tenant('id')) }}" class="mt-4 grid gap-3">
                    @csrf
                    <select name="ingredient_id" class="rounded-md border-stone-300 text-sm" required>
                        @foreach ($ingredients as $ingredient)
                            <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->current_stock }} {{ $ingredient->unit }})</option>
                        @endforeach
                    </select>
                    <div class="grid gap-3 sm:grid-cols-[160px_1fr]">
                        <input name="quantity" type="number" step="0.01" placeholder="+10 or -2" class="rounded-md border-stone-300 text-sm" required>
                        <input name="note" placeholder="Reason" class="rounded-md border-stone-300 text-sm">
                    </div>
                    <button class="w-fit rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white">Adjust stock</button>
                </form>

                <div class="mt-5 divide-y divide-stone-100">
                    @foreach ($ingredients as $ingredient)
                        <div class="flex items-center justify-between gap-4 py-3">
                            <div>
                                <p class="font-semibold">{{ $ingredient->name }}</p>
                                <p class="text-sm text-stone-600">Low at {{ $ingredient->low_stock_threshold }} {{ $ingredient->unit }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $ingredient->isLow() ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-700' }}">{{ $ingredient->current_stock }} {{ $ingredient->unit }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-8 rounded-lg border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Staff and roles</h2>
            <form method="POST" action="{{ route('tenant.admin.staff.store', tenant('id')) }}" class="mt-4 grid gap-3 lg:grid-cols-6">
                @csrf
                <input name="name" placeholder="Name" class="rounded-md border-stone-300 text-sm" required>
                <input name="email" type="email" placeholder="Email" class="rounded-md border-stone-300 text-sm" required>
                <input name="phone" placeholder="Phone" class="rounded-md border-stone-300 text-sm">
                <select name="role" class="rounded-md border-stone-300 text-sm">
                    <option value="kitchen">Cuisine</option>
                    <option value="driver">Livreur</option>
                    <option value="admin">Admin</option>
                </select>
                <input name="password" type="password" placeholder="Password" class="rounded-md border-stone-300 text-sm" required>
                <input name="password_confirmation" type="password" placeholder="Confirm" class="rounded-md border-stone-300 text-sm" required>
                <button class="w-fit rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white lg:col-span-6">Create staff account</button>
            </form>

            <div class="mt-5 grid gap-3 md:grid-cols-3">
                @foreach ($staff as $member)
                    <div class="rounded-lg border border-stone-200 p-4">
                        <p class="font-semibold">{{ $member->name }}</p>
                        <p class="text-sm text-stone-600">{{ $member->email }}</p>
                        <span class="mt-3 inline-flex rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ ucfirst($member->role) }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
