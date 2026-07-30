<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_m extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function options()
	{
		$this->db->select('id, person_name');
		$this->db->from('token_users');
		$this->db->where('is_fired', 0);
		$this->db->order_by('person_name', 'ASC');
		return $this->db->get()->result_array();
	}

	public function get($id)
	{
		return $this->db
			->select('id, person_name, person_dolj, person_department, city_id, cabinet, sogl_ruk, needcrypto, pos, sd, n_type, id_num, id_printed, not_print, is_fired, is_token_needed, cr_date, updated')
			->where('id', (int) $id)
			->get('token_users')
			->row_array();
	}

	public function get_display($id)
	{
		$this->db->select('u.id, u.person_name, u.is_token_needed, dj.name AS dolj_name, d.name AS department_name', FALSE);
		$this->db->from('token_users u');
		$this->db->join('dolj dj', 'dj.id = u.person_dolj', 'left');
		$this->db->join('departments d', 'd.id = u.person_department', 'left');
		$this->db->where('u.id', (int) $id);
		$row = $this->db->get()->row_array();
		if ($row)
		{
			$row['is_token_needed'] = ! empty($row['is_token_needed']);
		}

		return $row;
	}

	public function update_is_token_needed($id, $is_token_needed)
	{
		$this->db->where('id', (int) $id);
		$this->db->update('token_users', array(
			'is_token_needed' => (int) (bool) $is_token_needed,
		));

		return $this->db->affected_rows() >= 0;
	}
}
