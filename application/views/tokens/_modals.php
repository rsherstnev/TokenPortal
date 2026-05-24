<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- ===================== Модалка модели токена ============================ -->
<div class="modal fade skzi-modal" id="tokenModelModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="tokenModelForm" novalidate autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title" id="tokenModelModalTitle">Добавление модели токена</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="form-group mb-0">
                        <label class="form-label-required">Модель</label>
                        <input type="text" class="form-control" name="name" placeholder="Рутокен ЭЦП 2.0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-skzi-primary">Создать</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== Модалка токена =================================== -->
<div class="modal fade skzi-modal" id="tokenModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="tokenForm" novalidate autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title" id="tokenModalTitle">Добавление токена</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                        <label class="form-label-required">Модель</label>
                        <select class="custom-select" name="token_model_id" required>
                            <option value="">— Не указана —</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label-required">Серийный номер</label>
                        <input type="text" class="form-control" name="serial_number" placeholder="0123456789" inputmode="numeric" required>
                    </div>
                    <div class="token-flags-section">
                        <div class="token-flags-title">Состояние</div>
                        <label class="token-flag-card token-flag-broken" for="token-is-broken">
                            <div class="token-flag-left">
                                <span class="token-flag-icon"><i class="bi bi-wrench-adjustable-circle-fill"></i></span>
                                <div class="token-flag-text">
                                    <span class="token-flag-title">Неисправен (сломан)</span>
                                    <span class="token-flag-hint">Помечает токен как неисправный</span>
                                </div>
                            </div>
                            <div class="custom-control custom-switch mb-0" onclick="event.stopPropagation()">
                                <input type="checkbox" class="custom-control-input" id="token-is-broken" name="is_broken" value="1">
                                <label class="custom-control-label" for="token-is-broken"></label>
                            </div>
                        </label>
                        <label class="token-flag-card token-flag-lost" for="token-is-lost">
                            <div class="token-flag-left">
                                <span class="token-flag-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                <div class="token-flag-text">
                                    <span class="token-flag-title">Утерян</span>
                                    <span class="token-flag-hint">Помечает токен как утерянный</span>
                                </div>
                            </div>
                            <div class="custom-control custom-switch mb-0" onclick="event.stopPropagation()">
                                <input type="checkbox" class="custom-control-input" id="token-is-lost" name="is_lost" value="1">
                                <label class="custom-control-label" for="token-is-lost"></label>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-skzi-primary">Создать</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== Модалка передачи ================================ -->
<div class="modal fade skzi-modal" id="transferModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="transferForm" novalidate autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title">Передача токена <span id="transfer-token-info" class="ml-2"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="token_id" value="">
                    <div class="form-group">
                        <label>Текущий владелец</label>
                        <div class="form-control bg-light" id="transfer-from" style="user-select: none;">—</div>
                    </div>
                    <div class="form-group">
                        <label>Передать кому</label>
                        <select class="custom-select" name="to_employee_id">
                            <option value="">— Возврат на склад —</option>
                        </select>
                        <small class="form-text text-muted">Пустое значение возвращает токен на склад.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label>Комментарий</label>
                        <textarea class="form-control" name="comment" rows="2" placeholder="Например: выдан по заявке №…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-skzi-primary">Передать</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== Модалка истории ================================== -->
<div class="modal fade skzi-modal" id="historyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">История передач <span id="history-token-info" class="ml-2"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="history-body">
                <div class="history-empty"><i class="bi bi-clock-history"></i>История передач пуста</div>
            </div>
        </div>
    </div>
</div>
