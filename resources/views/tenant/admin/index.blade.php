@php
    $modalToShow = old('_modal');
    $statCards = [
        ['label' => 'Today orders', 'value' => $stats['today_orders'], 'icon' => 'receipt', 'valueClass' => 'text-zinc-950 dark:text-white'],
        ['label' => 'Paid revenue', 'value' => \App\Support\Money::mad($stats['revenue'], 0), 'icon' => 'badge-dollar', 'valueClass' => 'text-zinc-950 dark:text-white'],
        ['label' => 'Active orders', 'value' => $stats['active_orders'], 'icon' => 'clipboard-list', 'valueClass' => 'text-brand-700 dark:text-brand-300'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ tenant('name') }}</p>
            <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Restaurant admin dashboard') }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8" data-realtime-scope="orders,menu,tables,staff">
        @if (session('status'))
            <div class="status-toast mb-6">{{ session('status') }}</div>
        @endif

        <section class="grid gap-4 md:grid-cols-3">
            @foreach ($statCards as $stat)
                <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ __($stat['label']) }}</p>
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                            <x-icon :name="$stat['icon']" class="h-4 w-4" />
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold {{ $stat['valueClass'] }}">{{ $stat['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="mt-8 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Table QR kit') }}</p>
                    <h2 class="mt-1 text-lg font-semibold">{{ __('Dining room tables') }}</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ trans_choice(':count active table with unique IDs and guest-ready QR codes.|:count active tables with unique IDs and guest-ready QR codes.', $tableCount, ['count' => $tableCount]) }}</p>
                </div>
                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-table-qr')" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800 app-focus">
                    <x-icon name="qr-code" class="h-4 w-4" />
                    {{ __('Add table QR') }}
                </button>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($tables as $table)
                    @php($qrDownloadUrl = route('tenant.admin.tables.qr', [tenant('id'), $table, 'download' => 1]))
                    <article class="relative overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 p-4 transition hover:border-brand-300 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:border-brand-800">
                        <div class="absolute inset-x-0 top-0 h-1 bg-brand-500"></div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Table ID') }}</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-950 dark:text-white">{{ $table->code }}</p>
                            </div>
                            <span @class([
                                'rounded-full bg-white px-2.5 py-1 text-xs font-semibold shadow-sm dark:bg-zinc-900',
                                'text-brand-700 dark:text-brand-200' => ! $table->is_occupied,
                                'text-amber-700 dark:text-amber-200' => $table->is_occupied,
                            ])>{{ $table->is_occupied ? __('Occupied') : __('Active') }}</span>
                        </div>

                        <div class="mt-4 grid place-items-center rounded-lg border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800">
                            <img src="{{ route('tenant.admin.tables.qr', [tenant('id'), $table]) }}" alt="{{ __(':table QR code', ['table' => $table->code]) }}" class="h-32 w-32">
                        </div>

                        <p class="mt-3 break-all rounded-md bg-white px-2 py-1.5 font-mono text-[11px] text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">{{ $table->qr_token }}</p>
                        <div class="mt-3 grid gap-2">
                            <a href="{{ $qrDownloadUrl }}" download="table-{{ \Illuminate\Support\Str::slug($table->code) ?: $table->id }}-qr.png" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                <x-icon name="qr-code" class="h-4 w-4" />
                                {{ __('Download QR') }}
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300 sm:col-span-2 lg:col-span-4">
                        {{ __('No active tables yet. Add the first table QR to start dine-in ordering.') }}
                    </div>
                @endforelse
            </div>
        </section>

        <x-modal name="add-table-qr" :show="$modalToShow === 'add-table-qr'" maxWidth="md" focusable>
            <form method="POST" action="{{ route('tenant.admin.tables.store', tenant('id')) }}" class="p-6">
                @csrf
                <input type="hidden" name="_modal" value="add-table-qr">
                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Add table QR code') }}</h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('Add one table at a time. Existing QR tokens stay stable.') }}</p>
                <div class="mt-5">
                    <x-input-label for="table_code" value="{{ __('Table code') }}" />
                    <x-text-input id="table_code" name="code" type="text" maxlength="20" value="{{ old('code') }}" placeholder="{{ __('Leave empty for the next code') }}" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button class="gap-2">
                        <x-icon name="qr-code" class="h-4 w-4" />
                        {{ __('Add QR code') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

        <section class="mt-8 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-1">
                <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Orders') }}</p>
                <h2 class="text-lg font-semibold">{{ __('Dispatch and order control') }}</h2>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                    <thead>
                        <tr class="text-start text-zinc-600 dark:text-zinc-300">
                            <th class="py-2">{{ __('Order') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('Driver') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="py-3">
                                    <a href="{{ route('tenant.orders.show', [tenant('id'), $order]) }}" class="font-semibold text-zinc-950 hover:text-brand-700 app-focus dark:text-white dark:hover:text-brand-300">{{ $order->public_code }}</a>
                                    <p class="text-zinc-600 dark:text-zinc-300">{{ $order->customer_name }}</p>
                                </td>
                                <td>
                                    {{ $order->typeLabel() }}
                                    @if ($order->type === 'local' && $order->restaurantTable)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order->restaurantTable->code }}</p>
                                    @endif
                                </td>
                                <td><span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ __(App\Models\Order::STATUS_FLOW[$order->status] ?? ucfirst($order->status)) }}</span></td>
                                <td>{{ $order->formattedTotal() }}</td>
                                <td>{{ $order->driver?->name ?? __('Unassigned') }}</td>
                                <td class="text-end">
                                    @if ($order->type === 'delivery' && in_array($order->status, ['ready', 'assigned'], true))
                                        <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'assign-order-{{ $order->id }}')" class="rounded-lg bg-brand-700 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-800 app-focus">{{ __('Assign') }}</button>
                                    @else
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('No action') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-sm text-zinc-600 dark:text-zinc-300">{{ __('No orders yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @foreach ($orders as $order)
            @if ($order->type === 'delivery' && in_array($order->status, ['ready', 'assigned'], true))
                <x-modal name="assign-order-{{ $order->id }}" maxWidth="md" focusable>
                    <form method="POST" action="{{ route('tenant.admin.orders.assign', [tenant('id'), $order]) }}" class="p-6">
                        @csrf
                        <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Assign :code', ['code' => $order->public_code]) }}</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('Choose the driver who should take this delivery.') }}</p>
                        <label class="mt-5 grid gap-1 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            {{ __('Driver') }}
                            <select name="driver_id" class="rounded-md border-zinc-300 text-sm dark:border-zinc-700" required>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}" @selected($order->driver_id === $driver->id)>{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                            <x-primary-button>{{ __('Assign driver') }}</x-primary-button>
                        </div>
                    </form>
                </x-modal>
            @endif
        @endforeach

        <section class="mt-8">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Menu') }}</p>
                        <h2 class="text-lg font-semibold">{{ __('Menu management') }}</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-category')" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Add category') }}</button>
                        <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-dish')" class="rounded-lg bg-brand-700 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-800 app-focus">{{ __('Create dish') }}</button>
                    </div>
                </div>

                <div class="mt-5 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($categories as $category)
                        <div class="flex items-center justify-between gap-4 py-3">
                            <div>
                                <p class="font-semibold">{{ $category->name }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $category->description ?: __('No description yet.') }}</p>
                            </div>
                            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ trans_choice(':count item|:count items', $category->menuItems->count(), ['count' => $category->menuItems->count()]) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <x-modal name="create-category" :show="$modalToShow === 'create-category'" maxWidth="lg" focusable>
            <form method="POST" action="{{ route('tenant.admin.categories.store', tenant('id')) }}" class="p-6">
                @csrf
                <input type="hidden" name="_modal" value="create-category">
                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Add category') }}</h3>
                <div class="mt-5 grid gap-4">
                    <div>
                        <x-input-label for="category_name" value="{{ __('Category name') }}" required />
                        <x-text-input id="category_name" name="name" value="{{ old('name') }}" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="category_description" value="{{ __('Description') }}" />
                        <textarea id="category_description" name="description" rows="3" class="mt-1 block w-full rounded-md border-zinc-300 text-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button>{{ __('Add category') }}</x-primary-button>
                </div>
            </form>
        </x-modal>

        <x-modal name="create-dish" :show="$modalToShow === 'create-dish'" maxWidth="lg" focusable>
            <form method="POST" action="{{ route('tenant.admin.menu-items.store', tenant('id')) }}" class="p-6" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_modal" value="create-dish">
                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Create dish') }}</h3>
                <div class="mt-5 grid gap-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="dish_category_id" value="{{ __('Category') }}" required />
                            <select id="dish_category_id" name="category_id" class="mt-1 block w-full rounded-md border-zinc-300 text-sm" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="dish_name" value="{{ __('Dish name') }}" required />
                            <x-text-input id="dish_name" name="name" value="{{ old('name') }}" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="dish_description" value="{{ __('Description') }}" />
                        <textarea id="dish_description" name="description" rows="3" class="mt-1 block w-full rounded-md border-zinc-300 text-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                    <div x-data="imageCropper()">
                        <input type="hidden" name="cropped_image" x-ref="croppedImageInput">
                        <x-input-label for="dish_image" value="{{ __('Dish image') }}" />
                        
                        <div class="mt-1" x-show="!preview">
                            <label for="dish_image" class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-zinc-300 px-4 py-5 text-sm font-medium text-zinc-500 transition hover:border-brand-400 hover:text-brand-600 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-brand-600 dark:hover:text-brand-400">
                                <x-icon name="image-plus" class="h-5 w-5" />
                                <span>{{ __('Upload image') }}</span>
                            </label>
                            <input id="dish_image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" x-ref="imageInput" @change="loadFile" />
                        </div>

                        <div x-show="preview" class="mt-1" style="display: none;">
                            <div class="relative mb-2 h-64 w-full overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900">
                                <img x-ref="image" :src="preview" class="max-w-full" />
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="reset()" class="flex-1 rounded-lg border border-zinc-300 px-3 py-2 text-sm font-semibold hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Cancel') }}</button>
                            </div>
                        </div>

                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400" x-show="!preview">{{ __('JPG, PNG or WebP. Max 2 MB.') }}</p>
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="dish_price" value="{{ __('Price (MAD)') }}" required />
                            <x-text-input id="dish_price" name="price" type="number" step="0.01" min="0.5" value="{{ old('price') }}" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="dish_prep_minutes" value="{{ __('Prep minutes') }}" required />
                            <x-text-input id="dish_prep_minutes" name="prep_minutes" type="number" min="1" value="{{ old('prep_minutes', 12) }}" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('prep_minutes')" class="mt-2" />
                        </div>
                        <label class="mt-6 flex items-center gap-2 rounded-md border border-zinc-300 px-3 text-sm dark:border-zinc-700">
                            <input name="is_active" value="1" type="checkbox" class="rounded border-zinc-300 text-brand-700 focus:ring-brand-500" @checked(old('is_active', true))>
                            {{ __('Active') }}
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button>{{ __('Create dish') }}</x-primary-button>
                </div>
            </form>
        </x-modal>

        <section class="mt-8 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Team') }}</p>
                    <h2 class="text-lg font-semibold">{{ __('Staff and roles') }}</h2>
                </div>
                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-staff')" class="rounded-lg bg-brand-700 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-800 app-focus">{{ __('Create staff account') }}</button>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-3">
                @forelse ($staff as $member)
                    <article class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                        <p class="font-semibold">{{ $member->name }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $member->email }}</p>
                        <span class="mt-3 inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ __(ucfirst($member->role)) }}</span>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-zinc-300 p-5 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300 md:col-span-3">{{ __('No staff accounts yet.') }}</div>
                @endforelse
            </div>
        </section>

        <x-modal name="create-staff" :show="$modalToShow === 'create-staff'" maxWidth="xl" focusable>
            <form method="POST" action="{{ route('tenant.admin.staff.store', tenant('id')) }}" class="p-6">
                @csrf
                <input type="hidden" name="_modal" value="create-staff">
                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Create staff account') }}</h3>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="staff_name" value="{{ __('Name') }}" required />
                        <x-text-input id="staff_name" name="name" value="{{ old('name') }}" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="staff_email" value="{{ __('Email') }}" required />
                        <x-text-input id="staff_email" name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="staff_phone" value="{{ __('Phone') }}" />
                        <x-text-input id="staff_phone" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="staff_role" value="{{ __('Role') }}" required />
                        <select id="staff_role" name="role" class="mt-1 block w-full rounded-md border-zinc-300 text-sm">
                            <option value="kitchen" @selected(old('role') === 'kitchen')>{{ __('Kitchen') }}</option>
                            <option value="driver" @selected(old('role') === 'driver')>{{ __('Driver') }}</option>
                            <option value="admin" @selected(old('role') === 'admin')>{{ __('Admin') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="staff_password" value="{{ __('Password') }}" required />
                        <x-text-input id="staff_password" name="password" type="password" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="staff_password_confirmation" value="{{ __('Confirm password') }}" required />
                        <x-text-input id="staff_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button>{{ __('Create staff account') }}</x-primary-button>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('imageCropper', () => ({
                preview: null,
                cropper: null,
                
                loadFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.preview = e.target.result;
                        this.$nextTick(() => {
                            this.initCropper();
                        });
                    };
                    reader.readAsDataURL(file);
                },
                
                initCropper() {
                    if (this.cropper) {
                        this.cropper.destroy();
                    }
                    
                    this.cropper = new Cropper(this.$refs.image, {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        autoCropArea: 1,
                        crop: () => {
                            this.$refs.croppedImageInput.value = this.cropper.getCroppedCanvas({
                                maxWidth: 1200,
                                maxHeight: 1200,
                                fillColor: '#fff',
                                imageSmoothingEnabled: true,
                                imageSmoothingQuality: 'high',
                            }).toDataURL('image/jpeg', 0.85);
                        }
                    });
                },
                
                reset() {
                    this.preview = null;
                    this.$refs.imageInput.value = '';
                    this.$refs.croppedImageInput.value = '';
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                }
            }))
        })
    </script>
</x-app-layout>
