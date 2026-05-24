<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_m extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Базовый SELECT с join-ами и составным именем сотрудника.
	 */
	private function base_select()
	{
		$this->db->select("
			t.id,
			t.token_model_id,
			t.serial_number,
			t.employee_id,
			t.is_broken,
			t.is_lost,
			t.created_at,
			t.updated_at,
			tm.name AS model_name,
			TRIM(CONCAT_WS(' ', e.lastname, e.firstname, e.patronymic)) AS employee_fullname,
			(SELECT MAX(tr.transferred_at)
			 FROM token_transfers tr
			 WHERE tr.token_id = t.id AND tr.to_employee_id IS NOT NULL) AS last_issued_at
		", FALSE);
		$this->db->from('tokens t');
		$this->db->join('token_models tm', 'tm.id = t.token_model_id', 'left');
		$this->db->join('employees e', 'e.id = t.employee_id', 'left');
		$this->db->where('t.deleted_at IS NULL', NULL, FALSE);
	}

	/**
	 * @param string $search строка поиска по сотруднику/модели/серийнику/статусу
	 * @param string $status all|issued|not_issued|broken|lost
	 */
	public function list_filtered($search = '', $status = 'not_issued')
	{
		$this->base_select();

		switch ($status)
		{
			case 'issued':
				$this->db->where('t.employee_id IS NOT NULL', NULL, FALSE);
				$this->db->where('t.is_broken', 0);
				$this->db->where('t.is_lost', 0);
				break;
			case 'not_issued':
				$this->db->where('t.employee_id IS NULL', NULL, FALSE);
				$this->db->where('t.is_broken', 0);
				$this->db->where('t.is_lost', 0);
				break;
			case 'broken':
				$this->db->where('t.is_broken', 1);
				break;
			case 'lost':
				$this->db->where('t.is_lost', 1);
				break;
			case 'all':
			default:
				break;
		}

		if ($search !== '')
		{
			$lc = mb_strtolower($search, 'UTF-8');
			$conditions = array(
				"LOWER(t.serial_number) LIKE '%".$this->db->escape_like_str($lc)."%'",
				"LOWER(tm.name) LIKE '%".$this->db->escape_like_str($lc)."%'",
				"LOWER(TRIM(CONCAT_WS(' ', e.lastname, e.firstname, e.patronymic))) LIKE '%".$this->db->escape_like_str($lc)."%'",
			);

			$this->db->where('('.implode(' OR ', $conditions).')', NULL, FALSE);
		}

		$this->db->order_by('t.created_at', 'DESC');

		$rows = $this->db->get()->result_array();
		foreach ($rows as &$row)
		{
			$row['status'] = $this->compute_status($row);
		}
		return $rows;
	}

	public function count_all($status = 'not_issued')
	{
		$this->db->from('tokens t');
		$this->db->where('t.deleted_at IS NULL', NULL, FALSE);

		switch ($status)
		{
			case 'issued':
				$this->db->where('t.employee_id IS NOT NULL', NULL, FALSE);
				$this->db->where('t.is_broken', 0);
				$this->db->where('t.is_lost', 0);
				break;
			case 'not_issued':
				$this->db->where('t.employee_id IS NULL', NULL, FALSE);
				$this->db->where('t.is_broken', 0);
				$this->db->where('t.is_lost', 0);
				break;
			case 'broken':
				$this->db->where('t.is_broken', 1);
				break;
			case 'lost':
				$this->db->where('t.is_lost', 1);
				break;
			case 'all':
			default:
				break;
		}

		return (int) $this->db->count_all_results();
	}

	public function get($id)
	{
		$this->base_select();
		$this->db->where('t.id', $id);
		$row = $this->db->get()->row_array();
		if ($row)
		{
			$row['status'] = $this->compute_status($row);
		}
		return $row;
	}

	public function compute_status($row)
	{
		$statuses = array();

		if ((int) $row['is_lost'] === 1)
		{
			$statuses[] = array('code' => 'lost', 'label' => 'Утерян');
		}
		if ((int) $row['is_broken'] === 1)
		{
			$statuses[] = array('code' => 'broken', 'label' => 'Сломан');
		}

		if (empty($statuses))
		{
			if ( ! empty($row['employee_id']))
			{
				$statuses[] = array('code' => 'issued', 'label' => 'Выдан');
			}
			else
			{
				$statuses[] = array('code' => 'not_issued', 'label' => 'Не выдан');
			}
		}

		return $statuses;
	}

	public function create($data)
	{
		$id = uuid_v4();
		$this->db->insert('tokens', array(
			'id'             => $id,
			'token_model_id' => $data['token_model_id'],
			'serial_number'  => $data['serial_number'],
			'is_broken'      => ! empty($data['is_broken']) ? 1 : 0,
			'is_lost'        => ! empty($data['is_lost']) ? 1 : 0,
			'employee_id'    => NULL,
			'created_at'     => gmdate('Y-m-d H:i:s'),
			'updated_at'     => gmdate('Y-m-d H:i:s'),
		));
		return $id;
	}

	public function update($id, $data)
	{
		return $this->db
			->where('id', $id)
			->update('tokens', array(
				'token_model_id' => $data['token_model_id'],
				'serial_number'  => $data['serial_number'],
				'is_broken'      => ! empty($data['is_broken']) ? 1 : 0,
				'is_lost'        => ! empty($data['is_lost']) ? 1 : 0,
				'updated_at'     => gmdate('Y-m-d H:i:s'),
			));
	}

	public function soft_delete($id)
	{
		return $this->db
			->where('id', $id)
			->update('tokens', array(
				'deleted_at' => gmdate('Y-m-d H:i:s'),
				'updated_at' => gmdate('Y-m-d H:i:s'),
			));
	}

	/**
	 * Проверяет, существует ли активный токен с такой же моделью и серийным номером.
	 * @param string      $model_id   UUID модели токена
	 * @param string      $serial     серийный номер
	 * @param string|null $exclude_id UUID токена, который нужно исключить (для случая обновления)
	 * @return bool
	 */
	public function exists_by_model_and_serial($model_id, $serial, $exclude_id = NULL)
	{
		$this->db->from('tokens')
			->where('deleted_at IS NULL', NULL, FALSE)
			->where('token_model_id', $model_id)
			->where('serial_number', $serial);

		if ($exclude_id !== NULL)
		{
			$this->db->where('id !=', $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	public function count_all_active()
	{
		return (int) $this->db
			->where('deleted_at IS NULL', NULL, FALSE)
			->from('tokens')
			->count_all_results();
	}
}
