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
			'page_title' => 'Статистика',
			'active_nav' => 'statistics',
		);
		$this->load->view('templates/header', $data);
		$this->load->view('statistics/index', $data);
		$this->load->view('templates/footer', $data);
	}

	public function summary_json()
	{
		$search = trim((string) $this->input->get('q'));
		$data   = $this->statistics_m->summary($search);
		$this->json_ok($data, array(
			'count' => count($data['without_token']),
			'total' => $data['totals']['without_token'],
		));
	}
}
