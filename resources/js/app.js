

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

window.Alpine = Alpine;

Alpine.start();
