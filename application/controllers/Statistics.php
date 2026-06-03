<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('statistics_m');
	}

	public function index()
	{
		$data = array(
			'active_nav' => 'statistics',
		);
		$this->load->view('templates/header', $data);
		$this->load->view('statistics/index', $data);
		$this->load->view('templates/footer', $data);
	}

	public function without_token_list_json()
	{
		$search = trim((string) $this->input->get('q'));
		$rows   = $this->statistics_m->list_without_token($search);
		$this->json_ok(
			array('items' => $rows),
			array(
				'count' => count($rows),
				'total' => $this->statistics_m->count_without_token(),
			)
		);
	}

	public function multiple_tokens_list_json()
	{
		$search = trim((string) $this->input->get('q'));
		$rows   = $this->statistics_m->list_multiple_tokens($search);
		$this->json_ok(
			array('items' => $rows),
			array(
				'count' => count($rows),
				'total' => $this->statistics_m->count_multiple_tokens(),
			)
		);
	}

	public function stuck_tokens_list_json()
	{
		$search = trim((string) $this->input->get('q'));
		$rows   = $this->statistics_m->list_stuck_tokens($search);
		$this->json_ok(
			array('items' => $rows),
			array(
				'count' => count($rows),
				'total' => $this->statistics_m->count_stuck_tokens(),
			)
		);
	}
}
