<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employees extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('employee_m');
	}

	public function get($id)
	{
		if ( ! is_positive_int_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}

		$row = $this->employee_m->get_display($id);
		if ( ! $row)
		{
			$this->json_error('Сотрудник не найден', 404);
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

		$existing = $this->employee_m->get($id);
		if ( ! $existing)
		{
			$this->json_error('Сотрудник не найден', 404);
			return;
		}

		$is_token_needed = (int) (bool) $this->input->post('is_token_needed');

		$this->employee_m->update_is_token_needed($id, $is_token_needed);

		$row = $this->employee_m->get_display($id);
		$this->audit_log('Изменён признак «Токен нужен»', array(
			'employee_id'     => (int) $id,
			'person_name'     => $row['person_name'] ?? '',
			'is_token_needed' => $is_token_needed,
		));

		$this->json_ok($row, array('message' => 'Сохранено'));
	}
}
