<?php defined('BASEPATH') OR exit('No direct script access allowed');
$active_nav = isset($active_nav) ? $active_nav : '';
$page_title = isset($page_title) ? $page_title : 'Учёт токенов СКЗИ';
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-name" content="<?= htmlspecialchars($this->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="csrf-hash" content="<?= htmlspecialchars($this->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> · СКЗИ</title>
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/tom-select/tom-select.bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <script>window.SKZI_BASE_URL = '<?= base_url() ?>';</script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-skzi">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?= site_url('tokens') ?>">
            <i class="bi bi-shield-lock"></i> Учёт токенов СКЗИ
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'tokens' ? 'active' : '' ?>" href="<?= site_url('tokens') ?>">Токены</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'employees' ? 'active' : '' ?>" href="<?= site_url('employees') ?>">Сотрудники</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container-fluid px-4 mt-4 mb-5">
    <h1 class="page-title"><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h1>
