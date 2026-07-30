<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_transfers extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('token_m');
		$this->load->model('token_transfer_m');
		$this->load->model('employee_m');
	}

	public function index()
	{
		$data = array(
			'active_nav' => 'transfer_history',
		);
		$this->load->view('templates/header', $data);
		$this->load->view('transfer_history/index', $data);
		$this->load->view('templates/footer', $data);
	}

	public function list_json()
	{
		$search    = trim((string) $this->input->get('q'));
		$date_from = $this->parse_utc_datetime($this->input->get('date_from'));
		$date_to   = $this->parse_utc_datetime($this->input->get('date_to'));

		if ($date_from && $date_to && $date_from > $date_to)
		{
			$this->json_error('Дата начала не может быть позже даты окончания', 422);
			return;
		}

		$filters = array(
			'search'    => $search,
			'date_from' => $date_from,
			'date_to'   => $date_to,
		);
		$rows  = $this->token_transfer_m->list_filtered($filters);
		$total = $this->token_transfer_m->count_filtered($filters);
		$this->json_ok($rows, array('count' => count($rows), 'total' => $total));
	}

	private function parse_utc_datetime($value)
	{
		$value = is_string($value) ? trim($value) : '';
		if ($value === '')
		{
			return NULL;
		}

		$dt = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone('UTC'));
		if ($dt && $dt->format('Y-m-d H:i:s') === $value)
		{
			return $value;
		}

		$dt = DateTime::createFromFormat('Y-m-d', $value, new DateTimeZone('UTC'));
		if ($dt && $dt->format('Y-m-d') === $value)
		{
			return $dt->format('Y-m-d') . ' 00:00:00';
		}

		return NULL;
	}

	public function create($token_id)
	{
		$this->require_post();

		if ( ! is_positive_int_id($token_id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		$token = $this->token_m->get($token_id);
		if ( ! $token)
		{
			$this->json_error('Токен не найден', 404);
			return;
		}

		$raw_to = $this->input->post('to_employee_id');
		$to_employee_id = is_string($raw_to) ? trim($raw_to) : '';
		$to_employee_id = $to_employee_id === '' ? NULL : $to_employee_id;
		$comment = trim((string) $this->input->post('comment'));

		$raw_date = trim((string) $this->input->post('transferred_at'));
		$transferred_at = NULL;
		if ($raw_date !== '')
		{
			$d = DateTime::createFromFormat('Y-m-d', $raw_date);
			if ( ! $d || $d->format('Y-m-d') !== $raw_date)
			{
				$this->json_error('Некорректная дата передачи', 422, array('transferred_at' => 'Укажите корректную дату'));
				return;
			}
			$transferred_at = $d->format('Y-m-d') . ' 00:00:00';
		}

		$to_name = NULL;
		// Возврат на склад (to = NULL) или передача конкретному пользователю.
		if ($to_employee_id !== NULL)
		{
			if ( ! ctype_digit((string) $to_employee_id) || (int) $to_employee_id <= 0)
			{
				$this->json_error('Выберите пользователя', 422, array('to_employee_id' => 'Выберите пользователя'));
				return;
			}
			$emp = $this->employee_m->get($to_employee_id);
			if ( ! $emp)
			{
				$this->json_error('Пользователь не найден', 422, array('to_employee_id' => 'Пользователь не найден'));
				return;
			}
			if ( ! empty($emp['is_fired']))
			{
				$this->json_error('Нельзя передать токен уволенному пользователю', 422, array('to_employee_id' => 'Пользователь уволен'));
				return;
			}
			$to_name = $emp['person_name'];
		}

		if ($token['employee_id'] === $to_employee_id)
		{
			$this->json_error('Токен уже у выбранного владельца', 422, array('to_employee_id' => 'Текущий владелец совпадает с выбранным'));
			return;
		}

		$from_name = NULL;
		if ( ! empty($token['employee_id']))
		{
			$from_emp = $this->employee_m->get($token['employee_id']);
			$from_name = $from_emp ? $from_emp['person_name'] : NULL;
		}

		$transfer_id = $this->token_transfer_m->transfer($token_id, $to_employee_id, $comment, $transferred_at);
		if ( ! $transfer_id)
		{
			$this->json_error('Не удалось выполнить передачу', 500);
			return;
		}

		$this->audit_log(
			audit_log_transfer_create_message(
				$transfer_id,
				$token,
				$token['employee_id'],
				$from_name,
				$to_employee_id,
				$to_name,
				$transferred_at,
				$comment
			),
			array('action' => 'token_transfer.create', 'entity_id' => $transfer_id, 'token_id' => (int) $token_id)
		);

		$this->json_ok($this->token_m->get($token_id), array('message' => 'Передача выполнена'));
	}

	public function get($id)
	{
		if ( ! is_positive_int_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}

		$row = $this->token_transfer_m->get($id);
		if ( ! $row)
		{
			$this->json_error('Запись передачи не найдена', 404);
			return;
		}

		$this->json_ok($row);
	}

	public function update($id)
	{
		$this->require_post();

		if ( ! is_positive_int_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}

		$existing = $this->token_transfer_m->get($id);
		if ( ! $existing)
		{
			$this->json_error('Запись передачи не найдена', 404);
			return;
		}

		$scope = trim((string) $this->input->post('update_scope'));

		if ($scope === 'comment')
		{
			$comment = trim((string) $this->input->post('comment'));
			$old_comment = isset($existing['comment']) ? (string) $existing['comment'] : '';
			if ( ! $this->token_transfer_m->update_comment($id, $comment))
			{
				$this->json_error('Не удалось сохранить комментарий', 500);
				return;
			}

			$row = $this->token_transfer_m->get($id);
			$token = $this->token_m->get($existing['token_id']);
			$this->audit_log(
				audit_log_transfer_comment_message($id, $old_comment, $comment, $token ?: array()),
				array('action' => 'token_transfer.update_comment', 'entity_id' => (int) $id)
			);
			$this->json_ok($row, array('message' => 'Комментарий обновлён'));
			return;
		}

		if ($scope === 'transferred_at')
		{
			$raw_date = trim((string) $this->input->post('transferred_at'));
			if ($raw_date === '')
			{
				$this->json_error('Укажите дату передачи', 422, array('transferred_at' => 'Укажите дату'));
				return;
			}

			$d = DateTime::createFromFormat('Y-m-d', $raw_date);
			if ( ! $d || $d->format('Y-m-d') !== $raw_date)
			{
				$this->json_error('Некорректная дата передачи', 422, array('transferred_at' => 'Укажите корректную дату'));
				return;
			}

			$transferred_at = $d->format('Y-m-d') . ' 00:00:00';
			$old_date = isset($existing['transferred_at']) ? (string) $existing['transferred_at'] : '';
			if ( ! $this->token_transfer_m->update_transferred_at($id, $transferred_at))
			{
				$this->json_error('Не удалось сохранить дату', 500);
				return;
			}

			$row = $this->token_transfer_m->get($id);
			$token = $this->token_m->get($existing['token_id']);
			$this->audit_log(
				audit_log_transfer_date_message($id, $old_date, $transferred_at, $token ?: array()),
				array('action' => 'token_transfer.update_date', 'entity_id' => (int) $id)
			);
			$this->json_ok($row, array('message' => 'Дата передачи обновлена'));
			return;
		}

		if ($scope === 'transfer')
		{
			$comment = trim((string) $this->input->post('comment'));
			$raw_date = trim((string) $this->input->post('transferred_at'));
			if ($raw_date === '')
			{
				$this->json_error('Укажите дату передачи', 422, array('transferred_at' => 'Укажите дату'));
				return;
			}

			$d = DateTime::createFromFormat('Y-m-d', $raw_date);
			if ( ! $d || $d->format('Y-m-d') !== $raw_date)
			{
				$this->json_error('Некорректная дата передачи', 422, array('transferred_at' => 'Укажите корректную дату'));
				return;
			}

			$transferred_at = $d->format('Y-m-d') . ' 00:00:00';
			$old_comment = isset($existing['comment']) ? (string) $existing['comment'] : '';
			$old_date = isset($existing['transferred_at']) ? (string) $existing['transferred_at'] : '';
			if ( ! $this->token_transfer_m->update_edit_fields($id, $comment, $transferred_at))
			{
				$this->json_error('Не удалось сохранить передачу', 500);
				return;
			}

			$row = $this->token_transfer_m->get($id);
			$token = $this->token_m->get($existing['token_id']);
			if (trim($old_comment) !== $comment)
			{
				$this->audit_log(
					audit_log_transfer_comment_message($id, $old_comment, $comment, $token ?: array()),
					array('action' => 'token_transfer.update_comment', 'entity_id' => (int) $id)
				);
			}
			if ($old_date !== $transferred_at)
			{
				$this->audit_log(
					audit_log_transfer_date_message($id, $old_date, $transferred_at, $token ?: array()),
					array('action' => 'token_transfer.update_date', 'entity_id' => (int) $id)
				);
			}
			$this->json_ok($row, array('message' => 'Передача обновлена'));
			return;
		}

		$this->json_error('Нет данных для обновления', 422);
	}

	public function transfer_act($id)
	{
		if ( ! is_positive_int_id($id))
		{
			show_404();
			return;
		}

		$row = $this->token_transfer_m->get_act_data($id);
		if ( ! $row)
		{
			show_404();
			return;
		}

		$this->audit_log(
			audit_log_transfer_act_message($id, array(
				'model_name'    => $row['model_name'] ?? '',
				'serial_number' => $row['serial_number'] ?? '',
			)),
			array('action' => 'token_transfer.download_act', 'entity_id' => (int) $id)
		);

		$this->load->helper('transfer_act');
		$this->load->library('transfer_act_docx');

		$token_label = trim($row['model_name'] . ', ' . $row['serial_number'], ', ');

		try
		{
			$binary = $this->transfer_act_docx->render(array(
				'city'     => 'г. Красноярск',
				'date'     => transfer_act_format_date($row['transferred_at']),
				'from_who' => transfer_act_format_party(
					$row['from_dolj_name'],
					$row['from_department_name'],
					$row['from_fullname'],
					$row['from_employee_id']
				),
				'token'    => $token_label,
				'to_who'   => transfer_act_format_party(
					$row['to_dolj_name'],
					$row['to_department_name'],
					$row['to_fullname'],
					$row['to_employee_id']
				),
			));
		}
		catch (RuntimeException $e)
		{
			show_error($e->getMessage(), 500);
			return;
		}

		$filename = transfer_act_download_filename($row['to_fullname'], $row['to_employee_id']);
		$ascii_name = transfer_act_ascii_filename($filename);

		$this->output->set_status_header(200);
		header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
		header('Content-Disposition: attachment; filename="' . $ascii_name . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
		header('Content-Length: ' . strlen($binary));
		header('Cache-Control: private, no-store, no-cache, must-revalidate');
		header('Pragma: no-cache');
		echo $binary;
		exit;
	}

	public function history($token_id)
	{
		if ( ! is_positive_int_id($token_id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		$token = $this->token_m->get($token_id);
		if ( ! $token)
		{
			$this->json_error('Токен не найден', 404);
			return;
		}
		$rows = $this->token_transfer_m->history($token_id);
		$this->json_ok(array(
			'token'     => $token,
			'transfers' => $rows,
		), array('count' => count($rows)));
	}
}
