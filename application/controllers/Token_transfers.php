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
			'page_title' => 'История передач',
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

		if ( ! is_uuid($token_id))
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
		}

		if ($token['employee_id'] === $to_employee_id)
		{
			$this->json_error('Токен уже у выбранного владельца', 422, array('to_employee_id' => 'Текущий владелец совпадает с выбранным'));
			return;
		}

		$transfer_id = $this->token_transfer_m->transfer($token_id, $to_employee_id, $comment);
		if ( ! $transfer_id)
		{
			$this->json_error('Не удалось выполнить передачу', 500);
			return;
		}

		$this->json_ok($this->token_m->get($token_id), array('message' => 'Передача выполнена'));
	}

	public function history($token_id)
	{
		if ( ! is_uuid($token_id))
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
