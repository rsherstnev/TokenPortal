<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_transfer_m extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Транзакционная передача токена сотруднику.
	 * @return string|false UUID созданной передачи или false при ошибке.
	 */
	public function transfer($token_id, $to_employee_id, $comment = '')
	{
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

		$transfer_id = uuid_v4();
		$now = gmdate('Y-m-d H:i:s');

		$this->db->insert('token_transfers', array(
			'id'               => $transfer_id,
			'token_id'         => $token_id,
			'from_employee_id' => $from_employee_id,
			'to_employee_id'   => $to_employee_id,
			'comment'          => $comment !== '' ? $comment : NULL,
			'transferred_at'   => $now,
			'created_at'       => $now,
		));

		$this->db
			->where('id', $token_id)
			->update('tokens', array(
				'employee_id' => $to_employee_id,
				'updated_at'  => $now,
			));

		$this->db->trans_complete();

		return $this->db->trans_status() ? $transfer_id : FALSE;
	}

	public function history($token_id)
	{
		return $this->list_filtered(array('token_id' => $token_id));
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
			TRIM(CONCAT_WS(' ', fe.lastname, fe.firstname, fe.patronymic)) AS from_fullname,
			TRIM(CONCAT_WS(' ', te.lastname, te.firstname, te.patronymic)) AS to_fullname
		", FALSE);
		$this->db->from('token_transfers tr');
		$this->db->join('tokens t', 't.id = tr.token_id', 'inner');
		$this->db->join('token_models tm', 'tm.id = t.token_model_id', 'inner');
		$this->db->join('employees fe', 'fe.id = tr.from_employee_id', 'left');
		$this->db->join('employees te', 'te.id = tr.to_employee_id', 'left');

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
		$this->db->join('employees fe', 'fe.id = tr.from_employee_id', 'left');
		$this->db->join('employees te', 'te.id = tr.to_employee_id', 'left');

		$this->_apply_list_filters($search, $token_id, $date_from, $date_to);

		return (int) $this->db->count_all_results();
	}

	private function _apply_list_filters($search, $token_id, $date_from, $date_to)
	{
		$this->db->where('t.deleted_at IS NULL', NULL, FALSE);

		if ($token_id !== NULL)
		{
			$this->db->where('tr.token_id', $token_id);
		}

		if ($search !== '')
		{
			$lc = mb_strtolower($search, 'UTF-8');
			$like = $this->db->escape_like_str($lc);
			$conditions = array(
				"LOWER(tm.name) LIKE '%{$like}%'",
				"LOWER(t.serial_number) LIKE '%{$like}%'",
				"LOWER(tr.comment) LIKE '%{$like}%'",
				"LOWER(CONCAT_WS(' ', fe.lastname, fe.firstname, fe.patronymic)) LIKE '%{$like}%'",
				"LOWER(CONCAT_WS(' ', te.lastname, te.firstname, te.patronymic)) LIKE '%{$like}%'",
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
