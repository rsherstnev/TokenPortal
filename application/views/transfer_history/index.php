<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div id="transfer-history-page" class="row">
    <div class="col-12 mb-4">
        <div class="skzi-section">
            <div class="skzi-section-header">
                <div class="skzi-section-title">
                    <span class="title"><i class="bi bi-clock-history"></i>История передач</span>
                    <span class="count">Записи: <span id="transfer-history-count">0</span></span>
                </div>
            </div>
            <div class="transfer-history-filters mb-2">
                <div class="transfer-history-filters-search">
                    <div class="input-group search-input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text border-right-0"><i class="bi bi-search"></i></span>
                        </div>
                        <input type="text" class="form-control search-input border-left-0" id="transfer-history-search"
                               placeholder="Поиск по модели, серийному номеру, сотрудникам, комментарию…">
                    </div>
                </div>
                <div class="transfer-history-filters-dates">
                    <div class="transfer-history-filters-date">
                        <div class="input-group search-input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-right-0">С</span>
                            </div>
                            <input type="date" class="form-control search-input border-left-0" id="transfer-history-date-from"
                                   title="Начало периода" aria-label="Начало периода">
                        </div>
                    </div>
                    <div class="transfer-history-filters-date">
                        <div class="input-group search-input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-right-0">По</span>
                            </div>
                            <input type="date" class="form-control search-input border-left-0" id="transfer-history-date-to"
                                   title="Конец периода" aria-label="Конец периода">
                        </div>
                    </div>
                    <button type="button" class="btn btn-light transfer-history-reset-dates" id="transfer-history-reset-dates">
                        Сбросить даты
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="skzi-table">
                    <thead>
                        <tr>
                            <th data-sort-key="model_name">Модель <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="serial_number">Серийный номер <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="from_fullname">От кого <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="to_fullname">Кому <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="transferred_at">Дата передачи <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="comment">Комментарий <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th class="actions-cell-header">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="transfer-history-list">
                        <tr><td colspan="7" class="empty-cell">Загрузка…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('shared/_transfer_comment_modal'); ?>
