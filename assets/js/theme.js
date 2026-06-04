(function () {
    'use strict';

    var STORAGE_KEY = 'theme';
    var LEGACY_KEY = 'skzi-theme';
    var DEFAULT_THEME = 'skzi-light';

    var THEMES = [
        'skzi-light', 'skzi-dark',
        'white', 'light', 'green', 'amber', 'wb', 'purple', 'cream', 'blush', 'mist',
        'spearmint', 'lilac', 'dune', 'porcelain', 'coral', 'paper', 'sky',
        'dark', 'github', 'rose-pine', 'kanagawa', 'catppuccin', 'tokyonight',
        'everforest', 'gruvbox', 'dracula', 'onedark', 'monokai', 'solarized-dark',
    ];

    var LIGHT_THEMES = {
        'skzi-light': 1,
        white: 1, light: 1, green: 1, amber: 1, wb: 1, purple: 1, cream: 1, blush: 1,
        mist: 1, spearmint: 1, lilac: 1, dune: 1, porcelain: 1, coral: 1, paper: 1, sky: 1,
    };

    var ROOT_THEME_CLASSES = [
        'skzi-light', 'skzi-dark',
        'dark', 'light', 'green', 'amber', 'wb', 'purple', 'nord', 'rose-pine', 'github',
        'kanagawa', 'white', 'solarized-light', 'cream', 'blush', 'mist', 'spearmint',
        'lilac', 'dune', 'porcelain', 'coral', 'paper', 'sky', 'catppuccin', 'tokyonight',
        'everforest', 'gruvbox', 'dracula', 'onedark', 'solarized-dark', 'monokai',
    ];

    var THEME_TITLES = {
        'skzi-light': 'Светлая (классика)',
        'skzi-dark': 'Тёмная (Discord)',
        light: 'Светлая',
        dark: 'Тёмная',
        green: 'Зелёная',
        amber: 'Янтарная',
        wb: 'Wildberries',
        purple: 'Фиолетовая',
        'rose-pine': 'Rosé Pine',
        github: 'GitHub',
        kanagawa: 'Kanagawa',
        catppuccin: 'Catppuccin',
        tokyonight: 'Tokyo Night',
        everforest: 'Everforest',
        gruvbox: 'Gruvbox',
        dracula: 'Dracula',
        onedark: 'One Dark',
        monokai: 'Monokai',
        'solarized-dark': 'Solarized Dark',
        white: 'Белая',
        cream: 'Кремовая',
        blush: 'Пудровая',
        mist: 'Туман',
        spearmint: 'Мята',
        lilac: 'Сирень',
        dune: 'Дюны',
        porcelain: 'Фарфор',
        coral: 'Коралл',
        paper: 'Бумага',
        sky: 'Небо',
    };

    var THEME_ICONS = {
        'skzi-light': 'bi-sun-fill',
        'skzi-dark': 'bi-moon-fill',
        light: 'bi-sun',
        dark: 'bi-moon-stars',
        green: 'bi-flower1',
        amber: 'bi-fire',
        wb: 'bi-bag',
        purple: 'bi-stars',
        'rose-pine': 'bi-flower2',
        github: 'bi-terminal',
        kanagawa: 'bi-water',
        catppuccin: 'bi-palette',
        tokyonight: 'bi-star',
        everforest: 'bi-tree',
        gruvbox: 'bi-boxes',
        dracula: 'bi-emoji-ghost',
        onedark: 'bi-code-slash',
        monokai: 'bi-lightning',
        'solarized-dark': 'bi-eclipse',
        white: 'bi-cloud-sun',
        cream: 'bi-cup-hot',
        blush: 'bi-heart',
        mist: 'bi-cloud-fog2',
        spearmint: 'bi-moisture',
        lilac: 'bi-gem',
        dune: 'bi-mountain',
        porcelain: 'bi-circle',
        coral: 'bi-shell',
        paper: 'bi-journal-text',
        sky: 'bi-cloud',
    };

    var THEME_PREVIEW = {
        'skzi-light': { bg: 'rgb(246, 247, 249)', accent: 'rgb(26, 161, 176)' },
        'skzi-dark': { bg: 'rgb(49, 51, 56)', accent: 'rgb(88, 101, 242)' },
        white: { bg: 'rgb(252, 253, 255)', accent: 'rgb(0, 150, 184)' },
        light: { bg: 'rgb(240, 242, 245)', accent: 'rgb(0, 150, 184)' },
        green: { bg: 'rgb(238, 245, 235)', accent: 'rgb(61, 122, 79)' },
        amber: { bg: 'rgb(250, 246, 238)', accent: 'rgb(180, 83, 9)' },
        wb: { bg: 'rgb(250, 243, 249)', accent: 'rgb(203, 17, 171)' },
        purple: { bg: 'rgb(238, 230, 250)', accent: 'rgb(124, 58, 237)' },
        dark: { bg: 'rgb(32, 34, 37)', accent: 'rgb(0, 150, 184)' },
        github: { bg: 'rgb(13, 17, 23)', accent: 'rgb(88, 166, 255)' },
        'rose-pine': { bg: 'rgb(25, 23, 36)', accent: 'rgb(184, 84, 112)' },
        kanagawa: { bg: 'rgb(31, 31, 40)', accent: 'rgb(62, 128, 138)' },
        catppuccin: { bg: 'rgb(30, 30, 46)', accent: 'rgb(137, 180, 250)' },
        tokyonight: { bg: 'rgb(26, 27, 38)', accent: 'rgb(122, 162, 247)' },
        everforest: { bg: 'rgb(45, 53, 59)', accent: 'rgb(127, 187, 179)' },
        gruvbox: { bg: 'rgb(29, 32, 33)', accent: 'rgb(254, 128, 25)' },
        dracula: { bg: 'rgb(40, 42, 54)', accent: 'rgb(189, 147, 249)' },
        onedark: { bg: 'rgb(40, 44, 52)', accent: 'rgb(97, 175, 239)' },
        monokai: { bg: 'rgb(39, 40, 34)', accent: 'rgb(253, 151, 31)' },
        'solarized-dark': { bg: 'rgb(0, 43, 54)', accent: 'rgb(42, 161, 152)' },
        cream: { bg: 'rgb(250, 248, 243)', accent: 'rgb(180, 83, 9)' },
        blush: { bg: 'rgb(253, 242, 248)', accent: 'rgb(219, 39, 119)' },
        mist: { bg: 'rgb(241, 245, 249)', accent: 'rgb(3, 105, 161)' },
        spearmint: { bg: 'rgb(240, 253, 244)', accent: 'rgb(5, 150, 105)' },
        lilac: { bg: 'rgb(250, 245, 255)', accent: 'rgb(124, 58, 237)' },
        dune: { bg: 'rgb(250, 246, 239)', accent: 'rgb(161, 98, 7)' },
        porcelain: { bg: 'rgb(248, 250, 252)', accent: 'rgb(14, 116, 144)' },
        coral: { bg: 'rgb(255, 247, 237)', accent: 'rgb(234, 88, 12)' },
        paper: { bg: 'rgb(252, 251, 249)', accent: 'rgb(79, 70, 229)' },
        sky: { bg: 'rgb(240, 249, 255)', accent: 'rgb(2, 132, 199)' },
    };

    var PANEL_MAX_HEIGHT = 280;
    var PANEL_OPEN_UP_THRESHOLD = 160;
    var PANEL_MIN_VIEW_HEIGHT = 96;

    function readStoredTheme() {
        var raw = localStorage.getItem(STORAGE_KEY);
        var legacy = localStorage.getItem(LEGACY_KEY);
        if (!raw && legacy) {
            if (legacy === 'light') {
                raw = 'skzi-light';
            } else if (legacy === 'dark') {
                raw = 'skzi-dark';
            } else {
                raw = legacy;
            }
        }
        if (raw === 'nord') {
            localStorage.setItem(STORAGE_KEY, 'rose-pine');
            return 'rose-pine';
        }
        if (raw === 'solarized-light') {
            localStorage.setItem(STORAGE_KEY, 'white');
            return 'white';
        }
        return raw;
    }

    function resolveInitialTheme() {
        var saved = readStoredTheme();
        if (saved && THEMES.indexOf(saved) >= 0) {
            return saved;
        }
        return DEFAULT_THEME;
    }

    function applyDocumentThemeClass(theme) {
        var root = document.documentElement;
        var i;
        for (i = 0; i < ROOT_THEME_CLASSES.length; i++) {
            root.classList.remove(ROOT_THEME_CLASSES[i]);
        }
        root.setAttribute('data-skzi-tone', LIGHT_THEMES[theme] ? 'light' : 'dark');
        /* «Тёмная» WebPortal — без класса, палитра :root */
        if (theme !== 'dark') {
            root.classList.add(theme);
        }
    }

    function persistTheme(theme) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
            localStorage.removeItem(LEGACY_KEY);
        } catch (e) { /* private mode */ }
    }

    window.SkziTheme = {
        THEMES: THEMES,
        DEFAULT_THEME: DEFAULT_THEME,
        resolveInitialTheme: resolveInitialTheme,
        applyDocumentThemeClass: applyDocumentThemeClass,
        readStoredTheme: readStoredTheme,
        getTitle: function (t) { return THEME_TITLES[t] || t; },
        getIcon: function (t) { return THEME_ICONS[t] || 'bi-palette'; },
        getPreview: function (t) { return THEME_PREVIEW[t] || { bg: '#888', accent: '#333' }; },
        isLight: function (t) { return !!LIGHT_THEMES[t]; },
        set: function (theme) {
            if (THEMES.indexOf(theme) < 0) {
                theme = DEFAULT_THEME;
            }
            applyDocumentThemeClass(theme);
            persistTheme(theme);
            if (window.SkziThemePicker) {
                window.SkziThemePicker.setCurrent(theme);
            }
        },
        get: function () {
            var saved = readStoredTheme();
            return saved && THEMES.indexOf(saved) >= 0 ? saved : DEFAULT_THEME;
        },
    };

    function ThemePicker() {
        this.theme = resolveInitialTheme();
        this.open = false;
        this.focusedIndex = 0;
        this.$root = null;
        this.$trigger = null;
        this.$panel = null;
        this.$list = null;
        this._scrollCloseHandlers = [];
    }

    ThemePicker.prototype.init = function () {
        this.$root = document.getElementById('themePicker');
        if (!this.$root) {
            return;
        }
        this.$trigger = this.$root.querySelector('.theme-picker-trigger');
        this.$panel = document.getElementById('themePickerPanel');
        if (this.$panel) {
            document.body.appendChild(this.$panel);
        }
        this.$list = this.$panel ? this.$panel.querySelector('[role="listbox"]') : null;
        this.renderList();
        this.setCurrent(this.theme);
        this.bindEvents();
    };

    ThemePicker.prototype.setCurrent = function (theme) {
        this.theme = theme;
        if (!this.$trigger) {
            return;
        }
        var title = THEME_TITLES[theme] || theme;
        var icon = THEME_ICONS[theme] || 'bi-palette';
        this.$trigger.querySelector('.theme-picker-label').textContent = title;
        var $icon = this.$trigger.querySelector('.theme-picker-icon i');
        if ($icon) {
            $icon.className = 'bi ' + icon;
        }
        this.updateSelectedInList();
    };

    ThemePicker.prototype.renderList = function () {
        if (!this.$list) {
            return;
        }
        var html = '';
        var self = this;
        THEMES.forEach(function (t, index) {
            var preview = THEME_PREVIEW[t];
            var title = THEME_TITLES[t] || t;
            var icon = THEME_ICONS[t] || 'bi-palette';
            html += ''
                + '<li role="presentation">'
                + '<button type="button" class="theme-picker-option" role="option" data-theme="' + t + '" data-index="' + index + '">'
                + '<span class="theme-picker-swatch" aria-hidden="true">'
                + '<span class="theme-picker-swatch-bg" style="background:' + preview.bg + '"></span>'
                + '<span class="theme-picker-swatch-accent" style="background:' + preview.accent + '"></span>'
                + '</span>'
                + '<i class="bi ' + icon + '" aria-hidden="true"></i>'
                + '<span class="theme-picker-option-label">' + title + '</span>'
                + '<i class="bi bi-check2 theme-picker-check" aria-hidden="true"></i>'
                + '</button>'
                + '</li>';
        });
        this.$list.innerHTML = html;
        this.$list.querySelectorAll('.theme-picker-option').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.selectTheme(btn.getAttribute('data-theme'));
            });
            btn.addEventListener('mouseenter', function () {
                self.focusedIndex = parseInt(btn.getAttribute('data-index'), 10) || 0;
                self.highlightFocused();
            });
        });
    };

    ThemePicker.prototype.updateSelectedInList = function () {
        if (!this.$list) {
            return;
        }
        this.$list.querySelectorAll('.theme-picker-option').forEach(function (btn) {
            var selected = btn.getAttribute('data-theme') === this.theme;
            btn.setAttribute('aria-selected', selected ? 'true' : 'false');
            btn.classList.toggle('is-selected', selected);
        }, this);
    };

    ThemePicker.prototype.highlightFocused = function () {
        if (!this.$list) {
            return;
        }
        var items = this.$list.querySelectorAll('.theme-picker-option');
        items.forEach(function (btn, i) {
            btn.classList.toggle('is-focused', i === this.focusedIndex);
        }, this);
    };

    ThemePicker.prototype.selectTheme = function (t) {
        window.SkziTheme.set(t);
        this.close();
    };

    ThemePicker.prototype.openPanel = function () {
        if (!this.$panel || !this.$trigger) {
            return;
        }
        this.open = true;
        this.$root.classList.add('is-open');
        this.$panel.classList.add('is-open');
        this.$trigger.setAttribute('aria-expanded', 'true');
        var idx = THEMES.indexOf(this.theme);
        this.focusedIndex = idx >= 0 ? idx : 0;
        this.highlightFocused();
        this.updateSelectedInList();
        this.positionPanel();
        this.attachScrollClose();
        if (this.$list) {
            this.$list.focus();
        }
    };

    ThemePicker.prototype.close = function () {
        this.open = false;
        this.detachScrollClose();
        if (this.$root) {
            this.$root.classList.remove('is-open');
        }
        if (this.$panel) {
            this.$panel.classList.remove('is-open');
        }
        if (this.$trigger) {
            this.$trigger.setAttribute('aria-expanded', 'false');
        }
    };

    ThemePicker.prototype.attachScrollClose = function () {
        var self = this;
        this.detachScrollClose();
        var el = this.$root ? this.$root.parentElement : null;
        var onScroll = function () { self.close(); };
        while (el) {
            var oy = window.getComputedStyle(el).overflowY;
            if (oy === 'auto' || oy === 'scroll' || oy === 'overlay') {
                el.addEventListener('scroll', onScroll, { passive: true });
                self._scrollCloseHandlers.push({ el: el, fn: onScroll });
            }
            el = el.parentElement;
        }
    };

    ThemePicker.prototype.detachScrollClose = function () {
        this._scrollCloseHandlers.forEach(function (h) {
            h.el.removeEventListener('scroll', h.fn);
        });
        this._scrollCloseHandlers = [];
    };

    ThemePicker.prototype.toggle = function () {
        if (this.open) {
            this.close();
        } else {
            this.openPanel();
        }
    };

    ThemePicker.prototype.positionPanel = function () {
        if (!this.$panel || !this.$trigger) {
            return;
        }
        var rect = this.$trigger.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom - 8;
        var spaceAbove = rect.top - 8;
        var openUp = spaceBelow < Math.min(PANEL_MAX_HEIGHT, PANEL_OPEN_UP_THRESHOLD) && spaceAbove > spaceBelow;
        var maxHeight = Math.min(
            PANEL_MAX_HEIGHT,
            Math.max(PANEL_MIN_VIEW_HEIGHT, openUp ? spaceAbove : spaceBelow)
        );
        var top = openUp ? rect.top - maxHeight - 4 : rect.bottom + 4;
        top = Math.max(8, Math.min(top, window.innerHeight - maxHeight - 8));
        this.$panel.style.top = top + 'px';
        this.$panel.style.left = rect.left + 'px';
        this.$panel.style.width = rect.width + 'px';
        this.$panel.style.maxHeight = maxHeight + 'px';
    };

    ThemePicker.prototype.bindEvents = function () {
        var self = this;

        this.$trigger.addEventListener('click', function () {
            self.toggle();
        });

        this.$trigger.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                self.toggle();
            }
        });

        if (this.$list) {
            this.$list.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    self.focusedIndex = Math.min(self.focusedIndex + 1, THEMES.length - 1);
                    self.highlightFocused();
                    self.scrollFocused();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    self.focusedIndex = Math.max(self.focusedIndex - 1, 0);
                    self.highlightFocused();
                    self.scrollFocused();
                } else if (e.key === 'Home') {
                    e.preventDefault();
                    self.focusedIndex = 0;
                    self.highlightFocused();
                    self.scrollFocused();
                } else if (e.key === 'End') {
                    e.preventDefault();
                    self.focusedIndex = THEMES.length - 1;
                    self.highlightFocused();
                    self.scrollFocused();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    self.selectTheme(THEMES[self.focusedIndex]);
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    self.close();
                    self.$trigger.focus();
                }
            });
        }

        document.addEventListener('mousedown', function (e) {
            if (!self.open) {
                return;
            }
            var t = e.target;
            if (self.$root.contains(t) || (self.$panel && self.$panel.contains(t))) {
                return;
            }
            self.close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' || !self.open) {
                return;
            }
            var active = document.activeElement;
            if (!self.$root.contains(active) && !(self.$panel && self.$panel.contains(active))) {
                return;
            }
            e.preventDefault();
            self.close();
            self.$trigger.focus();
        }, true);

        window.addEventListener('resize', function () {
            if (self.open) {
                self.positionPanel();
            }
        });
    };

    ThemePicker.prototype.scrollFocused = function () {
        var row = this.$list && this.$list.children[this.focusedIndex];
        if (row) {
            row.scrollIntoView({ block: 'nearest' });
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var picker = new ThemePicker();
        picker.init();
        window.SkziThemePicker = picker;
    });
})();
