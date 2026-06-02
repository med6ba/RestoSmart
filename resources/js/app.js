
import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import jsQR from 'jsqr';
import Pusher from 'pusher-js';

const themeStorageKey = 'restosmart-theme';

const preferredTheme = () => {
    const storedTheme = localStorage.getItem(themeStorageKey);

    if (storedTheme === 'dark' || storedTheme === 'light') {
        return storedTheme;
    }

    return 'light';
};

const applyTheme = (theme) => {
    const selectedTheme = theme === 'dark' ? 'dark' : 'light';

    document.documentElement.classList.toggle('dark', selectedTheme === 'dark');
    document.documentElement.dataset.theme = selectedTheme;
};

applyTheme(preferredTheme());

window.themeSwitcher = () => ({
    theme: document.documentElement.dataset.theme || 'light',
    setTheme(theme) {
        this.theme = theme === 'dark' ? 'dark' : 'light';
        localStorage.setItem(themeStorageKey, this.theme);
        applyTheme(this.theme);
    },
});

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT || 80),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        },
    });
}

const showRealtimeToast = (message) => {
    if (!message) {
        return;
    }

    const toast = document.createElement('div');
    toast.className = 'fixed right-4 top-4 z-[80] max-w-sm rounded-lg border border-brand-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 shadow-lg dark:border-brand-900/60 dark:bg-zinc-900 dark:text-white';
    toast.textContent = message;
    document.body.appendChild(toast);

    window.setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity');
        window.setTimeout(() => toast.remove(), 250);
    }, 2400);
};

const setupTenantRealtime = () => {
    const tenantId = document.querySelector('meta[name="tenant-id"]')?.getAttribute('content');
    const userRole = document.querySelector('meta[name="user-role"]')?.getAttribute('content');

    if (!window.Echo || !tenantId || !userRole) {
        return;
    }

    window.Echo
        .private(`tenant.${tenantId}.role.${userRole}`)
        .listen('.tenant.updated', (event) => {
            window.dispatchEvent(new CustomEvent('restosmart:tenant-updated', { detail: event }));
            showRealtimeToast(event.message);

            const scopeElement = document.querySelector('[data-realtime-scope]');

            if (!scopeElement) {
                return;
            }

            const scopes = String(scopeElement.dataset.realtimeScope || '')
                .split(',')
                .map((scope) => scope.trim())
                .filter(Boolean);

            if (!scopes.includes('*') && !scopes.includes(event.area)) {
                return;
            }

            window.clearTimeout(window.__restosmartRealtimeReload);
            window.__restosmartRealtimeReload = window.setTimeout(() => window.location.reload(), 700);
        });
};

window.sidebarShell = () => ({
    sidebarOpen: false,
    collapsed: false,
    width: 288,
    resizing: false,

    init() {
        this.collapsed = localStorage.getItem('restosmart-sidebar-collapsed') === 'true';

        const storedWidth = Number(localStorage.getItem('restosmart-sidebar-width'));

        if (storedWidth >= 224 && storedWidth <= 420) {
            this.width = storedWidth;
        }
    },

    effectiveWidth() {
        return this.collapsed ? 80 : this.width;
    },

    toggleSidebarLabels() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('restosmart-sidebar-collapsed', this.collapsed ? 'true' : 'false');
    },

    resetSidebarWidth() {
        this.width = 288;
        localStorage.setItem('restosmart-sidebar-width', String(this.width));
    },

    startResize(event) {
        if (this.collapsed) {
            this.collapsed = false;
            localStorage.setItem('restosmart-sidebar-collapsed', 'false');
        }

        this.resizing = true;
        document.body.style.cursor = 'ew-resize';
        document.body.style.userSelect = 'none';

        const move = (moveEvent) => {
            const isRtl = document.documentElement.dir === 'rtl';
            const nextWidth = isRtl ? window.innerWidth - moveEvent.clientX : moveEvent.clientX;

            if (nextWidth < 180) {
                this.collapsed = true;
                localStorage.setItem('restosmart-sidebar-collapsed', 'true');
                return;
            }

            this.collapsed = false;
            localStorage.setItem('restosmart-sidebar-collapsed', 'false');
            this.width = Math.min(420, Math.max(224, nextWidth));
        };

        const stop = () => {
            this.resizing = false;
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            localStorage.setItem('restosmart-sidebar-width', String(Math.round(this.width)));
            window.removeEventListener('mousemove', move);
            window.removeEventListener('mouseup', stop);
        };

        move(event);
        window.addEventListener('mousemove', move);
        window.addEventListener('mouseup', stop, { once: true });
    },
});

