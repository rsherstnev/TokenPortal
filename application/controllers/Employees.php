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
			'page_title' => 'Пользователи',
			'active_nav' => 'employees',
		);
		$this->load->view('templates/header', $data);
		$this->load->view('employees/index', $data);
		$this->load->view('templates/footer', $data);
	}

	public function list_json()
	{
		$search = trim((string) $this->input->get('q'));
		$rows   = $this->employee_m->list_filtered($search);
		$total  = $this->employee_m->count_all();
		$this->json_ok($rows, array('count' => count($rows), 'total' => $total));
	}

	public function options()
	{
		$this->json_ok($this->employee_m->options());
	}

	public function get($id)
	{
		if ( ! $this->_valid_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		$row = $this->employee_m->get($id);
		if ( ! $row)
		{
			$this->json_error('Пользователь не найден', 404);
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
		$this->json_ok($this->employee_m->get($id), array('message' => 'Пользователь создан'));
	}

	public function update($id)
	{
		$this->require_post();
		if ( ! $this->_valid_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		if ( ! $this->employee_m->get($id))
		{
			$this->json_error('Пользователь не найден', 404);
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
		$this->json_ok($this->employee_m->get($id), array('message' => 'Пользователь обновлён'));
	}

	public function delete($id)
	{
		$this->require_post();
		if ( ! $this->_valid_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		if ( ! $this->employee_m->get($id))
		{
			$this->json_error('Пользователь не найден', 404);
			return;
		}
		$this->employee_m->delete($id);
		$this->json_ok(array(), array('message' => 'Пользователь удалён'));
	}

	private function _valid_id($id)
	{
		return ctype_digit((string) $id) && (int) $id > 0;
	}

	private function collect_payload()
	{
		return array(
			'person_name'       => (string) $this->input->post('person_name'),
			'person_dolj'       => (string) $this->input->post('person_dolj'),
			'person_department' => (string) $this->input->post('person_department'),
			'city_id'           => (string) $this->input->post('city_id'),
			'cabinet'           => (string) $this->input->post('cabinet'),
			'sogl_ruk'          => (int) (bool) $this->input->post('sogl_ruk'),
			'needcrypto'        => (int) (bool) $this->input->post('needcrypto'),
			'pos'               => (int) (bool) $this->input->post('pos'),
			'sd'                => (string) $this->input->post('sd'),
			'n_type'            => (string) $this->input->post('n_type'),
			'id_num'            => (string) $this->input->post('id_num'),
			'id_printed'        => (string) $this->input->post('id_printed'),
			'not_print'         => (int) (bool) $this->input->post('not_print'),
		);
	}

	private function validate($p)
	{
		$errors = array();
		if (trim($p['person_name']) === '')
		{
			$errors['person_name'] = 'Укажите ФИО';
		}
		if ( ! is_numeric($p['person_dolj']))
		{
			$errors['person_dolj'] = 'Укажите должность (код)';
		}
		if ( ! is_numeric($p['person_department']))
		{
			$errors['person_department'] = 'Укажите отдел (код)';
		}
		if ( ! is_numeric($p['city_id']))
		{
			$errors['city_id'] = 'Укажите код города';
		}
		if (trim($p['cabinet']) === '')
		{
			$errors['cabinet'] = 'Укажите кабинет';
		}
		if ( ! is_numeric($p['sd']))
		{
			$errors['sd'] = 'Укажите SD';
		}
		$valid_types = array('', 'пром', 'энергонадзор', 'стройнадзор', 'ГТС');
		if ( ! in_array($p['n_type'], $valid_types, TRUE))
		{
			$errors['n_type'] = 'Недопустимый тип надзора';
		}
		if (trim($p['id_num']) === '')
		{
			$errors['id_num'] = 'Укажите номер удостоверения';
		}
		return $errors;
	}
}
