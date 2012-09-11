<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pagination extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->library('main_library');
	}
 
	function index() {
		// Nothing to do..
	}
	
	function get_recipes($term, $offset) {
		$term = str_replace('-', ' ', $term);
		$recipes = $this->Shinjuku_model->select_recipes($term, $offset);

		foreach ($recipes as $recipe)
			$data['other_recipes'][] = $recipe;

		$this->load->view('pagination_view', $data);
	}

	function get_recipes_count($term, $offset) {
		$term = str_replace('-', ' ', $term);
		$recipes_count = 0;
		$recipes = $this->Shinjuku_model->count_recipes($term, $offset);

		foreach ($recipes as $recipe)
			$recipes_count++;

		die((String)$recipes_count);
	}
}

?>