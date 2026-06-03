<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_transfer_m extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Транзакционная передача токена сотруднику.
	 * @return int|false ID созданной передачи или false при ошибке.
	 */
	public function transfer($token_id, $to_employee_id, $comment = '', $transferred_at = NULL)
	{
		$token_id = (int) $token_id;
		$this->db->trans_start();

		$token = $this->db
			->select('employee_id')
			->where('id', $token_id)
			->where('deleted_at IS NULL', NULL, FALSE)
			->get('tokens')
			->row_array();

		if ( ! $token)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$from_employee_id = $token['employee_id'];

		// Запрещаем «передачу самому себе»
		if ($from_employee_id && $to_employee_id && $from_employee_id === $to_employee_id)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$now = gmdate('Y-m-d H:i:s');
		$effective_at = ($transferred_at !== NULL) ? $transferred_at : $now;

		$this->db->insert('token_transfers', array(
			'token_id'         => $token_id,
			'from_employee_id' => $from_employee_id,
			'to_employee_id'   => $to_employee_id,
			'comment'          => $comment !== '' ? $comment : NULL,
			'transferred_at'   => $effective_at,
			'created_at'       => $now,
		));
		$transfer_id = (int) $this->db->insert_id();

		$this->db
			->where('id', $token_id)
			->update('tokens', array(
				'employee_id' => $to_employee_id,
				'updated_at'  => $now,
			));

		$this->db->trans_complete();

		return $this->db->trans_status() ? $transfer_id : FALSE;
	}

	public function get($id)
	{
		$this->db->select("
			tr.id,
			tr.token_id,
			tr.comment,
			tr.transferred_at,
			tr.from_employee_id,
			tr.to_employee_id,
			tm.name AS model_name,
			t.serial_number,
			fe.person_name AS from_fullname,
			fe.is_fired AS from_is_fired,
			te.person_name AS to_fullname,
			te.is_fired AS to_is_fired
		", FALSE);
		$this->db->from('token_transfers tr');
		$this->db->join('tokens t', 't.id = tr.token_id', 'inner');
		$this->db->join('token_models tm', 'tm.id = t.token_model_id', 'inner');
		$this->db->join('users fe', 'fe.id = tr.from_employee_id', 'left');
		$this->db->join('users te', 'te.id = tr.to_employee_id', 'left');
		$this->db->where('tr.id', (int) $id);
		$this->db->where('t.deleted_at IS NULL', NULL, FALSE);

		$row = $this->db->get()->row_array();

		return $row ?: FALSE;
	}

	/**
	 * Данные передачи для акта приёма-передачи (должность, отдел сотрудников).
	 */
	public function get_act_data($id)
	{
		$this->db->select("
			tr.id,
			tr.transferred_at,
			tr.from_employee_id,
			tr.to_employee_id,
			tm.name AS model_name,
			t.serial_number,
			fe.person_name AS from_fullname,
			fdj.name AS from_dolj_name,
			fd.name AS from_department_name,
			te.person_name AS to_fullname,
			tdj.name AS to_dolj_name,
			td.name AS to_department_name
		", FALSE);
		$this->db->from('token_transfers tr');
		$this->db->join('tokens t', 't.id = tr.token_id', 'inner');
		$this->db->join('token_models tm', 'tm.id = t.token_model_id', 'inner');
		$this->db->join('users fe', 'fe.id = tr.from_employee_id', 'left');
		$this->db->join('dolj fdj', 'fdj.id = fe.person_dolj', 'left');
		$this->db->join('departments fd', 'fd.id = fe.person_department', 'left');
		$this->db->join('users te', 'te.id = tr.to_employee_id', 'left');
		$this->db->join('dolj tdj', 'tdj.id = te.person_dolj', 'left');
		$this->db->join('departments td', 'td.id = te.person_department', 'left');
		$this->db->where('tr.id', (int) $id);
		$this->db->where('t.deleted_at IS NULL', NULL, FALSE);

		$row = $this->db->get()->row_array();

		return $row ?: FALSE;
	}

	public function update_comment($id, $comment)
	{
		if ( ! $this->get($id))
		{
			return FALSE;
		}

		$this->db
			->where('id', (int) $id)
			->update('token_transfers', array(
				'comment' => $comment !== '' ? $comment : NULL,
			));

		return $this->db->affected_rows() >= 0;
	}

	public function update_transferred_at($id, $transferred_at)
	{
		if ( ! $this->get($id))
		{
			return FALSE;
		}

		$this->db
			->where('id', (int) $id)
			->update('token_transfers', array(
				'transferred_at' => $transferred_at,
			));

		return $this->db->affected_rows() >= 0;
	}

	public function history($token_id)
	{
		return $this->list_filtered(array('token_id' => (int) $token_id));
	}

	public function list_filtered($filters = array())
	{
		$search    = isset($filters['search']) ? (string) $filters['search'] : '';
		$token_id  = isset($filters['token_id']) ? $filters['token_id'] : NULL;
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : NULL;
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : NULL;

		$this->db->select("
			tr.id,
			tr.token_id,
			tr.comment,
			tr.transferred_at,
			tr.from_employee_id,
			tr.to_employee_id,
			tm.name AS model_name,
			t.serial_number,
			fe.person_name AS from_fullname,
			fe.is_fired AS from_is_fired,
			te.person_name AS to_fullname,
			te.is_fired AS to_is_fired
		", FALSE);
		$this->db->from('token_transfers tr');
		$this->db->join('tokens t', 't.id = tr.token_id', 'inner');
		$this->db->join('token_models tm', 'tm.id = t.token_model_id', 'inner');
		$this->db->join('users fe', 'fe.id = tr.from_employee_id', 'left');
		$this->db->join('users te', 'te.id = tr.to_employee_id', 'left');

		$this->_apply_list_filters($search, $token_id, $date_from, $date_to);

		$this->db->order_by('tr.transferred_at', 'DESC');

		return $this->db->get()->result_array();
	}

	public function count_filtered($filters = array())
	{
		$search    = isset($filters['search']) ? (string) $filters['search'] : '';
		$token_id  = isset($filters['token_id']) ? $filters['token_id'] : NULL;
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : NULL;
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : NULL;

		$this->db->from('token_transfers tr');
		$this->db->join('tokens t', 't.id = tr.token_id', 'inner');
		$this->db->join('token_models tm', 'tm.id = t.token_model_id', 'inner');
		$this->db->join('users fe', 'fe.id = tr.from_employee_id', 'left');
		$this->db->join('users te', 'te.id = tr.to_employee_id', 'left');

		$this->_apply_list_filters($search, $token_id, $date_from, $date_to);

		return (int) $this->db->count_all_results();
	}

	private function _apply_list_filters($search, $token_id, $date_from, $date_to)
	{
		$this->db->where('t.deleted_at IS NULL', NULL, FALSE);

		if ($token_id !== NULL)
		{
			$this->db->where('tr.token_id', (int) $token_id);
		}

		if ($search !== '')
		{
			$lc = mb_strtolower($search, 'UTF-8');
			$like = $this->db->escape_like_str($lc);
			$conditions = array(
				"LOWER(tm.name) LIKE '%{$like}%'",
				"LOWER(t.serial_number) LIKE '%{$like}%'",
				"LOWER(tr.comment) LIKE '%{$like}%'",
				"LOWER(fe.person_name) LIKE '%{$like}%'",
				"LOWER(te.person_name) LIKE '%{$like}%'",
			);
			// «Склад» в интерфейсе — это NULL в from/to_employee_id, в БД слова нет.
			if ($lc !== '' && mb_strpos('склад', $lc) !== FALSE)
			{
				$conditions[] = 'tr.from_employee_id IS NULL';
				$conditions[] = 'tr.to_employee_id IS NULL';
			}
			$this->db->where('(' . implode(' OR ', $conditions) . ')', NULL, FALSE);
		}

		if ($date_from !== NULL)
		{
			$this->db->where('tr.transferred_at >=', $date_from);
		}

		if ($date_to !== NULL)
		{
			$this->db->where('tr.transferred_at <=', $date_to);
		}
	}
}
