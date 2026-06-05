<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Запись действий в веб-приложении в JSON-файл на сервере.
 */
class Audit_log {

	/** @var CI_Controller */
	protected $CI;

	protected $enabled = FALSE;

	/** @var string */
	protected $file_path = '';

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->config->load('audit_log', TRUE);

		$this->enabled = (bool) $this->CI->config->item('audit_log_enabled', 'audit_log');
		$this->file_path = (string) $this->CI->config->item('audit_log_file', 'audit_log');
	}

	/**
	 * @param string $message   Подробное описание действия на русском языке
	 * @param array  $context   Дополнительные поля (controller, action, entity_id, …)
	 */
	public function write($message, array $context = array())
	{
		if ( ! $this->enabled || $this->file_path === '')
		{
			return;
		}

		$message = trim((string) $message);
		if ($message === '')
		{
			return;
		}

		$entry = array(
			'timestamp' => date('c'),
			'ip'        => $this->CI->input->ip_address(),
			'message'   => $message,
		);

		if ( ! empty($context))
		{
			$entry['context'] = $context;
		}

		$line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
		if ($line === FALSE)
		{
			log_message('error', 'Audit_log: не удалось сериализовать запись журнала');
			return;
		}

		$line .= "\n";
		$path = $this->resolve_path($this->file_path);
		$dir = dirname($path);

		if ( ! is_dir($dir) && ! @mkdir($dir, 0775, TRUE) && ! is_dir($dir))
		{
			log_message('error', 'Audit_log: не удалось создать каталог '.$dir);
			return;
		}

		if (@file_put_contents($path, $line, FILE_APPEND | LOCK_EX) === FALSE)
		{
			$hint = $this->write_failure_hint($path);
			log_message('error', 'Audit_log: не удалось записать в файл '.$path.'. '.$hint);
			// log_threshold часто 0 — тогда log_message никуда не попадает; дублируем в error_log PHP/Apache.
			error_log('[Audit_log] не удалось записать в '.$path.'. '.$hint);
		}
	}

	/**
	 * Краткая подсказка по правам для администратора.
	 */
	protected function write_failure_hint($path)
	{
		$dir = dirname($path);
		if ( ! is_dir($dir))
		{
			return 'Каталог не существует: '.$dir;
		}
		if ( ! is_writable($dir))
		{
			return 'Нет прав на запись в каталог '.$dir
				.' (владелец: '.@fileowner($dir).', права: '.substr(sprintf('%o', @fileperms($dir)), -4).'). '
				.'Выдайте веб-серверу права, например: chown www-data:www-data '.$dir.' && chmod 775 '.$dir;
		}
		if (file_exists($path) && ! is_writable($path))
		{
			return 'Файл существует, но недоступен для записи: '.$path;
		}

		return 'Проверьте SELinux/AppArmor или диск (quota).';
	}

	/**
	 * Преобразует относительный путь в абсолютный (от корня приложения / FCPATH).
	 */
	protected function resolve_path($path)
	{
		if ($path === '' || $path[0] === '/' || preg_match('#^[A-Za-z]:[\\\\/]#', $path))
		{
			return $path;
		}

		if (defined('FCPATH'))
		{
			return rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
		}

		return $path;
	}
}
