<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('is_positive_int_id'))
{
	/**
	 * Проверяет положительный целочисленный идентификатор (из URL или формы).
	 */
	function is_positive_int_id($value)
	{
		if ($value === NULL || $value === '' || is_bool($value))
		{
			return FALSE;
		}

		if (is_int($value))
		{
			return $value > 0;
		}

		$s = (string) $value;

		return ctype_digit($s) && (int) $s > 0;
	}
}
