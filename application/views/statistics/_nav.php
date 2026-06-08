<?php defined('BASEPATH') OR exit('No direct script access allowed');
$active_statistics_tab = isset($active_statistics_tab) ? $active_statistics_tab : 'without_token';
?>
<nav class="skzi-statistics-nav mb-4" aria-label="Разделы статистики">
    <ul class="nav skzi-nav-pills">
        <li class="nav-item">
            <a class="nav-link <?= $active_statistics_tab === 'without_token' ? 'active' : '' ?>"
               href="<?= site_url('statistics/without_token') ?>">
                <i class="bi bi-person-x" aria-hidden="true"></i>Работники без токена
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $active_statistics_tab === 'multiple_tokens' ? 'active' : '' ?>"
               href="<?= site_url('statistics/multiple_tokens') ?>">
                <i class="bi bi-people" aria-hidden="true"></i>Работники с несколькими токенами
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $active_statistics_tab === 'stuck_tokens' ? 'active' : '' ?>"
               href="<?= site_url('statistics/stuck_tokens') ?>">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Зависшие токены
            </a>
        </li>
    </ul>
</nav>
