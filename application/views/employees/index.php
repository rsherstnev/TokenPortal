<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div id="employees-page" class="row">
    <div class="col-12 mb-4">
        <div class="skzi-section">
            <div class="skzi-section-header">
                <div class="skzi-section-title">
                    <span class="title"><i class="bi bi-people"></i>Пользователи</span>
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
                           placeholder="Поиск по ФИО, кабинету, номеру удостоверения…">
                </div>
            </div>

            <div class="table-responsive">
                <table class="skzi-table">
                    <thead>
                        <tr>
                            <th data-sort-key="person_name">ФИО <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="person_dolj">Должн. <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="person_department">Отдел <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="cabinet">Кабинет <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="n_type">Тип надзора <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th data-sort-key="id_num">Уд-е № <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th style="width: 1%;"></th>
                        </tr>
                    </thead>
                    <tbody id="employees-list">
                        <tr><td colspan="7" class="empty-cell">Загрузка…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================== Модалка пользователя =============================== -->
<div class="modal fade skzi-modal" id="employeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form id="employeeForm" novalidate autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalTitle">Добавление пользователя</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">

                    <div class="form-group">
                        <label class="form-label-required">ФИО</label>
                        <input type="text" class="form-control" name="person_name" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label-required">Должность (код)</label>
                            <input type="number" class="form-control" name="person_dolj" min="0" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label-required">Отдел (код)</label>
                            <input type="number" class="form-control" name="person_department" min="0" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label-required">Город (код)</label>
                            <input type="number" class="form-control" name="city_id" min="0" max="255" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label-required">Кабинет</label>
                            <input type="text" class="form-control" name="cabinet" maxlength="6" placeholder="101">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label-required">SD</label>
                            <input type="number" class="form-control" name="sd" min="0" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label-required">Тип надзора</label>
                            <select class="form-control" name="n_type">
                                <option value="">—</option>
                                <option value="пром">пром</option>
                                <option value="энергонадзор">энергонадзор</option>
                                <option value="стройнадзор">стройнадзор</option>
                                <option value="ГТС">ГТС</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label-required">Номер удостоверения</label>
                            <input type="text" class="form-control" name="id_num" maxlength="6" placeholder="123456">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Дата печати уд-я</label>
                            <input type="datetime-local" class="form-control" name="id_printed">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-12">
                            <div class="toggle-row mb-2">
                                <div class="label">Согласовано руководителем</div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="emp-sogl_ruk" name="sogl_ruk" value="1">
                                    <label class="custom-control-label" for="emp-sogl_ruk"></label>
                                </div>
                            </div>
                            <div class="toggle-row mb-2">
                                <div class="label">Нужна крипто</div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="emp-needcrypto" name="needcrypto" value="1">
                                    <label class="custom-control-label" for="emp-needcrypto"></label>
                                </div>
                            </div>
                            <div class="toggle-row mb-2">
                                <div class="label">POS</div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="emp-pos" name="pos" value="1">
                                    <label class="custom-control-label" for="emp-pos"></label>
                                </div>
                            </div>
                            <div class="toggle-row mb-0">
                                <div class="label">Не печатать</div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="emp-not_print" name="not_print" value="1">
                                    <label class="custom-control-label" for="emp-not_print"></label>
                                </div>
                            </div>
                        </div>
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
