<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('audit_log_yes_no'))
{
	function audit_log_yes_no($value)
	{
		return (int) (bool) $value === 1 ? 'да' : 'нет';
	}
}

if ( ! function_exists('audit_log_employee_party'))
{
	/**
	 * Подпись сотрудника или «склад» для журнала передач.
	 *
	 * @param int|null    $employee_id
	 * @param string|null $person_name
	 */
	function audit_log_employee_party($employee_id, $person_name = NULL)
	{
		if ($employee_id === NULL || $employee_id === '' || (int) $employee_id <= 0)
		{
			return 'склад';
		}

		$name = trim((string) $person_name);
		if ($name !== '')
		{
			return $name . ' (ID ' . (int) $employee_id . ')';
		}

		return 'сотрудник с ID ' . (int) $employee_id;
	}
}

if ( ! function_exists('audit_log_token_label'))
{
	/**
	 * Краткое описание токена для сообщений журнала.
	 */
	function audit_log_token_label(array $token)
	{
		$id = isset($token['id']) ? (int) $token['id'] : 0;
		$model = isset($token['model_name']) ? trim((string) $token['model_name']) : '';
		$serial = isset($token['serial_number']) ? trim((string) $token['serial_number']) : '';

		$parts = array();
		if ($id > 0)
		{
			$parts[] = 'ID ' . $id;
		}
		if ($model !== '')
		{
			$parts[] = 'модель «' . $model . '»';
		}
		if ($serial !== '')
		{
			$parts[] = 'серийный номер «' . $serial . '»';
		}

		return $parts ? implode(', ', $parts) : 'токен';
	}
}

if ( ! function_exists('audit_log_token_create_message'))
{
	function audit_log_token_create_message($id, array $token, $model_name = NULL)
	{
		$label = audit_log_token_label(array_merge($token, array('id' => $id)));
		if ($model_name !== NULL && trim((string) $model_name) !== '' && empty($token['model_name']))
		{
			$label = 'ID ' . (int) $id . ', модель «' . trim((string) $model_name) . '», серийный номер «'
				. trim((string) ($token['serial_number'] ?? '')) . '»';
		}

		return 'Токен (' . $label . ') был создан';
	}
}

if ( ! function_exists('audit_log_token_delete_message'))
{
	function audit_log_token_delete_message(array $token)
	{
		return 'Токен (' . audit_log_token_label($token) . ') был удалён';
	}
}

if ( ! function_exists('audit_log_token_update_messages'))
{
	/**
	 * Сообщения об изменении полей токена (по одному на каждое изменённое поле).
	 *
	 * @param array       $before       Запись до обновления (get())
	 * @param array       $after_input  Новые значения из POST
	 * @param string|null $new_model_name Название новой модели (если сменилась)
	 */
	function audit_log_token_update_messages(array $before, array $after_input, $new_model_name = NULL)
	{
		$id = (int) $before['id'];
		$messages = array();

		$old_model_id = (int) $before['token_model_id'];
		$new_model_id = (int) $after_input['token_model_id'];
		if ($old_model_id !== $new_model_id)
		{
			$old_name = trim((string) ($before['model_name'] ?? ''));
			if ($old_name === '')
			{
				$old_name = 'ID ' . $old_model_id;
			}
			$new_name = trim((string) ($new_model_name ?? ''));
			if ($new_name === '')
			{
				$new_name = 'ID ' . $new_model_id;
			}
			$messages[] = 'У токена с ID ' . $id . ' идентификатор модели был изменён с «' . $old_name
				. '» (ID ' . $old_model_id . ') на «' . $new_name . '» (ID ' . $new_model_id . ')';
		}

		$old_serial = trim((string) ($before['serial_number'] ?? ''));
		$new_serial = trim((string) ($after_input['serial_number'] ?? ''));
		if ($old_serial !== $new_serial)
		{
			$messages[] = 'У токена с ID ' . $id . ' серийный номер был изменён с «' . $old_serial
				. '» на «' . $new_serial . '»';
		}

		$old_broken = (int) ($before['is_broken'] ?? 0);
		$new_broken = (int) (bool) ($after_input['is_broken'] ?? 0);
		if ($old_broken !== $new_broken)
		{
			$messages[] = 'У токена с ID ' . $id . ' признак «неисправен» был изменён с «'
				. audit_log_yes_no($old_broken) . '» на «' . audit_log_yes_no($new_broken) . '»';
		}

		$old_lost = (int) ($before['is_lost'] ?? 0);
		$new_lost = (int) (bool) ($after_input['is_lost'] ?? 0);
		if ($old_lost !== $new_lost)
		{
			$messages[] = 'У токена с ID ' . $id . ' признак «утерян» был изменён с «'
				. audit_log_yes_no($old_lost) . '» на «' . audit_log_yes_no($new_lost) . '»';
		}

		if (empty($messages))
		{
			$messages[] = 'Токен (' . audit_log_token_label($before) . ') был обновлён без изменения отслеживаемых полей';
		}

		return $messages;
	}
}

