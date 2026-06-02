<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('RestoBot') }}</p>
            <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Recipe assistant') }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4 border-b border-zinc-200 px-4 py-3 dark:border-zinc-800 sm:px-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-brand-700 text-white shadow-sm shadow-brand-700/20">
                        <x-icon name="sparkles" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="truncate font-semibold text-zinc-950 dark:text-white">{{ __('RestoBot') }}</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Online') }}</p>
                    </div>
                </div>

                @if ($messages !== [])
                    <form method="POST" action="{{ route('tenant.restobot.clear', tenant('id')) }}">
                        @csrf
                        <button class="grid h-10 w-10 place-items-center rounded-lg border border-zinc-300 text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950 app-focus dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white" title="{{ __('Clear chat') }}" aria-label="{{ __('Clear chat') }}">
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </form>
                @endif
            </div>

            <div class="max-h-[64vh] min-h-[460px] space-y-5 overflow-y-auto bg-zinc-50 px-4 py-5 dark:bg-zinc-950/60 sm:px-5">
                @forelse ($messages as $message)
                    @php($isUser = $message['role'] === 'user')
                    <article @class(['flex gap-3', 'justify-end' => $isUser])>
                        @unless ($isUser)
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-brand-700 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-brand-200 dark:ring-zinc-800">
                                <x-icon name="sparkles" class="h-4 w-4" />
                            </span>
                        @endunless

                        <div @class([
                            'max-w-[82%] rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm',
                            'rounded-br-md bg-brand-700 text-white' => $isUser,
                            'rounded-bl-md border border-zinc-200 bg-white text-zinc-800 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100' => ! $isUser,
                        ])>
                            <p @class([
                                'mb-1 text-xs font-semibold uppercase',
                                'text-brand-100' => $isUser,
                                'text-zinc-500 dark:text-zinc-400' => ! $isUser,
                            ])>
                                {{ $isUser ? __('You') : __('RestoBot') }}
                            </p>
                            <div class="whitespace-pre-line">{{ $message['content'] }}</div>
                        </div>
                    </article>
                @empty
                    <article class="flex gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-brand-700 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-brand-200 dark:ring-zinc-800">
                            <x-icon name="sparkles" class="h-4 w-4" />
                        </span>
                        <div class="max-w-[82%] rounded-2xl rounded-bl-md border border-zinc-200 bg-white px-4 py-3 text-sm leading-6 text-zinc-800 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100">
                            <p class="mb-1 text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ __('RestoBot') }}</p>
                            <div>{{ __('What are we cooking today?') }}</div>
                        </div>
                    </article>
                @endforelse
            </div>

            <form method="POST" action="{{ route('tenant.restobot.store', tenant('id')) }}" class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 sm:p-5">
                @csrf
                <x-input-label for="restobot_question" value="{{ __('Message') }}" required class="sr-only" />
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <textarea id="restobot_question" name="question" rows="2" class="block min-h-12 flex-1 resize-none rounded-lg border-zinc-300 text-sm shadow-sm app-focus dark:border-zinc-700" placeholder="{{ __('Message RestoBot...') }}" required>{{ old('question') }}</textarea>
                    <x-primary-button class="justify-center gap-2 sm:h-12">
                        <x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180" />
                        {{ __('Send') }}
                    </x-primary-button>
                </div>
                <x-input-error :messages="$errors->get('question')" class="mt-2" />
            </form>
        </section>
    </div>
</x-app-layout>
