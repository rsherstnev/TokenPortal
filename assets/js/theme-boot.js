(function () {
    'use strict';

    var DEFAULT_THEME = 'skzi-light';
    var DEFAULT_DARK_THEME = 'theme-midnight';
    var KNOWN = {
        'skzi-light': 1, 'skzi-dark': 1,
        'theme-sakura': 1, 'theme-linen': 1, 'theme-sage': 1, 'theme-frost': 1,
        'theme-midnight': 1, 'theme-obsidian': 1, 'theme-aurora': 1, 'theme-plum': 1,
    };
    var LIGHT_THEMES = {
        'skzi-light': 1,
        'theme-sakura': 1, 'theme-linen': 1, 'theme-sage': 1, 'theme-frost': 1,
    };
    var LEGACY_LIGHT = {
        white: 1, light: 1, green: 1, amber: 1, wb: 1, purple: 1, cream: 1, blush: 1,
        mist: 1, spearmint: 1, lilac: 1, dune: 1, porcelain: 1, coral: 1, paper: 1, sky: 1,
        'skzi-light': 1, 'solarized-light': 1,
    };
    var ROOT_CLS = [
        'skzi-light', 'skzi-dark',
        'theme-sakura', 'theme-dawn', 'theme-linen', 'theme-sage', 'theme-frost',
        'theme-midnight', 'theme-obsidian', 'theme-aurora', 'theme-plum',
        'dark', 'light', 'green', 'amber', 'wb', 'purple',
        'nord', 'rose-pine', 'github', 'kanagawa', 'white', 'solarized-light', 'cream',
        'blush', 'mist', 'spearmint', 'lilac', 'dune', 'porcelain', 'coral', 'paper',
        'sky', 'catppuccin', 'tokyonight', 'everforest', 'gruvbox', 'dracula', 'onedark',
        'solarized-dark', 'monokai',
    ];

    function resolveTheme(raw) {
        if (raw === 'light') {
            return 'skzi-light';
        }
        if (raw === 'dark') {
            return 'skzi-dark';
        }
        if (raw === 'theme-dawn') {
            return 'theme-sakura';
        }
        if (raw && KNOWN[raw]) {
            return raw;
        }
        if (raw && LEGACY_LIGHT[raw]) {
            return DEFAULT_THEME;
        }
        if (raw) {
            return DEFAULT_DARK_THEME;
        }
        return DEFAULT_THEME;
    }

    var raw = localStorage.getItem('skzi-theme') || localStorage.getItem('theme');
    var theme = resolveTheme(raw);
    var root = document.documentElement;
    var i;
    for (i = 0; i < ROOT_CLS.length; i++) {
        root.classList.remove(ROOT_CLS[i]);
    }
    root.classList.add(theme);
    root.setAttribute('data-skzi-tone', LIGHT_THEMES[theme] ? 'light' : 'dark');
})();
