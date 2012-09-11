<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Autocomplete extends CI_Controller {
 
	function __construct() {
		parent::__construct();
	}
 
	function index() {
		
		$this->load->view('home_view');
	}

	function suggestions() {

		// Search term from jQuery
		$term = $this->input->post('term');
		
		// Do mysql query or what ever
		$arr = $this->Shinjuku_model->select_search_autocomplete($term);
		$results = array();
		
		foreach ($arr as $item)
			$results[] = $item->term;

		// Return data
		echo json_encode($results);
	}
}