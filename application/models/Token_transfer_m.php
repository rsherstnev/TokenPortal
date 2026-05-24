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
		$now = date('Y-m-d H:i:s');

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
		$this->db->select("
			tr.id,
			tr.token_id,
			tr.comment,
			tr.transferred_at,
			tr.from_employee_id,
			tr.to_employee_id,
			TRIM(CONCAT_WS(' ', fe.lastname, fe.firstname, fe.patronymic)) AS from_fullname,
			TRIM(CONCAT_WS(' ', te.lastname, te.firstname, te.patronymic)) AS to_fullname
		", FALSE);
		$this->db->from('token_transfers tr');
		$this->db->join('employees fe', 'fe.id = tr.from_employee_id', 'left');
		$this->db->join('employees te', 'te.id = tr.to_employee_id', 'left');
		$this->db->where('tr.token_id', $token_id);
		$this->db->order_by('tr.transferred_at', 'DESC');

		return $this->db->get()->result_array();
	}
}
