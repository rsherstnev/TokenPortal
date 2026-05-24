<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div id="tokens-page" class="row">

    <!-- ============================== Tokens column ===================== -->
    <div class="col-lg-8 mb-4">
        <div class="skzi-section">
            <div class="skzi-section-header">
                <div class="skzi-section-title">
                    <span class="title"><i class="bi bi-key"></i>Токены</span>
                    <span class="count">Записи: <span id="tokens-count">0</span></span>
                </div>
                <button type="button" class="btn btn-skzi-primary" id="btn-add-token">
                    <i class="bi bi-plus-lg"></i> Добавить
                </button>
            </div>
            <div class="row no-gutters mb-2">
                <div class="col-md-9 pr-md-2 mb-2 mb-md-0">
                    <input type="text" class="form-control search-input" id="tokens-search"
                               placeholder="Поиск по сотруднику, модели, серийному номеру…">
                </div>
                <div class="col-md-3">
                    <select class="custom-select" id="tokens-status">
                        <option value="all" selected>Все</option>
                        <option value="issued">Выданные</option>
                        <option value="not_issued">Невыданные</option>
                        <option value="broken">Сломанные</option>
                        <option value="lost">Утерянные</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="skzi-table">
                    <thead>
                        <tr>
                            <th>Сотрудник <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Модель <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Серийный номер <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Статус <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th style="width: 1%;"></th>
                        </tr>
                    </thead>
                    <tbody id="tokens-list">
                        <tr><td colspan="5" class="empty-cell">Загрузка…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================== Token Models column =============== -->
    <div class="col-lg-4 mb-4">
        <div class="skzi-section">
            <div class="skzi-section-header">
                <div class="skzi-section-title">
                    <span class="title"><i class="bi bi-collection"></i>Модели токенов</span>
                    <span class="count">Записи: <span id="models-count">0</span></span>
                </div>
                <button type="button" class="btn btn-skzi-primary" id="btn-add-model">
                    <i class="bi bi-plus-lg"></i> Добавить
                </button>
            </div>
            <div class="mb-2">
                <input type="text" class="form-control search-input" id="models-search"
                           placeholder="Поиск по модели…">
            </div>

            <div class="table-responsive">
                <table class="skzi-table">
                    <thead>
                        <tr>
                            <th>Модель <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th class="actions-cell">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="token-models-list">
                        <tr><td colspan="2" class="empty-cell">Загрузка…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('tokens/_modals'); ?>
