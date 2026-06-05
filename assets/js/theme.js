(function () {
    'use strict';

    var STORAGE_KEY = 'skzi-theme';
    var ALT_KEY = 'theme';
    var LIGHT_THEME = 'skzi-light';
    var DARK_THEME = 'skzi-dark';
    var THEMES = [LIGHT_THEME, DARK_THEME];

    /* Снятие классов старых палитр WebPortal при миграции */
    var LEGACY_ROOT_CLASSES = [
        'skzi-light', 'skzi-dark',
        'dark', 'light', 'green', 'amber', 'wb', 'purple', 'nord', 'rose-pine', 'github',
        'kanagawa', 'white', 'solarized-light', 'cream', 'blush', 'mist', 'spearmint',
        'lilac', 'dune', 'porcelain', 'coral', 'paper', 'sky', 'catppuccin', 'tokyonight',
        'everforest', 'gruvbox', 'dracula', 'onedark', 'solarized-dark', 'monokai',
    ];

    var LEGACY_LIGHT = {
        white: 1, light: 1, green: 1, amber: 1, wb: 1, purple: 1, cream: 1, blush: 1,
        mist: 1, spearmint: 1, lilac: 1, dune: 1, porcelain: 1, coral: 1, paper: 1, sky: 1,
        'skzi-light': 1, 'solarized-light': 1,
    };

    function normalizeTheme(raw) {
        if (raw === 'light' || raw === LIGHT_THEME) {
            return LIGHT_THEME;
        }
        if (raw === 'dark' || raw === DARK_THEME) {
            return DARK_THEME;
        }
        if (raw && LEGACY_LIGHT[raw]) {
            return LIGHT_THEME;
        }
        if (raw) {
            return DARK_THEME;
        }
        return null;
    }

    function readStoredTheme() {
        var raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) {
            raw = localStorage.getItem(ALT_KEY);
        }
        return normalizeTheme(raw);
    }

    function resolveInitialTheme() {
        var saved = readStoredTheme();
        if (saved) {
            return saved;
        }
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return DARK_THEME;
        }
        return LIGHT_THEME;
    }

    function applyDocumentThemeClass(theme) {
        if (THEMES.indexOf(theme) < 0) {
            theme = LIGHT_THEME;
        }
        var root = document.documentElement;
        var i;
        for (i = 0; i < LEGACY_ROOT_CLASSES.length; i++) {
            root.classList.remove(LEGACY_ROOT_CLASSES[i]);
        }
        root.classList.add(theme);
        root.setAttribute('data-skzi-tone', theme === LIGHT_THEME ? 'light' : 'dark');
    }

    function persistTheme(theme) {
        try {
            localStorage.setItem(STORAGE_KEY, theme === DARK_THEME ? 'dark' : 'light');
            localStorage.removeItem(ALT_KEY);
        } catch (e) { /* private mode */ }
    }

    function toggleTheme() {
        var next = resolveInitialTheme() === DARK_THEME ? LIGHT_THEME : DARK_THEME;
        window.SkziTheme.set(next);
    }

    window.SkziTheme = {
        THEMES: THEMES,
        LIGHT_THEME: LIGHT_THEME,
        DARK_THEME: DARK_THEME,
        resolveInitialTheme: resolveInitialTheme,
        applyDocumentThemeClass: applyDocumentThemeClass,
        readStoredTheme: readStoredTheme,
        toggle: toggleTheme,
        set: function (theme) {
            applyDocumentThemeClass(theme);
            persistTheme(theme);
        },
        get: function () {
            return resolveInitialTheme();
        },
    };

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('themeToggle');
        if (btn) {
            btn.addEventListener('click', toggleTheme);
        }
    });
})();
