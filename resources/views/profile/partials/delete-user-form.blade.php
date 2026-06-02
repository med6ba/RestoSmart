@php
    $tenantId = function_exists('tenant') ? tenant('id') : null;
    $profileDestroyRoute = $tenantId ? route('tenant.profile.destroy', $tenantId) : route('profile.destroy');
@endphp

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-red-700 dark:text-red-300">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            {{ __('Deleting an admin account permanently removes the account and its restaurant workspaces.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ $profileDestroyRoute }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('This removes the admin account, tenant workspace, staff accounts, menu, orders, billing records, and platform application records. Re-enter your password to confirm.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" required />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    required
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3 gap-2">
                    <x-icon name="trash" class="h-4 w-4" />
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
