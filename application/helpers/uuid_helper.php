<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('uuid_v4'))
{
	/**
	 * Generate a random RFC 4122 v4 UUID using cryptographically secure bytes.
	 */
	function uuid_v4()
	{
		$data = random_bytes(16);
		$data[6] = chr((ord($data[6]) & 0x0F) | 0x40);
		$data[8] = chr((ord($data[8]) & 0x3F) | 0x80);
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}

if ( ! function_exists('is_uuid'))
{
	function is_uuid($value)
	{
		return 1;
	}
}
