<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
</main>

<div class="modal fade skzi-modal" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="confirm-text mb-0">Вы уверены?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger confirm-ok">Удалить</button>
                <button type="button" class="btn btn-light confirm-cancel" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/tom-select/tom-select.complete.min.js') ?>"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
