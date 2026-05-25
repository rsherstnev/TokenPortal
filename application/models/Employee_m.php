<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_m extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function list_filtered($search = '')
	{
		$this->db->select('id, person_name, person_dolj, person_department, city_id, cabinet, sogl_ruk, needcrypto, pos, sd, n_type, id_num, id_printed, not_print, cr_date, updated');
		$this->db->from('users');

		if ($search !== '')
		{
			$lc   = mb_strtolower($search, 'UTF-8');
			$like = $this->db->escape_like_str($lc);
			$this->db->where("(
				LOWER(person_name) LIKE '%{$like}%' OR
				LOWER(cabinet)     LIKE '%{$like}%' OR
				LOWER(id_num)      LIKE '%{$like}%'
			)", NULL, FALSE);
		}

		$this->db->order_by('person_name', 'ASC');

		return $this->db->get()->result_array();
	}

	public function count_all()
	{
		return (int) $this->db->count_all_results('users');
	}

	public function options()
	{
		$this->db->select('id, person_name');
		$this->db->from('users');
		$this->db->order_by('person_name', 'ASC');
		return $this->db->get()->result_array();
	}

	public function get($id)
	{
		return $this->db
			->select('id, person_name, person_dolj, person_department, city_id, cabinet, sogl_ruk, needcrypto, pos, sd, n_type, id_num, id_printed, not_print, cr_date, updated')
			->where('id', (int) $id)
			->get('users')
			->row_array();
	}

	public function create($data)
	{
		$this->db->insert('users', array(
			'person_name'       => trim($data['person_name']),
			'person_dolj'       => (int) $data['person_dolj'],
			'person_department' => (int) $data['person_department'],
			'city_id'           => (int) $data['city_id'],
			'cabinet'           => trim($data['cabinet']),
			'sogl_ruk'          => isset($data['sogl_ruk']) ? (int) (bool) $data['sogl_ruk'] : 0,
			'needcrypto'        => isset($data['needcrypto']) ? (int) (bool) $data['needcrypto'] : 0,
			'pos'               => isset($data['pos']) ? (int) (bool) $data['pos'] : 0,
			'sd'                => (int) $data['sd'],
			'n_type'            => $data['n_type'],
			'id_num'            => trim($data['id_num']),
			'id_printed'        => ($data['id_printed'] ?? '') !== '' ? trim($data['id_printed']) : NULL,
			'not_print'         => isset($data['not_print']) ? (int) (bool) $data['not_print'] : 0,
		));
		return (int) $this->db->insert_id();
	}

	public function update($id, $data)
	{
		return $this->db
			->where('id', (int) $id)
			->update('users', array(
				'person_name'       => trim($data['person_name']),
				'person_dolj'       => (int) $data['person_dolj'],
				'person_department' => (int) $data['person_department'],
				'city_id'           => (int) $data['city_id'],
				'cabinet'           => trim($data['cabinet']),
				'sogl_ruk'          => isset($data['sogl_ruk']) ? (int) (bool) $data['sogl_ruk'] : 0,
				'needcrypto'        => isset($data['needcrypto']) ? (int) (bool) $data['needcrypto'] : 0,
				'pos'               => isset($data['pos']) ? (int) (bool) $data['pos'] : 0,
				'sd'                => (int) $data['sd'],
				'n_type'            => $data['n_type'],
				'id_num'            => trim($data['id_num']),
				'id_printed'        => ($data['id_printed'] ?? '') !== '' ? trim($data['id_printed']) : NULL,
				'not_print'         => isset($data['not_print']) ? (int) (bool) $data['not_print'] : 0,
			));
	}

	public function delete($id)
	{
		return $this->db
			->where('id', (int) $id)
			->delete('users');
	}
}
