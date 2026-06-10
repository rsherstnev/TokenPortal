<?php defined('BASEPATH') OR exit('No direct script access allowed');
$active_nav = isset($active_nav) ? $active_nav : '';
?><!DOCTYPE html>
<html lang="ru">
<head>
    <script src="<?= base_url('assets/js/theme-boot.js') ?>"></script>
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
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/themes.css') ?>">
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
            <div class="theme-picker dropdown ml-auto">
                <button type="button" class="theme-picker-toggle dropdown-toggle" id="themePickerToggle"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        aria-label="Тема оформления" title="Тема оформления">
                    <i class="bi bi-palette-fill" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right theme-picker-menu" id="themePickerMenu" role="menu" aria-labelledby="themePickerToggle"></div>
            </div>
        </div>
    </div>
</nav>
<main class="container-fluid px-4 mt-4 mb-5">
