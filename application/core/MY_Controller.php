<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Базовый контроллер. Содержит JSON-helpers и проверку AJAX.
 */
class MY_Controller extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('audit_log', NULL, 'audit_logger');
	}

	/**
	 * Запись действия в журнал аудита (JSON-файл на сервере).
	 *
	 * @param string $message  Описание на русском языке
	 * @param array  $context  Дополнительный контекст (action, entity_id, …)
	 */
	protected function audit_log($message, array $context = array())
	{
		$this->audit_logger->write($message, $context);
	}

	protected function json_ok($payload = array(), $extra = array())
	{
		$response = array_merge(array('ok' => TRUE), $extra);
		if ( ! empty($payload))
		{
			$response['data'] = $payload;
		}
		// Свежий CSRF-токен возвращаем, чтобы AJAX-клиент мог его обновить.
		$response['csrf'] = array(
			'name'  => $this->security->get_csrf_token_name(),
			'hash'  => $this->security->get_csrf_hash(),
		);
		$this->output
			->set_status_header(200)
			->set_content_type('application/json; charset=utf-8')
			->set_output(json_encode($response, JSON_UNESCAPED_UNICODE));
	}

	protected function json_error($message, $status = 422, $errors = array())
	{
		$this->output
			->set_status_header($status)
			->set_content_type('application/json; charset=utf-8')
			->set_output(json_encode(array(
				'ok'      => FALSE,
				'message' => $message,
				'errors'  => $errors,
				'csrf'    => array(
					'name' => $this->security->get_csrf_token_name(),
					'hash' => $this->security->get_csrf_hash(),
				),
			), JSON_UNESCAPED_UNICODE));
	}

	protected function require_post()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			$this->json_error('Метод не поддерживается', 405);
			exit;
		}
	}
}
