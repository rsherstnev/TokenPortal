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

		// Возврат на склад (to = NULL) или передача конкретному сотруднику.
		if ($to_employee_id !== NULL)
		{
			if ( ! is_uuid($to_employee_id))
			{
				$this->json_error('Выберите сотрудника', 422, array('to_employee_id' => 'Выберите сотрудника'));
				return;
			}
			$emp = $this->employee_m->get($to_employee_id);
			if ( ! $emp || (int) $emp['is_active'] !== 1)
			{
				$this->json_error('Сотрудник не найден или неактивен', 422, array('to_employee_id' => 'Сотрудник не найден или неактивен'));
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
