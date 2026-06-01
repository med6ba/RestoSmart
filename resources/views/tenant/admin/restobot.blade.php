<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('RestoBot') }}</p>
            <h1 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ __('Recipe assistant') }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[1fr_320px] lg:px-8">
        <section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('New dish ideas only') }}</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ __('Ask for recipes, ingredients, preparation steps, allergens, and menu descriptions. RestoBot refuses unrelated questions.') }}</p>
                </div>
                @if ($messages !== [])
                    <form method="POST" action="{{ route('tenant.restobot.clear', tenant('id')) }}">
                        @csrf
                        <button class="rounded-lg border border-zinc-300 px-3 py-2 text-sm font-semibold hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('Clear chat') }}</button>
                    </form>
                @endif
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($messages as $message)
                    <article @class([
                        'rounded-lg border p-4 text-sm leading-6',
                        'border-brand-200 bg-brand-50 text-zinc-900 dark:border-brand-900/50 dark:bg-brand-950/20 dark:text-zinc-50' => $message['role'] === 'user',
                        'border-zinc-200 bg-zinc-50 text-zinc-800 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100' => $message['role'] !== 'user',
                    ])>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $message['role'] === 'user' ? __('Admin') : __('RestoBot') }}</p>
                        <div class="whitespace-pre-line">{{ $message['content'] }}</div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                        {{ __('Example: Create a Moroccan chicken bowl recipe with prep steps and a short menu description.') }}
                    </div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('tenant.restobot.store', tenant('id')) }}" class="mt-6">
                @csrf
                <x-input-label for="restobot_question" value="{{ __('Dish or recipe request') }}" required />
                <textarea id="restobot_question" name="question" rows="4" class="mt-1 block w-full rounded-md border-zinc-300 text-sm dark:border-zinc-700" placeholder="{{ __('Ask for a new dish recipe, ingredients, prep, or menu description...') }}" required>{{ old('question') }}</textarea>
                <x-input-error :messages="$errors->get('question')" class="mt-2" />
                <div class="mt-4 flex justify-end">
                    <x-primary-button class="gap-2">
                        <x-icon name="sparkles" class="h-4 w-4" />
                        {{ __('Ask RestoBot') }}
                    </x-primary-button>
                </div>
            </form>
        </section>

        <aside class="h-fit rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Guardrails') }}</p>
            <ul class="mt-4 space-y-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                <li>{{ __('Only recipe and new dish creation questions are answered.') }}</li>
                <li>{{ __('Use the output as a starting point, then review allergens and kitchen process.') }}</li>
                <li>{{ __('Groq model is configurable with GROQ_MODEL in .env.') }}</li>
            </ul>
        </aside>
    </div>
</x-app-layout>
