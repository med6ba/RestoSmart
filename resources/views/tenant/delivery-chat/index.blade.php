@php
    $hasActiveChat = (bool) $activeOrder;
    $closedMessage = __('Chat is closed because this order has already been delivered.');
@endphp

<x-app-layout>
    <div class="h-[calc(100vh-8rem)] min-h-[620px] overflow-hidden bg-zinc-50/80 dark:bg-zinc-950" data-realtime-scope="orders">
        <div class="grid h-full min-h-0 lg:grid-cols-[340px_minmax(0,1fr)]">
            <aside @class([
                'min-h-0 flex-col border-zinc-200 bg-white/85 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80',
                'hidden lg:flex' => $hasActiveChat,
                'flex' => ! $hasActiveChat,
                'lg:flex lg:border-e',
            ])>
                <div class="border-b border-zinc-200 p-5 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                            <x-icon name="messages-square" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h1 class="truncate text-xl font-bold text-zinc-950 dark:text-white">{{ __('Delivery Chat') }}</h1>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Assigned delivery conversations') }}</p>
                        </div>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-3">
                    @forelse ($conversations as $conversation)
                        @php
                            $isActive = $activeOrder?->is($conversation) ?? false;
                            $isClosed = $conversation->status === 'delivered';
                            $lastMessage = $conversation->latestDeliveryMessage;
                            $statusLabel = $statusLabels[$conversation->status] ?? ucfirst(str_replace('_', ' ', $conversation->status));
                            $unreadCount = (int) ($conversation->unread_messages_count ?? 0);
                        @endphp

                        <a
                            href="{{ route($routePrefix.'.show', [tenant('id'), $conversation, 'page' => $conversations->currentPage()]) }}"
                            @class([
                                'group mb-2 block rounded-lg border p-4 transition app-focus',
                                'border-brand-300 bg-brand-50/80 shadow-sm dark:border-brand-900/70 dark:bg-brand-950/30' => $isActive,
                                'border-transparent hover:border-zinc-200 hover:bg-zinc-50 dark:hover:border-zinc-800 dark:hover:bg-zinc-950/50' => ! $isActive,
                            ])
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-sm font-bold text-zinc-950 dark:text-white">{{ $conversation->public_code }}</p>
                                        @if ($isClosed)
                                            <span class="shrink-0 rounded-full border border-zinc-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:text-zinc-300">{{ __('Chat Closed') }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 truncate text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                        {{ auth()->user()->role === 'driver' ? $conversation->customer_name : $conversation->delivery?->driver?->name }}
                                    </p>
                                </div>

                                @if ($unreadCount > 0)
                                    <span class="grid h-6 min-w-6 place-items-center rounded-full bg-brand-600 px-2 text-xs font-bold text-white">{{ $unreadCount }}</span>
                                @endif
                            </div>

                            <p class="mt-3 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300">
                                @if ($lastMessage)
                                    <span class="font-semibold">{{ $lastMessage->sender?->name }}:</span>
                                    {{ $lastMessage->message }}
                                @else
                                    {{ __('No messages yet. Start with a quick delivery note.') }}
                                @endif
                            </p>

                            <div class="mt-3 flex items-center justify-between gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $statusLabel }}</span>
                                @if ($lastMessage?->created_at)
                                    <time datetime="{{ $lastMessage->created_at->toISOString() }}" data-local-time>{{ $lastMessage->created_at->format('H:i') }}</time>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                            <x-icon name="messages-square" class="mx-auto h-8 w-8 text-zinc-400" />
                            <h2 class="mt-3 text-sm font-bold text-zinc-950 dark:text-white">{{ __('No delivery chats yet') }}</h2>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Chats appear here after a delivery order has an assigned driver.') }}</p>
                        </div>
                    @endforelse
                </div>

                @if ($conversations->hasPages())
                    <div class="border-t border-zinc-200 px-3 py-3 dark:border-zinc-800">
                        {{ $conversations->onEachSide(0)->links() }}
                    </div>
                @endif
            </aside>

            <section @class([
                'min-h-0 flex-col bg-gradient-to-br from-zinc-50 via-white to-brand-50/30 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950',
                'flex' => $hasActiveChat,
                'hidden lg:flex' => ! $hasActiveChat,
            ])>
                @if ($activeOrder)
                    @php
                        $isClosed = $activeOrder->status === 'delivered';
                        $deliveryStatus = ucfirst(str_replace('_', ' ', $activeOrder->delivery?->status ?? 'delivery'));
                    @endphp

                    <div
                        class="flex h-full min-h-0 flex-col"
                        x-data="deliveryChat({
                            currentUserId: @js(auth()->id()),
                            orderId: @js($activeOrder->id),
                            channelName: @js($channelName),
                            sendUrl: @js($sendRoute),
                            canSend: @js($canSend),
                            currentUserLabel: @js(__('You')),
                            closedMessage: @js($closedMessage),
                            sendErrorMessage: @js(__('The message could not be sent.')),
                            messages: @js($messages->values()),
                        })"
                    >
                        <header class="border-b border-zinc-200 bg-white/90 p-4 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <a href="{{ route($routePrefix.'.index', tenant('id')) }}" class="grid h-10 w-10 place-items-center rounded-lg border border-zinc-200 text-zinc-600 transition hover:bg-zinc-100 app-focus dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 lg:hidden" aria-label="{{ __('Back to chats') }}">
                                        <x-icon name="arrow-right" class="h-4 w-4 rotate-180 rtl:rotate-0" />
                                    </a>
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white">
                                        {{ strtoupper(substr($recipient?->name ?? __('U'), 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <h2 class="truncate text-lg font-bold text-zinc-950 dark:text-white">{{ $recipient?->name ?? __('Delivery contact') }}</h2>
                                        <p class="mt-0.5 truncate text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ __('Order :code', ['code' => $activeOrder->public_code]) }} · {{ $deliveryStatus }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-zinc-600 ring-1 ring-zinc-200 dark:bg-zinc-950 dark:text-zinc-300 dark:ring-zinc-700">
                                        {{ $statusLabels[$activeOrder->status] ?? ucfirst(str_replace('_', ' ', $activeOrder->status)) }}
                                    </span>
                                    @if ($isClosed)
                                        <span class="rounded-full bg-zinc-950 px-3 py-1 text-xs font-bold text-white dark:bg-white dark:text-zinc-950">{{ __('Chat Closed') }}</span>
                                    @endif
                                </div>
                            </div>
                        </header>

                        <div x-ref="messages" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6" dir="ltr">
                            <template x-if="messages.length === 0">
                                <div class="mx-auto mt-10 max-w-sm rounded-lg border border-dashed border-zinc-300 bg-white/80 p-6 text-center dark:border-zinc-700 dark:bg-zinc-900/80">
                                    <x-icon name="messages-square" class="mx-auto h-8 w-8 text-brand-600 dark:text-brand-300" />
                                    <h3 class="mt-3 text-sm font-bold text-zinc-950 dark:text-white">{{ __('No messages yet') }}</h3>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Coordinate pickup details, gate codes, or delivery notes here.') }}</p>
                                </div>
                            </template>

                            <template x-for="message in messages" :key="message.id || message.message_id">
                                <div class="w-full">
                                    <div class="max-w-[86%] sm:max-w-[72%]" :class="isCurrentUser(message) ? 'ml-auto text-right' : 'mr-auto text-left'">
                                        <div
                                            class="inline-block rounded-2xl px-4 py-2.5 text-sm shadow-sm"
                                            :class="isCurrentUser(message)
                                                ? 'rounded-br-md bg-brand-600 text-left text-white shadow-brand-900/20'
                                                : 'rounded-bl-md border border-yellow-300 bg-yellow-200 text-left text-zinc-950 shadow-yellow-900/10 dark:border-yellow-400 dark:bg-yellow-300 dark:text-zinc-950'"
                                        >
                                            <p class="whitespace-pre-wrap break-words [overflow-wrap:anywhere]" dir="auto" x-text="message.message"></p>
                                            <div
                                                class="mt-1 text-[10px] leading-none"
                                                :class="isCurrentUser(message) ? 'text-right text-white/75' : 'text-right text-zinc-700/70'"
                                                x-text="message.formatted_time"
                                            ></div>
                                        </div>
                                        <p class="mt-1 px-1 text-[11px] text-zinc-400" x-show="message.pending">{{ __('Sending...') }}</p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="border-t border-zinc-200 bg-white/90 p-4 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                            <template x-if="error">
                                <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 dark:border-red-900/70 dark:bg-red-950/30 dark:text-red-200" x-text="error"></div>
                            </template>

                            @if ($canSend)
                                <form class="flex flex-col gap-3 sm:flex-row sm:items-end" x-on:submit.prevent="send()">
                                    <label class="sr-only" for="delivery-chat-message">{{ __('Message') }}</label>
                                    <textarea
                                        id="delivery-chat-message"
                                        x-model="draft"
                                        x-on:keydown.enter.exact.prevent="send()"
                                        rows="2"
                                        maxlength="1000"
                                        class="min-h-12 flex-1 resize-none rounded-lg border-zinc-300 bg-white text-sm shadow-sm app-focus dark:border-zinc-700 dark:bg-zinc-950"
                                        placeholder="{{ __('Write a delivery message...') }}"
                                    ></textarea>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-60 app-focus"
                                        x-bind:disabled="sending || ! draft.trim()"
                                    >
                                        <span x-show="!sending">{{ __('Send') }}</span>
                                        <span x-show="sending">{{ __('Sending...') }}</span>
                                    </button>
                                </form>
                            @else
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-sm font-semibold text-zinc-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">
                                    {{ $closedMessage }}
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="grid h-full place-items-center p-8">
                        <div class="max-w-md text-center">
                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-200">
                                <x-icon name="messages-square" class="h-7 w-7" />
                            </span>
                            <h2 class="mt-4 text-xl font-bold text-zinc-950 dark:text-white">{{ __('Select a delivery chat') }}</h2>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Choose an assigned delivery from the list to coordinate with the client or driver in real time.') }}</p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
