@props(['name'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('arrow-right')
            <path d="M5 12h14" />
            <path d="m13 6 6 6-6 6" />
            @break

        @case('archive')
            <rect x="3" y="4" width="18" height="4" rx="1" />
            <path d="M5 8v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8" />
            <path d="M10 12h4" />
            @break

        @case('badge-dollar')
            <path d="M3.9 12a2.1 2.1 0 0 1 0-3l1.2-1.2V6a2.1 2.1 0 0 1 2.1-2.1H9l1.5-1.5a2.1 2.1 0 0 1 3 0L15 3.9h1.8A2.1 2.1 0 0 1 18.9 6v1.8L20.1 9a2.1 2.1 0 0 1 0 3l-1.2 1.2V15a2.1 2.1 0 0 1-2.1 2.1H15l-1.5 1.5a2.1 2.1 0 0 1-3 0L9 17.1H7.2A2.1 2.1 0 0 1 5.1 15v-1.8L3.9 12Z" />
            <path d="M12 7v10" />
            <path d="M9.6 9.5c.6-.5 1.4-.7 2.4-.7 1.5 0 2.5.7 2.5 1.7 0 1.1-1.1 1.5-2.5 1.5s-2.5.4-2.5 1.5 1 1.7 2.5 1.7c1 0 1.8-.2 2.4-.7" />
            @break

        @case('bell')
            <path d="M10 21h4" />
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
            @break

        @case('building-store')
            <path d="M4 10h16l-1.5-6h-13L4 10Z" />
            <path d="M5 10v10h14V10" />
            <path d="M9 20v-6h6v6" />
            <path d="M4 10a3 3 0 0 0 6 0" />
            <path d="M10 10a3 3 0 0 0 6 0" />
            <path d="M16 10a3 3 0 0 0 4 2.8" />
            @break

        @case('chart-bar')
            <path d="M3 3v18h18" />
            <path d="M7 16V9" />
            <path d="M12 16V5" />
            <path d="M17 16v-4" />
            @break

        @case('check')
            <path d="m20 6-11 11-5-5" />
            @break

        @case('check-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="m9 12 2 2 4-4" />
            @break

        @case('chef-hat')
            <path d="M6 14.5V21h12v-6.5" />
            <path d="M6 14.5a4 4 0 0 1 .7-7.9A5 5 0 0 1 12 3a5 5 0 0 1 5.3 3.6 4 4 0 0 1 .7 7.9" />
            <path d="M8 17h8" />
            @break

        @case('clipboard-list')
            <path d="M9 3h6v4H9z" />
            <path d="M9 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3" />
            <path d="M8 12h.01" />
            <path d="M11 12h5" />
            <path d="M8 16h.01" />
            <path d="M11 16h5" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break

        @case('dollar-sign')
            <path d="M12 2v20" />
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
            @break

        @case('external-link')
            <path d="M15 3h6v6" />
            <path d="M10 14 21 3" />
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
            @break

        @case('home')
            <path d="m3 11 9-8 9 8" />
            <path d="M5 10v10h14V10" />
            <path d="M9 20v-6h6v6" />
            @break

        @case('layout-dashboard')
            <rect x="3" y="3" width="7" height="9" rx="1.5" />
            <rect x="14" y="3" width="7" height="5" rx="1.5" />
            <rect x="14" y="12" width="7" height="9" rx="1.5" />
            <rect x="3" y="16" width="7" height="5" rx="1.5" />
            @break

        @case('languages')
            <path d="M2 5h12" />
            <path d="M7 2v3" />
            <path d="m5 8 6 6" />
            <path d="m4 14 6-6 2-3" />
            <path d="m22 22-5-10-5 10" />
            <path d="M14 18h6" />
            @break

        @case('log-in')
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <path d="m10 17 5-5-5-5" />
            <path d="M15 12H3" />
            @break

        @case('log-out')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="m16 17 5-5-5-5" />
            <path d="M21 12H9" />
            @break

        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 7 9 6 9-6" />
            @break

        @case('map-pin')
            <path d="M12 22s7-5.3 7-12a7 7 0 1 0-14 0c0 6.7 7 12 7 12Z" />
            <circle cx="12" cy="10" r="2.5" />
            @break

        @case('menu')
            <path d="M4 7h16" />
            <path d="M4 12h16" />
            <path d="M4 17h16" />
            @break

        @case('package')
            <path d="m7.5 4.3 9 5.2" />
            <path d="m3.5 7 8.5 5 8.5-5" />
            <path d="M12 22V12" />
            <path d="M21 8v8a2 2 0 0 1-1 1.7l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 16V8a2 2 0 0 1 1-1.7l7-4a2 2 0 0 1 2 0l7 4A2 2 0 0 1 21 8Z" />
            @break

        @case('play')
            <path d="M6 4v16l14-8-14-8Z" />
            @break

        @case('plus')
            <path d="M12 5v14" />
            <path d="M5 12h14" />
            @break

        @case('qr-code')
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <path d="M7 7h.01" />
            <path d="M18 7h.01" />
            <path d="M7 18h.01" />
            <path d="M14 14h2v2h-2z" />
            <path d="M18 14h3v3" />
            <path d="M14 18h2v3" />
            <path d="M18 21h3" />
            @break

        @case('moon')
            <path d="M20.9 13.2A8 8 0 1 1 10.8 3.1a6.5 6.5 0 0 0 10.1 10.1Z" />
            @break

        @case('receipt')
            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z" />
            <path d="M8 7h8" />
            <path d="M8 11h8" />
            <path d="M8 15h5" />
            @break

        @case('route')
            <circle cx="6" cy="19" r="3" />
            <circle cx="18" cy="5" r="3" />
            <path d="M12 19h2a4 4 0 0 0 0-8H10a4 4 0 0 1 0-8h2" />
            @break

        @case('save')
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" />
            <path d="M17 21v-8H7v8" />
            <path d="M7 3v5h8" />
            @break

        @case('settings')
            <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3h.1a1.7 1.7 0 0 0 .9-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.5.9h.1a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.6.9Z" />
            @break

        @case('shield-check')
            <path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3v8Z" />
            <path d="m9 12 2 2 4-4" />
            @break

        @case('sparkles')
            <path d="m12 3 1.8 4.2L18 9l-4.2 1.8L12 15l-1.8-4.2L6 9l4.2-1.8L12 3Z" />
            <path d="m19 14 .9 2.1L22 17l-2.1.9L19 20l-.9-2.1L16 17l2.1-.9L19 14Z" />
            <path d="m5 15 .7 1.6L7 17.3l-1.3.7L5 20l-.7-2-1.3-.7 1.3-.7L5 15Z" />
            @break

        @case('shopping-cart')
            <circle cx="8" cy="21" r="1" />
            <circle cx="19" cy="21" r="1" />
            <path d="M2 3h3l3.5 12.5a2 2 0 0 0 2 1.5h7.7a2 2 0 0 0 1.9-1.4L22 8H6" />
            @break

        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2" />
            <path d="M12 20v2" />
            <path d="m4.93 4.93 1.41 1.41" />
            <path d="m17.66 17.66 1.41 1.41" />
            <path d="M2 12h2" />
            <path d="M20 12h2" />
            <path d="m6.34 17.66-1.41 1.41" />
            <path d="m19.07 4.93-1.41 1.41" />
            @break

        @case('truck')
            <path d="M10 17H5a2 2 0 0 1-2-2V6h11v11" />
            <path d="M14 8h4l3 4v3a2 2 0 0 1-2 2h-1" />
            <path d="M14 17h-4" />
            <circle cx="7" cy="17" r="2" />
            <circle cx="16" cy="17" r="2" />
            @break

        @case('trash')
            <path d="M3 6h18" />
            <path d="M8 6V4h8v2" />
            <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
            @break

        @case('utensils')
            <path d="M4 3v7" />
            <path d="M7 3v7" />
            <path d="M4 7h3" />
            <path d="M5.5 10v11" />
            <path d="M17 3v18" />
            <path d="M14 3c0 4 0 7 3 7" />
            @break

        @case('user')
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21a8 8 0 0 1 16 0" />
            @break

        @case('user-plus')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M19 8v6" />
            <path d="M22 11h-6" />
            @break

        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.9" />
            <path d="M16 3.1a4 4 0 0 1 0 7.8" />
            @break

        @case('x')
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
