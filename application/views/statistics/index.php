<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div id="statistics-page" class="row">
    <div class="col-lg-5 mb-4">
        <div class="skzi-section">
            <div class="skzi-section-header">
                <div class="skzi-section-title">
                    <span class="title"><i class="bi bi-bar-chart"></i>Отделы с сотрудниками без токенов</span>
                </div>
            </div>
            <div id="statistics-chart" class="statistics-chart">
                <div class="empty-cell">Загрузка…</div>
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="skzi-section">
            <div class="skzi-section-header">
                <div class="skzi-section-title">
                    <span class="title"><i class="bi bi-person-x"></i>Без токена</span>
                    <span class="count">Записи: <span id="statistics-list-count">0</span></span>
                </div>
            </div>
            <div class="mb-2">
                <input type="text" class="form-control search-input" id="statistics-search"
                       placeholder="Поиск по ФИО, должности, отделу…">
            </div>

            <div class="table-responsive">
                <table class="skzi-table">
                    <thead>
                        <tr>
                            <th data-sort-key="person_name">ФИО <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="person_dolj">Должность <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="person_department">Отдел <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody id="statistics-list">
                        <tr><td colspan="3" class="empty-cell">Загрузка…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
