<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal fade skzi-modal" id="transferDateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="transferDateForm" novalidate autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование даты передачи</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                        <label>Токен</label>
                        <div class="form-control bg-light" id="transfer-date-token" style="user-select: none;">—</div>
                    </div>
                    <div class="form-group">
                        <label>Передача</label>
                        <div class="form-control bg-light" id="transfer-date-route" style="user-select: none;">—</div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Дата передачи</label>
                        <input type="date" class="form-control" id="transfer-date-at" name="transferred_at" required>
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