if ( ! function_exists('audit_log_model_create_message'))
{
	function audit_log_model_create_message($id, $name)
	{
		return 'Модель токена с ID ' . (int) $id . ' («' . trim((string) $name) . '») была создана';
	}
}

if ( ! function_exists('audit_log_model_update_message'))
{
	function audit_log_model_update_message($id, $old_name, $new_name)
	{
		$old_name = trim((string) $old_name);
		$new_name = trim((string) $new_name);

		if ($old_name === $new_name)
		{
			return 'Модель токена с ID ' . (int) $id . ' («' . $new_name . '») была обновлена без изменения названия';
		}

		return 'Модель токена с ID ' . (int) $id . ' была переименована с «' . $old_name . '» на «' . $new_name . '»';
	}
}

if ( ! function_exists('audit_log_model_delete_message'))
{
	function audit_log_model_delete_message($id, $name)
	{
		return 'Модель токена с ID ' . (int) $id . ' («' . trim((string) $name) . '») была удалена';
	}
}

if ( ! function_exists('audit_log_transfer_create_message'))
{
	function audit_log_transfer_create_message(
		$transfer_id,
		array $token,
		$from_employee_id,
		$from_name,
		$to_employee_id,
		$to_name,
		$transferred_at = NULL,
		$comment = ''
	) {
		$from = audit_log_employee_party($from_employee_id, $from_name);
		$to = audit_log_employee_party($to_employee_id, $to_name);
		$date_part = '';
		if ($transferred_at !== NULL && trim((string) $transferred_at) !== '')
		{
			$date_part = ', дата передачи ' . trim((string) $transferred_at);
		}
		$comment_part = '';
		$comment = trim((string) $comment);
		if ($comment !== '')
		{
			$comment_part = ', комментарий: «' . $comment . '»';
		}

		return 'Выполнена передача токена (' . audit_log_token_label($token) . '): с «' . $from
			. '» на «' . $to . '» (запись передачи ID ' . (int) $transfer_id . $date_part . $comment_part . ')';
	}
}

if ( ! function_exists('audit_log_transfer_comment_message'))
{
	function audit_log_transfer_comment_message($transfer_id, $old_comment, $new_comment, array $token = array())
	{
		$old = trim((string) $old_comment);
		$new = trim((string) $new_comment);
		$token_part = empty($token) ? '' : ', токен ' . audit_log_token_label($token);

		if ($old === '' && $new !== '')
		{
			return 'У записи передачи с ID ' . (int) $transfer_id . $token_part
				. ' комментарий был установлен: «' . $new . '»';
		}
		if ($old !== '' && $new === '')
		{
			return 'У записи передачи с ID ' . (int) $transfer_id . $token_part
				. ' комментарий «' . $old . '» был удалён';
		}

		return 'У записи передачи с ID ' . (int) $transfer_id . $token_part
			. ' комментарий был изменён с «' . $old . '» на «' . $new . '»';
	}
}

if ( ! function_exists('audit_log_transfer_date_message'))
{
	function audit_log_transfer_date_message($transfer_id, $old_date, $new_date, array $token = array())
	{
		$token_part = empty($token) ? '' : ', токен ' . audit_log_token_label($token);

		return 'У записи передачи с ID ' . (int) $transfer_id . $token_part
			. ' дата передачи была изменена с «' . trim((string) $old_date)
			. '» на «' . trim((string) $new_date) . '»';
	}
}

if ( ! function_exists('audit_log_transfer_act_message'))
{
	function audit_log_transfer_act_message($transfer_id, array $token)
	{
		return 'Сформирован и выдан акт приёма-передачи по записи передачи ID ' . (int) $transfer_id
			. ' для токена (' . audit_log_token_label($token) . ')';
	}
}
