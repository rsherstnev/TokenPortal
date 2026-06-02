<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('transfer_act_format_date'))
{
	/**
	 * Дата передачи для акта: дд.мм.гггг (UTC из БД).
	 */
	function transfer_act_format_date($transferred_at)
	{
		if ( ! $transferred_at)
		{
			return '';
		}

		$ts = strtotime($transferred_at . ' UTC');
		if ($ts === FALSE)
		{
			return '';
		}

		return gmdate('d.m.Y', $ts);
	}
}

if ( ! function_exists('transfer_act_strip_department_number'))
{
	/**
	 * Убирает ведущий числовой префикс из названия отдела (например, «12 Отдел кадров» → «Отдел кадров»).
	 */
	function transfer_act_strip_department_number($department_name)
	{
		$name = trim((string) $department_name);
		if ($name === '')
		{
			return '';
		}

		$stripped = preg_replace('/^\s*\d+[\s.\-:,]*/u', '', $name);

		return trim($stripped !== '' ? $stripped : $name);
	}
}

if ( ! function_exists('transfer_act_format_party'))
{
	/**
	 * Должность, отдел и ФИО; для склада — «Склад».
	 * Формат: «{должность}, {отдел} и {ФИО}».
	 */
	function transfer_act_format_party($dolj_name, $department_name, $person_name, $employee_id)
	{
		if ($employee_id === NULL || $employee_id === '')
		{
			return 'Склад';
		}

		$dolj = trim((string) $dolj_name);
		$dept = transfer_act_strip_department_number($department_name);
		$name = trim((string) $person_name);

		$head = '';
		if ($dolj !== '' && $dept !== '')
		{
			$head = $dolj . ', ' . $dept;
		}
		elseif ($dolj !== '')
		{
			$head = $dolj;
		}
		elseif ($dept !== '')
		{
			$head = $dept;
		}

		if ($name !== '')
		{
			return $head !== '' ? $head . ' ' . $name : $name;
		}

		return $head !== '' ? $head : 'Склад';
	}
}

if ( ! function_exists('transfer_act_sanitize_filename'))
{
	/**
	 * Безопасное имя файла из ФИО (без расширения).
	 */
	function transfer_act_sanitize_filename($name)
	{
		$name = trim((string) $name);
		$name = preg_replace('/[\\\\\/\:\*\?\"\<\>\|]/u', '', $name);
		$name = preg_replace('/\s+/u', ' ', $name);

		return $name !== '' ? $name : '';
	}
}

if ( ! function_exists('transfer_act_download_filename'))
{
	/**
	 * Имя файла: ФИО принимающего токен; для склада — «Склад».
	 */
	function transfer_act_download_filename($to_fullname, $to_employee_id)
	{
		if ($to_employee_id === NULL || $to_employee_id === '')
		{
			$base = 'Склад';
		}
		else
		{
			$base = transfer_act_sanitize_filename($to_fullname);
			if ($base === '')
			{
				$base = 'Получатель';
			}
		}

		return $base . '.docx';
	}
}

if ( ! function_exists('transfer_act_ascii_filename'))
{
	/** ASCII-имя для заголовка Content-Disposition (старые браузеры). */
	function transfer_act_ascii_filename($utf8_filename)
	{
		$base = preg_replace('/\.docx$/iu', '', $utf8_filename);
		$ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
		if ($ascii === FALSE || trim($ascii) === '')
		{
			return 'document.docx';
		}

		$ascii = preg_replace('/[^a-zA-Z0-9._-]+/', '_', trim($ascii));
		$ascii = trim($ascii, '._-');

		return ($ascii !== '' ? $ascii : 'document') . '.docx';
	}
}
