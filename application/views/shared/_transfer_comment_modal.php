<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal fade skzi-modal" id="transferCommentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="transferCommentForm" novalidate autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование комментария</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                        <label>Токен</label>
                        <div class="form-control bg-light" id="transfer-comment-token" style="user-select: none;">—</div>
                    </div>
                    <div class="form-group">
                        <label>Передача</label>
                        <div class="form-control bg-light" id="transfer-comment-route" style="user-select: none;">—</div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Комментарий</label>
                        <textarea class="form-control" name="comment" rows="3" placeholder="Например: выдан по заявке №…"></textarea>
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
