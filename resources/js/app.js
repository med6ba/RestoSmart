

import Alpine from 'alpinejs';

const themeStorageKey = 'restosmart-theme';

const preferredTheme = () => {
    const storedTheme = localStorage.getItem(themeStorageKey);

    if (storedTheme === 'dark' || storedTheme === 'light') {
        return storedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
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

window.checkoutFlow = (initialType, initialTableToken = '') => ({
    type: initialType || 'delivery',
    tableToken: initialTableToken || '',
    scanStatus: initialTableToken ? 'Table QR scanned.' : '',
    scanError: '',
    scanning: false,
    detector: null,
    stream: null,
    frame: null,

    setType(type) {
        this.type = type;

        if (type !== 'local') {
            this.stopScanner();
        }
    },

    async startScanner() {
        this.scanError = '';

        if (!('BarcodeDetector' in window)) {
            this.scanError = 'QR scanning is not available in this browser.';
            return;
        }

        try {
            this.detector = new BarcodeDetector({ formats: ['qr_code'] });
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });

            this.scanning = true;
            await this.$nextTick();
            this.$refs.tableVideo.srcObject = this.stream;
            await this.$refs.tableVideo.play();
            this.scanStatus = 'Scanning table QR...';
            this.scanFrame();
        } catch (error) {
            this.scanError = 'Camera access was not available.';
            this.stopScanner();
        }
    },

    async scanFrame() {
        if (!this.scanning || !this.detector || !this.$refs.tableVideo) {
            return;
        }

        try {
            const codes = await this.detector.detect(this.$refs.tableVideo);

            if (codes.length > 0) {
                this.applyScan(codes[0].rawValue || '');
                return;
            }
        } catch (error) {
            this.scanError = 'The QR code could not be read.';
        }

        this.frame = requestAnimationFrame(() => this.scanFrame());
    },

    applyScan(rawValue) {
        const token = this.extractTableToken(rawValue);

        if (!token) {
            this.scanError = 'This QR code is not a table QR.';
            return;
        }

        this.tableToken = token;
        this.scanStatus = 'Table QR scanned.';
        this.scanError = '';
        this.stopScanner();
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

window.driverLocationReporter = (routes = []) => ({
    watchId: null,

    start() {
        if (!navigator.geolocation || routes.length === 0) {
            return;
        }

        this.watchId = navigator.geolocation.watchPosition(
            (position) => this.publish(position.coords),
            () => {},
            { enableHighAccuracy: true, maximumAge: 10000, timeout: 10000 },
        );
    },

    publish(coords) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        routes.forEach((route) => {
            fetch(route, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    latitude: coords.latitude,
                    longitude: coords.longitude,
                }),
            }).catch(() => {});
        });
    },
});

window.Alpine = Alpine;

Alpine.start();
