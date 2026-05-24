<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_models extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('token_model_m');
	}

	public function list_json()
	{
		$search = trim((string) $this->input->get('q'));
		$rows  = $this->token_model_m->list_filtered($search);
		$total = $this->token_model_m->count_all();
		$this->json_ok($rows, array('count' => count($rows), 'total' => $total));
	}

	public function options()
	{
		$this->json_ok($this->token_model_m->options());
	}

	public function get($id)
	{
		if ( ! is_uuid($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		$row = $this->token_model_m->get($id);
		if ( ! $row)
		{
			$this->json_error('Модель не найдена', 404);
			return;
		}
		$this->json_ok($row);
	}

	public function create()
	{
		$this->require_post();
		$name = trim((string) $this->input->post('name'));
		$errors = $this->validate_name($name);
		if ( ! empty($errors))
		{
			$this->json_error('Проверьте поля формы', 422, $errors);
			return;
		}
		$id = $this->token_model_m->create($name);
		$this->json_ok($this->token_model_m->get($id), array('message' => 'Модель создана'));
	}

	public function update($id)
	{
		$this->require_post();
		if ( ! is_uuid($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		if ( ! $this->token_model_m->get($id))
		{
			$this->json_error('Модель не найдена', 404);
			return;
		}
		$name = trim((string) $this->input->post('name'));
		$errors = $this->validate_name($name);
		if ( ! empty($errors))
		{
			$this->json_error('Проверьте поля формы', 422, $errors);
			return;
		}
		$this->token_model_m->update($id, $name);
		$this->json_ok($this->token_model_m->get($id), array('message' => 'Модель обновлена'));
	}

	public function delete($id)
	{
		$this->require_post();
		if ( ! is_uuid($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		if ( ! $this->token_model_m->get($id))
		{
			$this->json_error('Модель не найдена', 404);
			return;
		}
		if ($this->token_model_m->tokens_count($id) > 0)
		{
			$this->json_error('Невозможно удалить: есть токены этой модели', 409);
			return;
		}
		$this->token_model_m->soft_delete($id);
		$this->json_ok(array(), array('message' => 'Модель удалена'));
	}

	private function validate_name($name)
	{
		$errors = array();
		if ($name === '')
		{
			$errors['name'] = 'Укажите название модели';
		}
		elseif (mb_strlen($name) > 128)
		{
			$errors['name'] = 'Название слишком длинное (макс. 128 символов)';
		}
		return $errors;
	}
}
