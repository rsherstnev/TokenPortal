<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics_m extends CI_Model {

	private function apply_without_token_join()
	{
		$this->db->from('users u');
		$this->db->join(
			'tokens t',
			't.employee_id = u.id AND t.deleted_at IS NULL AND t.is_broken = 0 AND t.is_lost = 0',
			'left',
			FALSE
		);
		$this->db->where('t.id IS NULL', NULL, FALSE);
	}

	public function count_without_token_by_department()
	{
		$this->db->select('u.person_department AS department_id, COUNT(*) AS count', FALSE);
		$this->apply_without_token_join();
		$this->db->group_by('u.person_department');
		$this->db->order_by('count', 'DESC');
		$this->db->order_by('u.person_department', 'ASC');

		$rows = $this->db->get()->result_array();
		foreach ($rows as &$row)
		{
			$row['department_id'] = (int) $row['department_id'];
			$row['count']         = (int) $row['count'];
		}
		unset($row);

		return $rows;
	}

	public function list_without_token($search = '')
	{
		$this->db->select('u.id, u.person_name, u.person_dolj, u.person_department', FALSE);
		$this->apply_without_token_join();

		if ($search !== '')
		{
			$lc   = mb_strtolower($search, 'UTF-8');
			$like = $this->db->escape_like_str($lc);
			$this->db->where("(
				LOWER(u.person_name) LIKE '%{$like}%' OR
				CAST(u.person_dolj AS CHAR) LIKE '%{$like}%' OR
				CAST(u.person_department AS CHAR) LIKE '%{$like}%'
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

	public function summary($search = '')
	{
		return array(
			'by_department' => $this->count_without_token_by_department(),
			'without_token' => $this->list_without_token($search),
			'totals'        => array(
				'without_token' => $this->count_without_token(),
			),
		);
	}
}
