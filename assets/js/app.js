(function ($) {
    'use strict';

    const App = window.App = window.App || {};
    const BASE_URL = (window.SKZI_BASE_URL || '/').replace(/\/+$/, '') + '/';

    // ---- CSRF ----------------------------------------------------------------
    const Csrf = {
        name: $('meta[name="csrf-name"]').attr('content'),
        hash: $('meta[name="csrf-hash"]').attr('content'),
        update(payload) {
            if (!payload) return;
            if (payload.name) Csrf.name = payload.name;
            if (payload.hash) Csrf.hash = payload.hash;
            $('meta[name="csrf-hash"]').attr('content', Csrf.hash);
            $('input[name="' + Csrf.name + '"]').val(Csrf.hash);
        },
    };

    // ---- AJAX helpers --------------------------------------------------------
    App.url = function (path) {
        return BASE_URL + String(path).replace(/^\/+/, '');
    };

    const STATUS_ICONS = {
        not_issued: 'bi-dash-circle',
        issued:     'bi-check-circle-fill',
        broken:     'bi-wrench-adjustable-circle-fill',
        lost:       'bi-exclamation-triangle-fill',
    };
    App.statusIcon = function (code) {
        const cls = STATUS_ICONS[code];
        return cls ? '<i class="bi ' + cls + '"></i>' : '';
    };

    App.getJSON = function (path, params) {
        return $.ajax({
            url: App.url(path),
            method: 'GET',
            data: params || {},
            dataType: 'json',
        }).then((res) => {
            Csrf.update(res && res.csrf);
            return res;
        });
    };

    App.postJSON = function (path, data) {
        const payload = $.extend({}, data || {});
        payload[Csrf.name] = Csrf.hash;
        return $.ajax({
            url: App.url(path),
            method: 'POST',
            data: payload,
            dataType: 'json',
        }).then((res) => {
            Csrf.update(res && res.csrf);
            return res;
        }, (xhr) => {
            const res = xhr.responseJSON || { ok: false, message: 'Ошибка запроса' };
            Csrf.update(res.csrf);
            return $.Deferred().reject(res).promise();
        });
    };

    // ---- Toast notifications -------------------------------------------------
    App.toast = function (message, type) {
        let $c = $('.toast-container');
        if (!$c.length) {
            $c = $('<div class="toast-container"></div>').appendTo('body');
        }
        const $t = $('<div class="toast-msg"></div>').addClass(type || '').text(message);
        $c.append($t);
        setTimeout(() => $t.fadeOut(200, () => $t.remove()), 3500);
    };

    // ---- Form error helpers --------------------------------------------------
    App.clearErrors = function ($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.ts-wrapper.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
    };

    // ---- ESC key handling in modal text inputs -------------------------------
    // Disable Bootstrap's default ESC handling and implement custom logic
    $(document).on('shown.bs.modal', '.skzi-modal', function () {
        $(this).data('bs.modal')._config.keyboard = false;
    });

    // Custom ESC handler: clear non-empty inputs first, close modal only when all inputs are empty
    $(document).on('keydown', function (e) {
        if (e.key !== 'Escape') return;

        const $modal = $('.skzi-modal.show');
        if (!$modal.length) return;

        const $focused = $(':focus');
        const $textInput = $focused.is('input[type="text"], input[type="email"], input[type="search"], textarea') ? $focused : null;

        // If a non-empty text input is focused, just clear it
        if ($textInput && $textInput.val().trim() !== '') {
            e.preventDefault();
            e.stopPropagation();
            $textInput.val('');
            return;
        }

        // Check if any text input in the modal has content
        const $filledInput = $modal.find('input[type="text"], input[type="email"], input[type="search"], textarea')
            .filter(function () { return $(this).val().trim() !== ''; })
            .first();

        if ($filledInput.length) {
            // Clear the first non-empty input and focus it
            e.preventDefault();
            e.stopPropagation();
            $filledInput.val('');
            $filledInput.focus();
            return;
        }

        // All inputs are empty - close the modal
        $modal.modal('hide');
    });

    // ---- Clear modal form on close (backdrop click or empty ESC) -------------
    $(document).on('hidden.bs.modal', '.skzi-modal', function () {
        const $form = $(this).find('form');
        if (!$form.length) return;

        // Reset form fields
        $form[0].reset();

        // Clear hidden fields (like id)
        $form.find('input[type="hidden"]').val('');

        // Clear Tom Select dropdowns
        $form.find('select').each(function () {
            if (this.tomselect) {
                this.tomselect.clear(true);
            }
        });

        // Remove validation errors
        App.clearErrors($form);
    });

    App.applyErrors = function ($form, errors) {
        App.clearErrors($form);
        $.each(errors || {}, function (field, message) {
            const $input = $form.find('[name="' + field + '"]');
            $input.addClass('is-invalid');
            const el = $input[0];
            const $after = el && el.tomselect ? $(el.tomselect.wrapper) : $input;
            $after.addClass('is-invalid').after('<div class="invalid-feedback">' + escapeHtml(message) + '</div>');
        });
    };

    App.initSelect = function (el, opts) {
        if (!el) return null;
        if (el.tomselect) { el.tomselect.destroy(); }
        const inModal = !!(el.closest && el.closest('.modal'));
        return new TomSelect(el, Object.assign({
            allowEmptyOption: true,
            create: false,
            plugins: [],
            dropdownParent: inModal ? 'body' : null,
            onFocus: function () {
                this._prevValue = this.getValue();
                this.clear(true);
            },
            onBlur: function () {
                if (this.getValue() === '' && this._prevValue !== undefined) {
                    this.setValue(this._prevValue, true);
                }
                this._prevValue = undefined;
            },
            onItemAdd: function () {
                this._prevValue = undefined;
            },
        }, opts || {}));
    };

    // ---- Misc helpers --------------------------------------------------------
    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    App.escape = escapeHtml;

    App.formatDate = function (str) {
        if (!str) return '';
        // DATETIME-поля БД хранятся в UTC (см. db/schema.sql), поэтому строку
        // без таймзоны явно помечаем как UTC, а на экран выводим getHours/etc.,
        // которые сами переведут в локальное время браузера.
        const d = new Date(str.replace(' ', 'T') + 'Z');
        if (isNaN(d.getTime())) return str;
        const p = (n) => String(n).padStart(2, '0');
        return p(d.getDate()) + '.' + p(d.getMonth() + 1) + '.' + d.getFullYear() + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
    };

    App.debounce = function (fn, wait) {
        let timer;
        return function () {
            const ctx = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(ctx, args), wait || 300);
        };
    };

    // ---- Text highlight helper ------------------------------------------------
    App.highlightMatch = function (text, query) {
        if (!query || !text) return App.escape(text);
        const escapedText = App.escape(text);
        const escapedQuery = App.escape(query).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        if (!escapedQuery) return escapedText;
        const regex = new RegExp('(' + escapedQuery + ')', 'gi');
        return escapedText.replace(regex, '<mark class="search-highlight">$1</mark>');
    };

    // ---- Confirm dialog (uses Bootstrap modal) -------------------------------
    App.confirm = function (text, opts) {
        opts = opts || {};
        return new Promise((resolve) => {
            const $m = $('#confirmModal');
            $m.find('.confirm-text').text(text);
            $m.find('.confirm-ok').text(opts.okText || 'Удалить').removeClass('btn-danger btn-primary').addClass(opts.okClass || 'btn-danger');
            $m.find('.confirm-cancel').text(opts.cancelText || 'Отмена');

            const onOk = () => { cleanup(); resolve(true); };
            const onCancel = () => { cleanup(); resolve(false); };
            function cleanup() {
                $m.off('hidden.bs.modal', onCancel);
                $m.find('.confirm-ok').off('click', onOk);
                $m.modal('hide');
            }
            $m.find('.confirm-ok').one('click', onOk);
            $m.one('hidden.bs.modal', onCancel);
            $m.modal('show');
        });
    };

})(jQuery);

