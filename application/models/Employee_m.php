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
		$this->db->order_by('person_name', 'ASC');
		return $this->db->get()->result_array();
	}

	public function get($id)
	{
		return $this->db
			->select('id, person_name, person_dolj, person_department, city_id, cabinet, sogl_ruk, needcrypto, pos, sd, n_type, id_num, id_printed, not_print, is_fired, cr_date, updated')
			->where('id', (int) $id)
			->get('token_users')
			->row_array();
	}
}
