<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_m extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function list_filtered($search = '', $only_active = FALSE)
	{
		$this->db->select("
			id, firstname, lastname, patronymic, email, cabinet, is_active,
			TRIM(CONCAT_WS(' ', lastname, firstname, patronymic)) AS fullname,
			created_at, updated_at
		", FALSE);
		$this->db->from('employees');
		$this->db->where('deleted_at IS NULL', NULL, FALSE);

		if ($only_active)
		{
			$this->db->where('is_active', 1);
		}

		if ($search !== '')
		{
			$lc = mb_strtolower($search, 'UTF-8');
			$like = $this->db->escape_like_str($lc);
			$this->db->where("(
				LOWER(firstname)  LIKE '%{$like}%' OR
				LOWER(lastname)   LIKE '%{$like}%' OR
				LOWER(patronymic) LIKE '%{$like}%' OR
				LOWER(email)      LIKE '%{$like}%' OR
				LOWER(cabinet)    LIKE '%{$like}%'
			)", NULL, FALSE);
		}

		$this->db->order_by('lastname', 'ASC');
		$this->db->order_by('firstname', 'ASC');

		return $this->db->get()->result_array();
	}

	public function count_all()
	{
		return (int) $this->db
			->where('deleted_at IS NULL', NULL, FALSE)
			->count_all_results('employees');
	}

	public function options()
	{
		return $this->list_filtered('', TRUE);
	}

	public function get($id)
	{
		return $this->db
			->select("*, TRIM(CONCAT_WS(' ', lastname, firstname, patronymic)) AS fullname", FALSE)
			->where('id', $id)
			->where('deleted_at IS NULL', NULL, FALSE)
			->get('employees')
			->row_array();
	}

	public function create($data)
	{
		$id = uuid_v4();
		$now = date('Y-m-d H:i:s');
		$this->db->insert('employees', array(
			'id'         => $id,
			'firstname'  => trim($data['firstname']),
			'lastname'   => trim($data['lastname']),
			'patronymic' => trim($data['patronymic'] ?? ''),
			'email'      => trim($data['email'] ?? '') ?: NULL,
			'cabinet'    => trim($data['cabinet'] ?? '') ?: NULL,
			'is_active'  => isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1,
			'created_at' => $now,
			'updated_at' => $now,
		));
		return $id;
	}

	public function update($id, $data)
	{
		return $this->db
			->where('id', $id)
			->update('employees', array(
				'firstname'  => trim($data['firstname']),
				'lastname'   => trim($data['lastname']),
				'patronymic' => trim($data['patronymic'] ?? ''),
				'email'      => trim($data['email'] ?? '') ?: NULL,
				'cabinet'    => trim($data['cabinet'] ?? '') ?: NULL,
				'is_active'  => isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1,
				'updated_at' => date('Y-m-d H:i:s'),
			));
	}

	public function soft_delete($id)
	{
		return $this->db
			->where('id', $id)
			->update('employees', array(
				'deleted_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			));
	}
}