// =============================================================================
// Tokens page logic
// =============================================================================
(function ($) {
    'use strict';
    if (!$('#tokens-page').length) return;
    const App = window.App;

    const Tokens = {
        $list: $('#tokens-list'),
        $search: $('#tokens-search'),
        $status: $('#tokens-status'),
        $count: $('#tokens-count'),

        init() {
            this.bindEvents();
            App.initSelect(this.$status[0], { controlInput: false });
            this.refresh();
            TokenModels.refresh();
        },

        bindEvents() {
            const debouncedRefresh = App.debounce(() => this.refresh(), 250);
            this.$search.on('input', debouncedRefresh);
            this.$search.on('keydown', (e) => { if (e.key === 'Escape') { this.$search.val(''); this.refresh(); } });
            this.$status.on('change', () => this.refresh());

            $('#btn-add-token').on('click', () => this.openCreate());

            $('#tokenForm').find('[name="serial_number"]').on('input', function () {
                const pos = this.selectionStart;
                const cleaned = this.value.replace(/\D/g, '');
                if (this.value !== cleaned) {
                    this.value = cleaned;
                    this.setSelectionRange(pos - 1, pos - 1);
                }
            });

            this.$list.on('click', '.action-edit',     (e) => this.openEdit($(e.currentTarget).data('id')));
            this.$list.on('click', '.action-delete',   (e) => this.delete($(e.currentTarget).data('id')));
            this.$list.on('click', '.action-transfer', (e) => Transfers.open($(e.currentTarget).data('id')));
            this.$list.on('click', '.action-history',  (e) => History.open($(e.currentTarget).data('id')));

            $('#tokenForm').on('submit', (e) => { e.preventDefault(); this.save(); });
        },

        refresh() {
            const query = this.$search.val();
            App.getJSON('tokens/list', { q: query, status: this.$status.val() })
                .then((res) => this.render(res.data || [], res.total, query))
                .catch(() => App.toast('Не удалось загрузить токены', 'error'));
        },

        render(rows, total, query) {
            const n = rows.length;
            this.$count.text((total != null && n !== total) ? n + ' из ' + total : n);
            if (!rows.length) {
                this.$list.html('<tr><td colspan="5" class="empty-cell">Нет токенов</td></tr>');
                return;
            }
            const html = rows.map((r) => {
                const status = r.status || {};
                const employee = r.employee_fullname && r.employee_fullname.trim().length
                    ? App.highlightMatch(r.employee_fullname, query)
                    : '<span class="text-muted">—</span>';
                return ''
                    + '<tr data-id="' + App.escape(r.id) + '">'
                    +   '<td>' + employee + '</td>'
                    +   '<td>' + App.highlightMatch(r.model_name || '', query) + '</td>'
                    +   '<td>' + App.highlightMatch(r.serial_number || '', query) + '</td>'
                    +   '<td><span class="status-badge ' + App.escape(status.code) + '">' + App.statusIcon(status.code) + App.escape(status.label || '') + '</span></td>'
                    +   '<td class="actions-cell">'
                    +     '<button type="button" class="btn-icon history action-history"  data-id="' + App.escape(r.id) + '" title="История передач"><i class="bi bi-clock-history"></i></button>'
                    +     '<button type="button" class="btn-icon transfer action-transfer" data-id="' + App.escape(r.id) + '" title="Передать"><i class="bi bi-arrow-left-right"></i></button>'
                    +     '<button type="button" class="btn-icon edit action-edit"        data-id="' + App.escape(r.id) + '" title="Редактировать"><i class="bi bi-pencil"></i></button>'
                    +     '<button type="button" class="btn-icon delete action-delete"    data-id="' + App.escape(r.id) + '" title="Удалить"><i class="bi bi-trash"></i></button>'
                    +   '</td>'
                    + '</tr>';
            }).join('');
            this.$list.html(html);
        },

        openCreate() {
            const $form = $('#tokenForm');
            $form[0].reset();
            $form.find('[name="id"]').val('');
            $('#tokenModalTitle').text('Добавление токена');
            App.clearErrors($form);
            TokenModels.fillSelect($form.find('select[name="token_model_id"]'));
            $('#tokenModal').modal('show');
        },

        openEdit(id) {
            App.getJSON('tokens/get/' + id).then((res) => {
                const t = res.data;
                const $form = $('#tokenForm');
                $form[0].reset();
                $('#tokenModalTitle').text('Редактирование токена');
                App.clearErrors($form);
                TokenModels.fillSelect($form.find('select[name="token_model_id"]'), t.token_model_id);
                $form.find('[name="id"]').val(t.id);
                $form.find('[name="serial_number"]').val(t.serial_number);
                $form.find('[name="is_broken"]').prop('checked', !!Number(t.is_broken));
                $form.find('[name="is_lost"]').prop('checked', !!Number(t.is_lost));
                $('#tokenModal').modal('show');
            });
        },

        save() {
            const $form = $('#tokenForm');
            const id = $form.find('[name="id"]').val();
            const payload = {
                token_model_id: $form.find('select[name="token_model_id"]').val(),
                serial_number:  $form.find('[name="serial_number"]').val(),
                is_broken: $form.find('[name="is_broken"]').is(':checked') ? 1 : 0,
                is_lost:   $form.find('[name="is_lost"]').is(':checked')   ? 1 : 0,
            };
            const path = id ? ('tokens/update/' + id) : 'tokens/create';
            App.postJSON(path, payload)
                .then((res) => {
                    $('#tokenModal').modal('hide');
                    App.toast(res.message || 'Сохранено', 'success');
                    this.refresh();
                })
                .catch((res) => {
                    if (res.errors) App.applyErrors($form, res.errors);
                    App.toast(res.message || 'Ошибка', 'error');
                });
        },

        delete(id) {
            App.confirm('Удалить токен? Действие нельзя отменить.').then((ok) => {
                if (!ok) return;
                App.postJSON('tokens/delete/' + id)
                    .then((res) => { App.toast(res.message || 'Удалено', 'success'); this.refresh(); })
                    .catch((res) => App.toast(res.message || 'Не удалось удалить', 'error'));
            });
        },
    };

    // -------------------------------------------------------------------------
    const TokenModels = {
        cache: null,
        $list: $('#token-models-list'),
        $search: $('#models-search'),
        $count: $('#models-count'),

        init() {
            this.$search.on('input', App.debounce(() => this.refresh(), 250));
            this.$search.on('keydown', (e) => { if (e.key === 'Escape') { this.$search.val(''); this.refresh(); } });
            $('#btn-add-model').on('click', () => this.openCreate());

            this.$list.on('click', '.action-edit',   (e) => this.openEdit($(e.currentTarget).data('id')));
            this.$list.on('click', '.action-delete', (e) => this.delete($(e.currentTarget).data('id')));

            $('#tokenModelForm').on('submit', (e) => { e.preventDefault(); this.save(); });
        },

        refresh() {
            const query = this.$search.val();
            App.getJSON('token_models/list', { q: query })
                .then((res) => { this.render(res.data || [], res.total, query); this.cache = res.data || []; })
                .catch(() => App.toast('Не удалось загрузить модели', 'error'));
        },

        render(rows, total, query) {
            const n = rows.length;
            this.$count.text((total != null && n !== total) ? n + ' из ' + total : n);
            if (!rows.length) {
                this.$list.html('<tr><td colspan="2" class="empty-cell">Нет моделей</td></tr>');
                return;
            }
            const html = rows.map((r) => ''
                + '<tr data-id="' + App.escape(r.id) + '">'
                +   '<td>' + App.highlightMatch(r.name, query) + '</td>'
                +   '<td class="actions-cell">'
                +     '<button type="button" class="btn-icon edit action-edit"   data-id="' + App.escape(r.id) + '" title="Редактировать"><i class="bi bi-pencil"></i></button>'
                +     '<button type="button" class="btn-icon delete action-delete" data-id="' + App.escape(r.id) + '" title="Удалить"><i class="bi bi-trash"></i></button>'
                +   '</td>'
                + '</tr>'
            ).join('');
            this.$list.html(html);
        },

        fillSelect($select, selectedId) {
            App.getJSON('token_models/options').then((res) => {
                const options = (res.data || []).map((m) =>
                    '<option value="' + App.escape(m.id) + '"' + (m.id === selectedId ? ' selected' : '') + '>' + App.escape(m.name) + '</option>'
                ).join('');
                $select.html('<option value="">— Не указана —</option>' + options);
                App.initSelect($select[0]);
            });
        },

        openCreate() {
            const $form = $('#tokenModelForm');
            $form[0].reset();
            $form.find('[name="id"]').val('');
            $('#tokenModelModalTitle').text('Добавление модели токена');
            App.clearErrors($form);
            $('#tokenModelModal').modal('show');
        },

        openEdit(id) {
            App.getJSON('token_models/get/' + id).then((res) => {
                const $form = $('#tokenModelForm');
                $form[0].reset();
                App.clearErrors($form);
                $form.find('[name="id"]').val(res.data.id);
                $form.find('[name="name"]').val(res.data.name);
                $('#tokenModelModalTitle').text('Редактирование модели токена');
                $('#tokenModelModal').modal('show');
            });
        },

        save() {
            const $form = $('#tokenModelForm');
            const id = $form.find('[name="id"]').val();
            const payload = { name: $form.find('[name="name"]').val() };
            const path = id ? ('token_models/update/' + id) : 'token_models/create';
            App.postJSON(path, payload)
                .then((res) => {
                    $('#tokenModelModal').modal('hide');
                    App.toast(res.message || 'Сохранено', 'success');
                    this.refresh();
                    Tokens.refresh();
                })
                .catch((res) => {
                    if (res.errors) App.applyErrors($form, res.errors);
                    App.toast(res.message || 'Ошибка', 'error');
                });
        },

        delete(id) {
            App.confirm('Удалить модель токена?').then((ok) => {
                if (!ok) return;
                App.postJSON('token_models/delete/' + id)
                    .then((res) => { App.toast(res.message || 'Удалено', 'success'); this.refresh(); })
                    .catch((res) => App.toast(res.message || 'Не удалось удалить', 'error'));
            });
        },
    };

    // -------------------------------------------------------------------------
    const Transfers = {
        init() {
            $('#transferForm').on('submit', (e) => { e.preventDefault(); this.save(); });
        },

        open(tokenId) {
            App.getJSON('tokens/get/' + tokenId).then((res) => {
                const t = res.data;
                const $form = $('#transferForm');
                $form[0].reset();
                App.clearErrors($form);
                $form.find('[name="token_id"]').val(t.id);
                $('#transfer-token-info').html(
                    '<span class="token-pill"><i class="bi bi-key"></i>' + App.escape(t.model_name || '') + ', ' + App.escape(t.serial_number || '') + '</span>'
                );
                $('#transfer-from').text(t.employee_fullname && t.employee_fullname.trim() ? t.employee_fullname : 'Не выдан');
                this.fillEmployees($form.find('select[name="to_employee_id"]'), t.employee_id);
                $('#transferModal').modal('show');
            });
        },

        fillEmployees($select, excludeId) {
            App.getJSON('employees/options').then((res) => {
                const options = (res.data || [])
                    .filter((e) => e.id !== excludeId)
                    .map((e) => '<option value="' + App.escape(e.id) + '">' + App.escape(e.fullname || (e.lastname + ' ' + e.firstname)) + '</option>')
                    .join('');
                $select.html('<option value="">— Возврат на склад —</option>' + options);
                App.initSelect($select[0]);
            });
        },

        save() {
            const $form = $('#transferForm');
            const tokenId = $form.find('[name="token_id"]').val();
            const payload = {
                to_employee_id: $form.find('select[name="to_employee_id"]').val(),
                comment:        $form.find('[name="comment"]').val(),
            };
            App.postJSON('token_transfers/create/' + tokenId, payload)
                .then((res) => {
                    $('#transferModal').modal('hide');
                    App.toast(res.message || 'Передача выполнена', 'success');
                    Tokens.refresh();
                })
                .catch((res) => {
                    if (res.errors) App.applyErrors($form, res.errors);
                    App.toast(res.message || 'Не удалось передать', 'error');
                });
        },
    };

    // -------------------------------------------------------------------------
    const History = {
        open(tokenId) {
            App.getJSON('token_transfers/history/' + tokenId).then((res) => {
                const data = res.data || {};
                const t = data.token || {};
                $('#history-token-info').html(
                    '<span class="token-pill"><i class="bi bi-key"></i>' + App.escape(t.model_name || '') + ' ' + App.escape(t.serial_number || '') + '</span>'
                );
                const transfers = data.transfers || [];
                if (!transfers.length) {
                    $('#history-body').html(
                        '<div class="history-empty"><i class="bi bi-clock-history"></i>История передач пуста</div>'
                    );
                } else {
                    const html = '<ul class="history-list">' + transfers.map((tr) => {
                        const from = tr.from_fullname && tr.from_fullname.trim() ? App.escape(tr.from_fullname) : '<span class="text-muted">Склад</span>';
                        const to   = tr.to_fullname && tr.to_fullname.trim()   ? App.escape(tr.to_fullname)   : '<span class="text-muted">Склад</span>';
                        const comment = tr.comment ? '<div>' + App.escape(tr.comment) + '</div>' : '';
                        return ''
                            + '<li>'
                            +   '<div><strong>' + from + '</strong><span class="arrow"><i class="bi bi-arrow-right"></i></span><strong>' + to + '</strong></div>'
                            +   comment
                            +   '<div class="meta">' + App.escape(App.formatDate(tr.transferred_at)) + '</div>'
                            + '</li>';
                    }).join('') + '</ul>';
                    $('#history-body').html(html);
                }
                $('#historyModal').modal('show');
            });
        },
    };

    $(function () {
        Tokens.init();
        TokenModels.init();
        Transfers.init();
        History.open && (window.History = History);
    });
})(jQuery);

