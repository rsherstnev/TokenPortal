<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div id="employees-page" class="row">
    <div class="col-12 mb-4">
        <div class="skzi-section">
            <div class="skzi-section-header">
                <div class="skzi-section-title">
                    <span class="title"><i class="bi bi-people"></i>Сотрудники</span>
                    <span class="count">Записи: <span id="employees-count">0</span></span>
                </div>
                <button type="button" class="btn btn-skzi-primary" id="btn-add-employee">
                    <i class="bi bi-plus-lg"></i> Добавить
                </button>
            </div>
            <div class="mb-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="bi bi-search text-muted"></i></span>
                    </div>
                    <input type="text" class="form-control search-input border-left-0" id="employees-search"
                           placeholder="Поиск по имени, email, кабинету…">
                </div>
            </div>

            <div class="table-responsive">
                <table class="skzi-table">
                    <thead>
                        <tr>
                            <th>ФИО <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Email</th>
                            <th>Кабинет</th>
                            <th>Статус</th>
                            <th style="width: 1%;"></th>
                        </tr>
                    </thead>
                    <tbody id="employees-list">
                        <tr><td colspan="5" class="empty-cell">Загрузка…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================== Модалка сотрудника =============================== -->
<div class="modal fade skzi-modal" id="employeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="employeeForm" novalidate autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalTitle">Добавление сотрудника</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label-required">Фамилия</label>
                            <input type="text" class="form-control" name="lastname" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label-required">Имя</label>
                            <input type="text" class="form-control" name="firstname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Отчество</label>
                        <input type="text" class="form-control" name="patronymic">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-7">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" placeholder="user@example.com">
                        </div>
                        <div class="form-group col-md-5">
                            <label>Кабинет</label>
                            <input type="text" class="form-control" name="cabinet" placeholder="101">
                        </div>
                    </div>
                    <div class="toggle-row mb-0">
                        <div class="label">Активен</div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="employee-is-active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="employee-is-active"></label>
                        </div>
                        <div class="hint">Неактивные сотрудники не отображаются в форме передачи токена.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-skzi-primary">Сохранить</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>
