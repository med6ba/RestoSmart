@props([
    'action',
    'name' => 'confirm-logout',
])

<x-modal :name="$name" maxWidth="md" focusable>
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf

        <div class="flex items-start gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                <x-icon name="log-out" class="h-5 w-5" />
            </span>

            <div class="min-w-0">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">
                    {{ __('Are you sure you want to log out?') }}
                </h2>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                    {{ __('You will need to sign in again to access this workspace.') }}
                </p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-primary-button>
                {{ __('Log out') }}
            </x-primary-button>
        </div>
    </form>
</x-modal>
