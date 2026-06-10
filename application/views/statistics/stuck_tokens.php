<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('statistics/_nav'); ?>
<div id="statistics-page" class="row statistics-page--single" data-section="stuck">
    <div class="col-12">
        <div class="skzi-section">
            <div class="skzi-section-header">
                <div class="skzi-section-title">
                    <span class="title"><i class="bi bi-exclamation-triangle"></i>Зависшие токены</span>
                    <span class="count">Записи: <span id="statistics-stuck-count">0</span></span>
                </div>
            </div>
            <div class="mb-2">
                <input type="text" class="form-control search-input" id="statistics-stuck-search"
                       placeholder="Поиск по ФИО, отделу, должности, модели, серийному номеру…">
            </div>
            <div class="table-responsive">
                <table class="skzi-table">
                    <thead>
                        <tr>
                            <th data-sort-key="person_name">ФИО <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="person_department">Отдел <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="person_dolj">Должность <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="model_name">Модель <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="serial_number">Серийный номер <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody id="statistics-stuck-list">
                        <tr><td colspan="5" class="empty-cell">Загрузка…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
