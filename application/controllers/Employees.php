<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employees extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('employee_m');
	}

	public function index()
	{
		$data = array(
			'page_title' => 'Сотрудники',
			'active_nav' => 'employees',
		);
		$this->load->view('templates/header', $data);
		$this->load->view('employees/index', $data);
		$this->load->view('templates/footer', $data);
	}

	public function list_json()
	{
		$search = trim((string) $this->input->get('q'));
		$rows  = $this->employee_m->list_filtered($search, FALSE);
		$total = $this->employee_m->count_all();
		$this->json_ok($rows, array('count' => count($rows), 'total' => $total));
	}

	public function options()
	{
		$this->json_ok($this->employee_m->options());
	}

	public function get($id)
	{
		if ( ! is_uuid($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		$row = $this->employee_m->get($id);
		if ( ! $row)
		{
			$this->json_error('Сотрудник не найден', 404);
			return;
		}
		$this->json_ok($row);
	}

	public function create()
	{
		$this->require_post();
		$payload = $this->collect_payload();
		$errors  = $this->validate($payload);
		if ( ! empty($errors))
		{
			$this->json_error('Проверьте поля формы', 422, $errors);
			return;
		}
		$id = $this->employee_m->create($payload);
		$this->json_ok($this->employee_m->get($id), array('message' => 'Сотрудник создан'));
	}

	public function update($id)
	{
		$this->require_post();
		if ( ! is_uuid($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		if ( ! $this->employee_m->get($id))
		{
			$this->json_error('Сотрудник не найден', 404);
			return;
		}
		$payload = $this->collect_payload();
		$errors  = $this->validate($payload);
		if ( ! empty($errors))
		{
			$this->json_error('Проверьте поля формы', 422, $errors);
			return;
		}
		$this->employee_m->update($id, $payload);
		$this->json_ok($this->employee_m->get($id), array('message' => 'Сотрудник обновлён'));
	}

	public function delete($id)
	{
		$this->require_post();
		if ( ! is_uuid($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		if ( ! $this->employee_m->get($id))
		{
			$this->json_error('Сотрудник не найден', 404);
			return;
		}
		$this->employee_m->soft_delete($id);
		$this->json_ok(array(), array('message' => 'Сотрудник удалён'));
	}

	private function collect_payload()
	{
		return array(
			'firstname'  => (string) $this->input->post('firstname'),
			'lastname'   => (string) $this->input->post('lastname'),
			'patronymic' => (string) $this->input->post('patronymic'),
			'email'      => (string) $this->input->post('email'),
			'cabinet'    => (string) $this->input->post('cabinet'),
			'is_active'  => (int) (bool) $this->input->post('is_active'),
		);
	}

	private function validate($p)
	{
		$errors = array();
		if (trim($p['firstname']) === '')
		{
			$errors['firstname'] = 'Укажите имя';
		}
		if (trim($p['lastname']) === '')
		{
			$errors['lastname'] = 'Укажите фамилию';
		}
		if (trim($p['email']) !== '' && ! filter_var(trim($p['email']), FILTER_VALIDATE_EMAIL))
		{
			$errors['email'] = 'Некорректный email';
		}
		return $errors;
	}
}
