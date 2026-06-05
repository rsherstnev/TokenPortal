<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics_m extends CI_Model {

	private function active_token_sql()
	{
		return 'deleted_at IS NULL AND is_broken = 0 AND is_lost = 0';
	}

	private function employees_with_multiple_tokens_subquery()
	{
		$active = $this->active_token_sql();

		return '(SELECT employee_id FROM tokens WHERE '.$active.' AND employee_id IS NOT NULL GROUP BY employee_id HAVING COUNT(*) > 1)';
	}

	private function apply_without_token_join()
	{
		$this->db->from('token_users u');
		$this->db->join(
			'tokens t',
			't.employee_id = u.id AND t.deleted_at IS NULL AND t.is_broken = 0 AND t.is_lost = 0',
			'left',
			FALSE
		);
		$this->db->where('t.id IS NULL', NULL, FALSE);
	}

	public function list_without_token($search = '')
	{
		$this->db->select('u.id, u.person_name, u.person_dolj, u.person_department, dj.name AS dolj_name, d.name AS department_name', FALSE);
		$this->apply_without_token_join();
		$this->db->join('dolj dj', 'dj.id = u.person_dolj', 'left');
		$this->db->join('departments d', 'd.id = u.person_department', 'left');

		if ($search !== '')
		{
			$lc   = mb_strtolower($search, 'UTF-8');
			$like = $this->db->escape_like_str($lc);
			$this->db->where("(
				LOWER(u.person_name) LIKE '%{$like}%' OR
				LOWER(dj.name) LIKE '%{$like}%' OR
				LOWER(d.name) LIKE '%{$like}%'
			)", NULL, FALSE);
		}

		$this->db->order_by('u.person_name', 'ASC');

		return $this->db->get()->result_array();
	}

	public function count_without_token()
	{
		$this->db->select('COUNT(*) AS cnt', FALSE);
		$this->apply_without_token_join();
		$row = $this->db->get()->row_array();

		return (int) ($row['cnt'] ?? 0);
	}

	public function list_multiple_tokens($search = '')
	{
		$this->db->select(
			'u.id, u.person_name, u.person_dolj, u.person_department, dj.name AS dolj_name, d.name AS department_name, COUNT(t.id) AS token_count',
			FALSE
		);
		$this->db->from('token_users u');
		$this->db->join(
			'tokens t',
			't.employee_id = u.id AND t.deleted_at IS NULL AND t.is_broken = 0 AND t.is_lost = 0',
			'inner',
			FALSE
		);
		$this->db->join('dolj dj', 'dj.id = u.person_dolj', 'left');
		$this->db->join('departments d', 'd.id = u.person_department', 'left');
		$this->db->group_by('u.id');
		$this->db->having('COUNT(t.id) >', 1);

		if ($search !== '')
		{
			$lc   = mb_strtolower($search, 'UTF-8');
			$like = $this->db->escape_like_str($lc);
			$this->db->where("(
				LOWER(u.person_name) LIKE '%{$like}%' OR
				LOWER(dj.name) LIKE '%{$like}%' OR
				LOWER(d.name) LIKE '%{$like}%'
			)", NULL, FALSE);
		}

		$this->db->order_by('token_count', 'DESC');
		$this->db->order_by('u.person_name', 'ASC');

		$rows = $this->db->get()->result_array();
		foreach ($rows as &$row)
		{
			$row['token_count'] = (int) $row['token_count'];
		}
		unset($row);

		return $rows;
	}

	public function count_multiple_tokens()
	{
		$sub = $this->employees_with_multiple_tokens_subquery();

		$this->db->select('COUNT(*) AS cnt', FALSE);
		$this->db->from('token_users u');
		$this->db->where('u.id IN '.$sub, NULL, FALSE);
		$row = $this->db->get()->row_array();

		return (int) ($row['cnt'] ?? 0);
	}

	public function list_stuck_tokens($search = '')
	{
		$this->db->select(
			't.id AS token_id, t.serial_number, tm.name AS model_name, e.id AS employee_id, e.person_name, e.person_dolj, e.person_department, dj.name AS dolj_name, d.name AS department_name',
			FALSE
		);
		$this->db->from('tokens t');
		$this->db->join('token_users e', 'e.id = t.employee_id AND e.is_fired = 1', 'inner', FALSE);
		$this->db->join('token_models tm', 'tm.id = t.token_model_id', 'inner');
		$this->db->join('dolj dj', 'dj.id = e.person_dolj', 'left');
		$this->db->join('departments d', 'd.id = e.person_department', 'left');
		$this->db->where('t.deleted_at IS NULL', NULL, FALSE);
		$this->db->where('t.is_broken', 0);
		$this->db->where('t.is_lost', 0);
		$this->db->where('t.employee_id IS NOT NULL', NULL, FALSE);

		if ($search !== '')
		{
			$lc   = mb_strtolower($search, 'UTF-8');
			$like = $this->db->escape_like_str($lc);
			$this->db->where("(
				LOWER(e.person_name) LIKE '%{$like}%' OR
				LOWER(tm.name) LIKE '%{$like}%' OR
				LOWER(t.serial_number) LIKE '%{$like}%' OR
				LOWER(dj.name) LIKE '%{$like}%' OR
				LOWER(d.name) LIKE '%{$like}%'
			)", NULL, FALSE);
		}

		$this->db->order_by('e.person_name', 'ASC');
		$this->db->order_by('tm.name', 'ASC');
		$this->db->order_by('t.serial_number', 'ASC');

		return $this->db->get()->result_array();
	}

	public function count_stuck_tokens()
	{
		$this->db->select('COUNT(*) AS cnt', FALSE);
		$this->db->from('tokens t');
		$this->db->join('token_users e', 'e.id = t.employee_id AND e.is_fired = 1', 'inner', FALSE);
		$this->db->where('t.deleted_at IS NULL', NULL, FALSE);
		$this->db->where('t.is_broken', 0);
		$this->db->where('t.is_lost', 0);
		$this->db->where('t.employee_id IS NOT NULL', NULL, FALSE);
		$row = $this->db->get()->row_array();

		return (int) ($row['cnt'] ?? 0);
	}
}
