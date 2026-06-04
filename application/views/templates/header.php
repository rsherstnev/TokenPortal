<?php defined('BASEPATH') OR exit('No direct script access allowed');
$active_nav = isset($active_nav) ? $active_nav : '';
?><!DOCTYPE html>
<html lang="ru">
<head>
    <script>
    (function () {
        var THEMES = 'skzi-light skzi-dark white light green amber wb purple cream blush mist spearmint lilac dune porcelain coral paper sky dark github rose-pine kanagawa catppuccin tokyonight everforest gruvbox dracula onedark monokai solarized-dark'.split(' ');
        var LIGHT = { 'skzi-light':1, white:1, light:1, green:1, amber:1, wb:1, purple:1, cream:1, blush:1, mist:1, spearmint:1, lilac:1, dune:1, porcelain:1, coral:1, paper:1, sky:1 };
        var ROOT_CLS = 'skzi-light skzi-dark dark light green amber wb purple nord rose-pine github kanagawa white solarized-light cream blush mist spearmint lilac dune porcelain coral paper sky catppuccin tokyonight everforest gruvbox dracula onedark solarized-dark monokai'.split(' ');
        var raw = localStorage.getItem('theme');
        var legacy = localStorage.getItem('skzi-theme');
        if (!raw && legacy) {
            raw = legacy === 'light' ? 'skzi-light' : (legacy === 'dark' ? 'skzi-dark' : legacy);
        }
        if (raw === 'nord') { localStorage.setItem('theme', 'rose-pine'); raw = 'rose-pine'; }
        if (raw === 'solarized-light') { localStorage.setItem('theme', 'white'); raw = 'white'; }
        var theme = (raw && THEMES.indexOf(raw) >= 0) ? raw : 'skzi-light';
        var root = document.documentElement;
        for (var i = 0; i < ROOT_CLS.length; i++) { root.classList.remove(ROOT_CLS[i]); }
        root.setAttribute('data-skzi-tone', LIGHT[theme] ? 'light' : 'dark');
        if (theme !== 'dark') { root.classList.add(theme); }
    })();
    </script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-name" content="<?= htmlspecialchars($this->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="csrf-hash" content="<?= htmlspecialchars($this->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Учёт токенов</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/tom-select/tom-select.bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/themes.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <script>window.SKZI_BASE_URL = '<?= base_url() ?>';</script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-skzi">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?= site_url('tokens') ?>">
            <i class="bi bi-shield-lock"></i> Учёт токенов
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav skzi-nav-pills mr-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'tokens' ? 'active' : '' ?>" href="<?= site_url('tokens') ?>">
                        <i class="bi bi-key" aria-hidden="true"></i>Токены
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'transfer_history' ? 'active' : '' ?>" href="<?= site_url('transfer_history') ?>">
                        <i class="bi bi-clock-history" aria-hidden="true"></i>История передач
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'statistics' ? 'active' : '' ?>" href="<?= site_url('statistics') ?>">
                        <i class="bi bi-bar-chart" aria-hidden="true"></i>Статистика
                    </a>
                </li>
            </ul>
            <div class="theme-picker ml-auto" id="themePicker">
                <button type="button" class="theme-picker-trigger" aria-haspopup="listbox" aria-expanded="false" aria-label="Тема оформления">
                    <span class="theme-picker-icon" aria-hidden="true"><i class="bi bi-cloud-sun"></i></span>
                    <span class="theme-picker-label">Светлая (классика)</span>
                    <i class="bi bi-chevron-down theme-picker-chevron" aria-hidden="true"></i>
                </button>
            </div>
            <div class="theme-picker-panel" id="themePickerPanel">
                <ul role="listbox" aria-label="Тема оформления" tabindex="-1"></ul>
            </div>
        </div>
    </div>
</nav>
<main class="container-fluid px-4 mt-4 mb-5">
