(function () {
    'use strict';

    var STORAGE_KEY = 'skzi-theme';
    var ALT_KEY = 'theme';
    var DEFAULT_THEME = 'skzi-light';
    var DEFAULT_DARK_THEME = 'theme-midnight';

    var CATALOG = [
        { id: 'skzi-light', label: 'Светлая', group: 'Базовые', tone: 'light', accent: '#1aa1b0' },
        { id: 'skzi-dark', label: 'Тёмная', group: 'Базовые', tone: 'dark', accent: '#5865f2' },
        { id: 'theme-sakura', label: 'Сакура', group: 'Светлые', tone: 'light', accent: '#e84a8a' },
        { id: 'theme-linen', label: 'Лён', group: 'Светлые', tone: 'light', accent: '#c45c26' },
        { id: 'theme-sage', label: 'Шалфей', group: 'Светлые', tone: 'light', accent: '#2d6a4f' },
        { id: 'theme-frost', label: 'Иней', group: 'Светлые', tone: 'light', accent: '#0e7490' },
        { id: 'theme-midnight', label: 'Полночь', group: 'Тёмные', tone: 'dark', accent: '#6366f1' },
        { id: 'theme-obsidian', label: 'Обсидиан', group: 'Тёмные', tone: 'dark', accent: '#22d3ee' },
        { id: 'theme-aurora', label: 'Аврора', group: 'Тёмные', tone: 'dark', accent: '#34d399' },
        { id: 'theme-plum', label: 'Слива', group: 'Тёмные', tone: 'dark', accent: '#c084fc' },
    ];

    var THEME_IDS = CATALOG.map(function (item) { return item.id; });

    var LEGACY_ROOT_CLASSES = [
        'skzi-light', 'skzi-dark',
        'theme-sakura', 'theme-dawn', 'theme-linen', 'theme-sage', 'theme-frost',
        'theme-midnight', 'theme-obsidian', 'theme-aurora', 'theme-plum',
        'dark', 'light', 'green', 'amber', 'wb', 'purple', 'nord', 'rose-pine', 'github',
        'kanagawa', 'white', 'solarized-light', 'cream', 'blush', 'mist', 'spearmint',
        'lilac', 'dune', 'porcelain', 'coral', 'paper', 'sky', 'catppuccin', 'tokyonight',
        'everforest', 'gruvbox', 'dracula', 'onedark', 'solarized-dark', 'monokai',
    ];

    var LEGACY_LIGHT = {
        white: 1, light: 1, green: 1, amber: 1, wb: 1, purple: 1, cream: 1, blush: 1,
        mist: 1, spearmint: 1, lilac: 1, dune: 1, porcelain: 1, coral: 1, paper: 1, sky: 1,
        'skzi-light': 1, 'solarized-light': 1,
        'theme-sakura': 1, 'theme-linen': 1, 'theme-sage': 1, 'theme-frost': 1,
    };

    var CATALOG_BY_ID = {};
    CATALOG.forEach(function (item) {
        CATALOG_BY_ID[item.id] = item;
    });

    function isKnownTheme(theme) {
        return !!CATALOG_BY_ID[theme];
    }

    function normalizeTheme(raw) {
        if (raw === 'light') {
            return 'skzi-light';
        }
        if (raw === 'dark') {
            return 'skzi-dark';
        }
        if (raw === 'theme-dawn') {
            return 'theme-sakura';
        }
        if (isKnownTheme(raw)) {
            return raw;
        }
        if (raw && LEGACY_LIGHT[raw]) {
            return DEFAULT_THEME;
        }
        if (raw) {
            return DEFAULT_DARK_THEME;
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
        return DEFAULT_THEME;
    }

    function themeTone(theme) {
        return (CATALOG_BY_ID[theme] && CATALOG_BY_ID[theme].tone) || 'light';
    }

    function applyDocumentThemeClass(theme) {
        if (!isKnownTheme(theme)) {
            theme = DEFAULT_THEME;
        }
        var root = document.documentElement;
        var i;
        for (i = 0; i < LEGACY_ROOT_CLASSES.length; i++) {
            root.classList.remove(LEGACY_ROOT_CLASSES[i]);
        }
        root.classList.add(theme);
        root.setAttribute('data-skzi-tone', themeTone(theme));
    }

    function persistTheme(theme) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
            localStorage.removeItem(ALT_KEY);
        } catch (e) { /* private mode */ }
    }

    function toggleTheme() {
        var current = resolveInitialTheme();
        var next = themeTone(current) === 'dark' ? DEFAULT_THEME : DEFAULT_DARK_THEME;
        window.SkziTheme.set(next);
    }

    function updatePickerUi(theme) {
        var menu = document.getElementById('themePickerMenu');
        if (!menu) {
            return;
        }
        menu.querySelectorAll('.theme-picker-item').forEach(function (btn) {
            var active = btn.getAttribute('data-theme') === theme;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-checked', active ? 'true' : 'false');
        });
    }

    function buildPickerMenu() {
        var menu = document.getElementById('themePickerMenu');
        if (!menu || menu.childElementCount) {
            return;
        }

        var groups = [];
        CATALOG.forEach(function (item) {
            if (groups.indexOf(item.group) < 0) {
                groups.push(item.group);
            }
        });

        groups.forEach(function (group) {
            var header = document.createElement('div');
            header.className = 'dropdown-header';
            header.textContent = group;
            menu.appendChild(header);

            CATALOG.forEach(function (item) {
                if (item.group !== group) {
                    return;
                }
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dropdown-item theme-picker-item';
                btn.setAttribute('data-theme', item.id);
                btn.setAttribute('role', 'menuitemradio');
                btn.setAttribute('aria-checked', 'false');
                btn.innerHTML = ''
                    + '<span class="theme-picker-swatch" style="--theme-swatch:' + item.accent + '"></span>'
                    + '<span class="theme-picker-label">' + item.label + '</span>';
                btn.addEventListener('click', function () {
                    window.SkziTheme.set(item.id);
                    var toggle = document.getElementById('themePickerToggle');
                    if (toggle && window.jQuery) {
                        window.jQuery(toggle).dropdown('hide');
                    }
                });
                menu.appendChild(btn);
            });
        });
    }

    function initPicker() {
        buildPickerMenu();
        updatePickerUi(resolveInitialTheme());
    }

    window.SkziTheme = {
        CATALOG: CATALOG,
        THEME_IDS: THEME_IDS,
        DEFAULT_THEME: DEFAULT_THEME,
        DEFAULT_DARK_THEME: DEFAULT_DARK_THEME,
        resolveInitialTheme: resolveInitialTheme,
        applyDocumentThemeClass: applyDocumentThemeClass,
        readStoredTheme: readStoredTheme,
        toggle: toggleTheme,
        set: function (theme) {
            if (!isKnownTheme(theme)) {
                theme = DEFAULT_THEME;
            }
            applyDocumentThemeClass(theme);
            persistTheme(theme);
            updatePickerUi(theme);
        },
        get: function () {
            return resolveInitialTheme();
        },
    };

    document.addEventListener('DOMContentLoaded', initPicker);
})();