window.liveClock = () => ({
    currentTime: '--:--:--',
    currentDate: '',
    timezoneName: '',
    timezoneOffset: '',
    interval: null,

    initClock() {
        try {
            this.timezoneName = Intl.DateTimeFormat().resolvedOptions().timeZone.toUpperCase();
        } catch (e) {
            this.timezoneName = 'UTC';
        }
        
        this.updateTime();
        this.interval = setInterval(() => this.updateTime(), 1000);
    },

    updateTime() {
        const now = new Date();
        this.currentTime = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        
        const lang = document.documentElement.lang || 'en-US';
        this.currentDate = now.toLocaleDateString(lang, { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        
        const offset = -now.getTimezoneOffset();
        const sign = offset >= 0 ? '+' : '-';
        const hours = Math.floor(Math.abs(offset) / 60);
        this.timezoneOffset = `(UTC${sign}${hours})`;
    }
});

window.notificationsDropdown = () => ({
    open: false,
    notifications: [],
    unreadCount: 0,
    
    init() {
        this.fetchNotifications();
        
        const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        if (window.Echo && userId) {
            window.Echo.private(`user.${userId}`)
                .listen('.notification.sent', (e) => {
                    this.notifications.unshift(e.notification);
                    this.unreadCount++;
                    
                    if (typeof showRealtimeToast === 'function') {
                        showRealtimeToast(e.notification.title);
                    }
                });
        }
    },
    
    toggle() {
        this.open = !this.open;
    },
    
    close() {
        this.open = false;
    },
    
    async fetchNotifications() {
        try {
            const response = await fetch('/api/notifications', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            const data = await response.json();
            this.notifications = data.notifications || [];
            this.unreadCount = data.unreadCount || 0;
        } catch (error) {
            console.error('Failed to fetch notifications', error);
        }
    },
    
    async markAllAsRead() {
        if (this.unreadCount === 0) return;
        
        this.unreadCount = 0;
        this.notifications = this.notifications.map(n => ({...n, read_at: new Date().toISOString()}));
        
        try {
            await fetch('/api/notifications/read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
        } catch (error) {
            console.error('Failed to mark notifications as read', error);
        }
    },
    
    formatTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    },
    
    getIconClass(type) {
        switch(type) {
            case 'success': return 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400';
            case 'warning': return 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400';
            case 'error': return 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400';
            default: return 'bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400';
        }
    }
});

window.checkoutFlow = (initialType, initialTableToken = '', messages = {}) => ({
    type: initialType || 'delivery',
    tableToken: initialTableToken || '',
    messages: {
        scanned: 'Table QR scanned.',
        scanning: 'Scanning table QR...',
        unsupported: 'QR scanning is not available in this browser.',
        insecure: 'Camera scanning requires HTTPS or localhost. Open this restaurant with a secure URL, or enter the table token manually.',
        camera: 'Camera access was not available.',
        unreadable: 'The QR code could not be read.',
        notTable: 'This QR code is not a table QR.',
        notRegistered: 'This table QR is not registered for this restaurant.',
        validationFailed: 'The table QR could not be validated. Please try again.',
        tableScanned: 'Table :table scanned.',
        ...messages,
    },
    scanStatus: initialTableToken ? (messages.scanned || 'Table QR scanned.') : '',
    scanError: '',
    scanning: false,
    validateUrl: '',
    detector: null,
    stream: null,
    frame: null,
    canvas: null,
    canvasContext: null,

    setType(type) {
        this.type = type;

        if (type !== 'local') {
            this.stopScanner();
        }
    },

    setValidateUrl(url) {
        this.validateUrl = url || '';
    },

    async startScanner() {
        this.scanError = '';

        if (!window.isSecureContext && !['localhost', '127.0.0.1', '::1'].includes(window.location.hostname)) {
            this.scanError = this.messages.insecure;
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            this.scanError = this.messages.unsupported;
            return;
        }

        try {
            this.detector = null;

            if ('BarcodeDetector' in window) {
                try {
                    this.detector = new BarcodeDetector({ formats: ['qr_code'] });
                } catch (error) {
                    this.detector = null;
                }
            }

            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });

            this.scanning = true;
            await this.$nextTick();
            this.$refs.tableVideo.srcObject = this.stream;
            await this.$refs.tableVideo.play();
            this.scanStatus = this.messages.scanning;
            this.scanFrame();
        } catch (error) {
            this.scanError = this.messages.camera;
            this.stopScanner();
        }
    },

    async scanFrame() {
        if (!this.scanning || !this.$refs.tableVideo) {
            return;
        }

        try {
            const rawValue = this.detector
                ? await this.detectWithBarcodeDetector()
                : this.detectWithJsQr();

            if (rawValue) {
                await this.applyScan(rawValue);
                return;
            }
        } catch (error) {
            this.scanError = this.messages.unreadable;
        }

        this.frame = requestAnimationFrame(() => this.scanFrame());
    },

    async detectWithBarcodeDetector() {
        const codes = await this.detector.detect(this.$refs.tableVideo);

        return codes[0]?.rawValue || '';
    },

    detectWithJsQr() {
        const video = this.$refs.tableVideo;

        if (!video.videoWidth || !video.videoHeight) {
            return '';
        }

        if (!this.canvas) {
            this.canvas = document.createElement('canvas');
            this.canvasContext = this.canvas.getContext('2d', { willReadFrequently: true });
        }

        if (!this.canvasContext) {
            return '';
        }

        this.canvas.width = video.videoWidth;
        this.canvas.height = video.videoHeight;
        this.canvasContext.drawImage(video, 0, 0, this.canvas.width, this.canvas.height);

        const imageData = this.canvasContext.getImageData(0, 0, this.canvas.width, this.canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: 'attemptBoth',
        });

        return code?.data || '';
    },

    async applyScan(rawValue) {
        const token = this.extractTableToken(rawValue);

        if (!token) {
            this.scanError = this.messages.notTable;
            this.frame = requestAnimationFrame(() => this.scanFrame());
            return;
        }

        const validation = await this.validateTableToken(token);

        if (!validation.ok) {
            this.tableToken = '';
            this.scanError = validation.message || 'This table QR is not registered for this restaurant.';
            this.scanStatus = '';
            this.stopScanner();
            return;
        }

        this.tableToken = token;
        this.scanStatus = validation.table
            ? this.messages.tableScanned.replace(':table', validation.table)
            : this.messages.scanned;
        this.scanError = '';
        this.stopScanner();
    },

    async validateTableToken(token) {
        if (!this.validateUrl) {
            return { ok: true };
        }

        try {
            const url = new URL(this.validateUrl, window.location.origin);
            url.searchParams.set('token', token);

            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return { ok: false, message: this.messages.notRegistered };
            }

            return await response.json();
        } catch (error) {
            return { ok: false, message: this.messages.validationFailed };
        }
    },

    extractTableToken(rawValue) {
        const value = String(rawValue || '').trim();

        if (!value) {
            return '';
        }

        try {
            const url = new URL(value);
            const queryToken = url.searchParams.get('table') || url.searchParams.get('token');

            if (queryToken) {
                return queryToken.trim();
            }

            return decodeURIComponent(url.pathname.split('/').filter(Boolean).pop() || '').trim();
        } catch (error) {
            return value;
        }
    },

    stopScanner() {
        if (this.frame) {
            cancelAnimationFrame(this.frame);
            this.frame = null;
        }

        if (this.stream) {
            this.stream.getTracks().forEach((track) => track.stop());
            this.stream = null;
        }

        this.scanning = false;
    },
});

window.Alpine = Alpine;

setupTenantRealtime();

Alpine.start();
