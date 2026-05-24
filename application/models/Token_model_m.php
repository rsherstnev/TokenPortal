<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_model_m extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function list_filtered($search = '')
	{
		$this->db->select('id, name, created_at, updated_at');
		$this->db->from('token_models');
		$this->db->where('deleted_at IS NULL', NULL, FALSE);

		if ($search !== '')
		{
			$this->db->like('name', $search);
		}

		$this->db->order_by('name', 'ASC');

		return $this->db->get()->result_array();
	}

	public function count_all()
	{
		return (int) $this->db
			->where('deleted_at IS NULL', NULL, FALSE)
			->count_all_results('token_models');
	}

	public function options()
	{
		$this->db->select('id, name');
		$this->db->from('token_models');
		$this->db->where('deleted_at IS NULL', NULL, FALSE);
		$this->db->order_by('name', 'ASC');
		return $this->db->get()->result_array();
	}

	public function get($id)
	{
		return $this->db
			->where('id', $id)
			->where('deleted_at IS NULL', NULL, FALSE)
			->get('token_models')
			->row_array();
	}

	public function create($name)
	{
		$id = uuid_v4();
		$this->db->insert('token_models', array(
			'id'         => $id,
			'name'       => $name,
			'created_at' => gmdate('Y-m-d H:i:s'),
			'updated_at' => gmdate('Y-m-d H:i:s'),
		));
		return $id;
	}

	public function update($id, $name)
	{
		return $this->db
			->where('id', $id)
			->update('token_models', array(
				'name'       => $name,
				'updated_at' => gmdate('Y-m-d H:i:s'),
			));
	}

	public function soft_delete($id)
	{
		return $this->db
			->where('id', $id)
			->update('token_models', array(
				'deleted_at' => gmdate('Y-m-d H:i:s'),
				'updated_at' => gmdate('Y-m-d H:i:s'),
			));
	}

	/**
	 * Подсчёт активных (не удалённых) токенов, использующих модель.
	 */
	public function tokens_count($id)
	{
		return (int) $this->db
			->where('token_model_id', $id)
			->where('deleted_at IS NULL', NULL, FALSE)
			->from('tokens')
			->count_all_results();
	}
}