// =============================================================================
// Employees page logic
// =============================================================================
(function ($) {
    'use strict';
    if (!$('#employees-page').length) return;
    const App = window.App;

    const Employees = {
        $list: $('#employees-list'),
        $search: $('#employees-search'),
        $count: $('#employees-count'),

        init() {
            this.$search.on('input', App.debounce(() => this.refresh(), 250));
            this.$search.on('keydown', (e) => { if (e.key === 'Escape') { this.$search.val(''); this.refresh(); } });
            $('#btn-add-employee').on('click', () => this.openCreate());

            this.$list.on('click', '.action-edit',   (e) => this.openEdit($(e.currentTarget).data('id')));
            this.$list.on('click', '.action-delete', (e) => this.delete($(e.currentTarget).data('id')));

            $('#employeeForm').on('submit', (e) => { e.preventDefault(); this.save(); });

            this.refresh();
        },

        refresh() {
            const query = this.$search.val();
            App.getJSON('employees/list', { q: query })
                .then((res) => this.render(res.data || [], res.total, query))
                .catch(() => App.toast('Не удалось загрузить сотрудников', 'error'));
        },

        render(rows, total, query) {
            const n = rows.length;
            this.$count.text((total != null && n !== total) ? n + ' из ' + total : n);
            if (!rows.length) {
                this.$list.html('<tr><td colspan="5" class="empty-cell">Нет сотрудников</td></tr>');
                return;
            }
            const html = rows.map((r) => {
                const active = Number(r.is_active) === 1
                    ? '<span class="status-badge issued">Активен</span>'
                    : '<span class="status-badge lost">Неактивен</span>';
                return ''
                    + '<tr data-id="' + App.escape(r.id) + '">'
                    +   '<td>' + App.highlightMatch(r.fullname, query) + '</td>'
                    +   '<td>' + App.highlightMatch(r.email || '', query) + '</td>'
                    +   '<td>' + App.highlightMatch(r.cabinet || '', query) + '</td>'
                    +   '<td>' + active + '</td>'
                    +   '<td class="actions-cell">'
                    +     '<button type="button" class="btn-icon edit action-edit"     data-id="' + App.escape(r.id) + '" title="Редактировать"><i class="bi bi-pencil"></i></button>'
                    +     '<button type="button" class="btn-icon delete action-delete" data-id="' + App.escape(r.id) + '" title="Удалить"><i class="bi bi-trash"></i></button>'
                    +   '</td>'
                    + '</tr>';
            }).join('');
            this.$list.html(html);
        },

        openCreate() {
            const $form = $('#employeeForm');
            $form[0].reset();
            $form.find('[name="id"]').val('');
            $form.find('[name="is_active"]').prop('checked', true);
            $('#employeeModalTitle').text('Добавление сотрудника');
            App.clearErrors($form);
            $('#employeeModal').modal('show');
        },

        openEdit(id) {
            App.getJSON('employees/get/' + id).then((res) => {
                const e = res.data;
                const $form = $('#employeeForm');
                $form[0].reset();
                App.clearErrors($form);
                $form.find('[name="id"]').val(e.id);
                $form.find('[name="firstname"]').val(e.firstname);
                $form.find('[name="lastname"]').val(e.lastname);
                $form.find('[name="patronymic"]').val(e.patronymic);
                $form.find('[name="email"]').val(e.email);
                $form.find('[name="cabinet"]').val(e.cabinet);
                $form.find('[name="is_active"]').prop('checked', Number(e.is_active) === 1);
                $('#employeeModalTitle').text('Редактирование сотрудника');
                $('#employeeModal').modal('show');
            });
        },

        save() {
            const $form = $('#employeeForm');
            const id = $form.find('[name="id"]').val();
            const payload = {
                firstname:  $form.find('[name="firstname"]').val(),
                lastname:   $form.find('[name="lastname"]').val(),
                patronymic: $form.find('[name="patronymic"]').val(),
                email:      $form.find('[name="email"]').val(),
                cabinet:    $form.find('[name="cabinet"]').val(),
                is_active:  $form.find('[name="is_active"]').is(':checked') ? 1 : 0,
            };
            const path = id ? ('employees/update/' + id) : 'employees/create';
            App.postJSON(path, payload)
                .then((res) => {
                    $('#employeeModal').modal('hide');
                    App.toast(res.message || 'Сохранено', 'success');
                    this.refresh();
                })
                .catch((res) => {
                    if (res.errors) App.applyErrors($form, res.errors);
                    App.toast(res.message || 'Ошибка', 'error');
                });
        },

        delete(id) {
            App.confirm('Удалить сотрудника? Действие нельзя отменить.').then((ok) => {
                if (!ok) return;
                App.postJSON('employees/delete/' + id)
                    .then((res) => { App.toast(res.message || 'Удалено', 'success'); this.refresh(); })
                    .catch((res) => App.toast(res.message || 'Не удалось удалить', 'error'));
            });
        },
    };

    $(function () { Employees.init(); });
})(jQuery);
