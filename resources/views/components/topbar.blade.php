<header class="sticky top-0 z-20 flex h-16 w-full shrink-0 items-center gap-x-4 bg-zinc-50 px-4 dark:bg-zinc-950 sm:gap-x-6 sm:px-6 lg:px-8">
    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        <!-- Sidebar Toggle (Mobile only) -->
        <div class="flex items-center lg:hidden">
            <button type="button" @click="sidebarOpen = true" class="-m-2.5 p-2.5 text-zinc-700 dark:text-zinc-300">
                <span class="sr-only">{{ __('Open sidebar') }}</span>
                <x-icon name="menu" class="h-6 w-6" />
            </button>
        </div>

        <!-- Clock & Date -->
        <div class="flex flex-1 items-center justify-center lg:absolute lg:left-1/2 lg:top-1/2 lg:-translate-x-1/2 lg:-translate-y-1/2" x-data="liveClock()" x-init="initClock()">
            <div class="hidden sm:flex items-center gap-3 rounded-full bg-white px-3 py-1.5 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800">
                <div class="flex items-center gap-2">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <x-icon name="clock" class="h-3.5 w-3.5" />
                    </div>
                    <span class="text-sm font-bold tabular-nums tracking-wide text-zinc-900 dark:text-zinc-100" x-text="currentTime">--:--:--</span>
                </div>
                <div class="h-3.5 w-px bg-zinc-200 dark:bg-zinc-700"></div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400"
                     x-data="{ date: new Date().toLocaleDateString(document.documentElement.lang || 'en-US', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) }"
                     x-init="setInterval(() => date = new Date().toLocaleDateString(document.documentElement.lang || 'en-US', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }), 60000)">
                    <span x-text="date"></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-x-4 lg:gap-x-6 ms-auto">
            <div class="flex items-center gap-x-3 sm:gap-x-4">
                <!-- Theme Switcher -->
                <x-theme-switcher />

                <!-- Locale Switcher -->
                <x-locale-switcher compact />

                <!-- Separator -->
                <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-zinc-200 dark:lg:bg-zinc-800" aria-hidden="true"></div>

                <!-- Notifications -->
                <x-notifications-dropdown />
            </div>
        </div>
    </div>
</header>
