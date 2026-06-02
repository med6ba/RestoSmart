<div x-data="notificationsDropdown()" class="relative">
    <button type="button" @click="toggle()" @click.outside="close()" class="relative rounded-full p-1.5 text-zinc-400 hover:text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 transition-colors">
        <span class="sr-only">{{ __('View notifications') }}</span>
        <x-icon name="bell" class="h-5 w-5" x-bind:class="{ 'animate-pulse text-brand-500': unreadCount > 0 }" />
        
        <span x-show="unreadCount > 0" x-transition class="absolute end-1 top-1 flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-500 border-2 border-white dark:border-zinc-950"></span>
        </span>
    </button>

    <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" class="absolute end-0 z-50 mt-2 w-80 ltr:origin-top-right rtl:origin-top-left rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-zinc-900 dark:ring-white/10">
        <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Notifications') }}</h3>
            <button x-show="unreadCount > 0" @click="markAllAsRead()" class="text-xs text-brand-600 hover:text-brand-500 dark:text-brand-400 font-medium">
                {{ __('Mark all read') }}
            </button>
        </div>
        
        <div class="max-h-96 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('No notifications right now.') }}
                </div>
            </template>
            
            <template x-for="notification in notifications" :key="notification.id">
                <div class="px-4 py-3 border-b border-zinc-50 dark:border-zinc-800/50 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors" x-bind:class="{ 'bg-brand-50/30 dark:bg-brand-900/10': !notification.read_at }">
                    <div class="flex items-start">
                        <div class="shrink-0 pt-1">
                            <span class="grid h-8 w-8 place-items-center rounded-full" x-bind:class="getIconClass(notification.type)">
                                <x-icon name="bell" class="h-4 w-4" />
                            </span>
                        </div>
                        <div class="ms-3 w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white" x-text="notification.title"></p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400" x-text="notification.body"></p>
                            <p class="mt-1 text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 uppercase" x-text="formatTime(notification.created_at)"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
