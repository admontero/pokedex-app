/**
 * Light/dark theme switch.
 *
 * The initial theme is applied by an inline script in <head> so the page does
 * not flash the wrong colors. This module only handles the toggle, keeps the
 * button state in sync and follows the system preference while the user has
 * not chosen a theme explicitly.
 */

const STORAGE_KEY = 'pk-theme';
const root = document.documentElement;

const readStoredTheme = () => {
    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);

        return stored === 'dark' || stored === 'light' ? stored : null;
    } catch (error) {
        return null;
    }
};

const currentTheme = () => (root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light');

const syncToggles = () => {
    const isDark = currentTheme() === 'dark';
    const title = isDark ? 'Desactivar modo oscuro' : 'Activar modo oscuro';

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.title = title;
    });
};

const applyTheme = (theme, persist = true) => {
    root.setAttribute('data-bs-theme', theme);

    if (persist) {
        try {
            window.localStorage.setItem(STORAGE_KEY, theme);
        } catch (error) {
            // Private mode or a full quota: the theme still applies to this page.
        }
    }

    syncToggles();
};

const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)');

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
    });
});

const handleSystemChange = (event) => {
    // An explicit choice wins over the system preference.
    if (readStoredTheme() === null) {
        applyTheme(event.matches ? 'dark' : 'light', false);
    }
};

if (typeof systemPrefersDark.addEventListener === 'function') {
    systemPrefersDark.addEventListener('change', handleSystemChange);
} else {
    systemPrefersDark.addListener(handleSystemChange);
}

syncToggles();
