<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tokens extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('token_m');
		$this->load->model('token_model_m');
		$this->load->model('employee_m');
	}

	public function index()
	{
		$data = array(
			'active_nav'    => 'tokens',
			'models'        => $this->token_model_m->options(),
			'employees'     => $this->employee_m->options(),
		);
		$this->load->view('templates/header', $data);
		$this->load->view('tokens/index', $data);
		$this->load->view('templates/footer', $data);
	}

	public function employee_options()
	{
		$this->json_ok($this->employee_m->options());
	}

	public function list_json()
	{
		$search = trim((string) $this->input->get('q'));
		$status = (string) $this->input->get('status');
		if ( ! in_array($status, array('all', 'issued', 'not_issued', 'broken', 'lost'), TRUE))
		{
			$status = 'not_issued';
		}
		$rows  = $this->token_m->list_filtered($search, $status);
		$total = $this->token_m->count_all($status);
		$this->json_ok($rows, array('count' => count($rows), 'total' => $total));
	}

	public function get($id)
	{
		if ( ! is_positive_int_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}
		$row = $this->token_m->get($id);
		if ( ! $row)
		{
			$this->json_error('Токен не найден', 404);
			return;
		}
		$this->json_ok($row);
	}

	public function create()
	{
		$this->require_post();

		$model_id = trim((string) $this->input->post('token_model_id'));
		$serial   = trim((string) $this->input->post('serial_number'));
		$is_broken = (int) (bool) $this->input->post('is_broken');
		$is_lost   = (int) (bool) $this->input->post('is_lost');
		$comment   = trim((string) $this->input->post('comment'));

		$errors = $this->validate_input($model_id, $serial);
		if ( ! empty($errors))
		{
			$this->json_error('Проверьте поля формы', 422, $errors);
			return;
		}

		$id = $this->token_m->create(array(
			'token_model_id' => $model_id,
			'serial_number'  => $serial,
			'is_broken'      => $is_broken,
			'is_lost'        => $is_lost,
			'comment'        => $comment,
		));

		$created = $this->token_m->get($id);
		$model = $this->token_model_m->get($model_id);
		$this->audit_log(
			audit_log_token_create_message($id, $created ?: array('serial_number' => $serial), $model ? $model['name'] : NULL),
			array('action' => 'token.create', 'entity_id' => $id)
		);

		$this->json_ok($created, array('message' => 'Токен создан'));
	}

	public function update($id)
	{
		$this->require_post();

		if ( ! is_positive_int_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}

		$existing = $this->token_m->get($id);
		if ( ! $existing)
		{
			$this->json_error('Токен не найден', 404);
			return;
		}

		$model_id = trim((string) $this->input->post('token_model_id'));
		$serial   = trim((string) $this->input->post('serial_number'));
		$is_broken = (int) (bool) $this->input->post('is_broken');
		$is_lost   = (int) (bool) $this->input->post('is_lost');
		$comment   = trim((string) $this->input->post('comment'));

		$errors = $this->validate_input($model_id, $serial, $id);
		if ( ! empty($errors))
		{
			$this->json_error('Проверьте поля формы', 422, $errors);
			return;
		}

		$new_model = $this->token_model_m->get($model_id);
		$after_input = array(
			'token_model_id' => $model_id,
			'serial_number'  => $serial,
			'is_broken'      => $is_broken,
			'is_lost'        => $is_lost,
			'comment'        => $comment,
		);

		$this->token_m->update($id, $after_input);

		foreach (audit_log_token_update_messages($existing, $after_input, $new_model ? $new_model['name'] : NULL) as $audit_message)
		{
			$this->audit_log($audit_message, array('action' => 'token.update', 'entity_id' => (int) $id));
		}

		$this->json_ok($this->token_m->get($id), array('message' => 'Токен обновлён'));
	}

	public function delete($id)
	{
		$this->require_post();

		if ( ! is_positive_int_id($id))
		{
			$this->json_error('Некорректный идентификатор', 400);
			return;
		}

		$existing = $this->token_m->get($id);
		if ( ! $existing)
		{
			$this->json_error('Токен не найден', 404);
			return;
		}

		$this->token_m->soft_delete($id);
		$this->audit_log(
			audit_log_token_delete_message($existing),
			array('action' => 'token.delete', 'entity_id' => (int) $id)
		);
		$this->json_ok(array(), array('message' => 'Токен удалён'));
	}

	private function validate_input($model_id, $serial, $exclude_id = NULL)
	{
		$errors = array();
		$model_valid = FALSE;
		if ( ! is_positive_int_id($model_id))
		{
			$errors['token_model_id'] = 'Выберите модель токена';
		}
		else
		{
			$model = $this->token_model_m->get($model_id);
			if ( ! $model)
			{
				$errors['token_model_id'] = 'Модель не найдена';
			}
			else
			{
				$model_valid = TRUE;
			}
		}
		if ($serial === '')
		{
			$errors['serial_number'] = 'Укажите серийный номер';
		}
		elseif (mb_strlen($serial) > 128)
		{
			$errors['serial_number'] = 'Серийный номер слишком длинный (макс. 128 символов)';
		}
		elseif ($model_valid && $this->token_m->exists_by_model_and_serial($model_id, $serial, $exclude_id))
		{
			$errors['serial_number'] = 'Токен с такой моделью и серийным номером уже существует';
		}
		return $errors;
	}
}
