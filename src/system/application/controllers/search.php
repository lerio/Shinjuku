<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->library('main_library');
	}
 
	function index($term) {
		$term = strtolower(str_replace('-', ' ', $term));
		$data['query'] = ucwords($term);
		$data['title'] = $data['query']." | Shinjuku";
		$term = preg_replace('/recipes?/', '', $term);
		$data['term'] = str_replace(' ', '-', $term);
		$data['first_recipes'] = array();
		$recipes_id = array();
		$data['other_recipes'] = array();
		$sources = array(1, 2, 3, 4, 5);
		$data['recipes_count'] = 0;
		
		$recipes = $this->Shinjuku_model->select_recipes($term);
		
		if (count($recipes) >= 10)
			$this->Shinjuku_model->insert_term($term);
		
		foreach ($recipes as $recipe) {
			if (in_array($recipe->source_id, $sources)) {
				$data['first_recipes'][] = $recipe;
				$recipes_id[] = $recipe->id;
				unset($sources[$recipe->source_id - 1]);
				$data['recipes_count']++;
			}
			
			if (count($sources) == 0)
				break;
		}
		
		foreach ($recipes as $recipe) {
			if (in_array($recipe->id, $recipes_id))
				continue;
			else {
				$data['other_recipes'][] = $recipe;
				$data['recipes_count']++;
			}
		}
		
		$data['result'] = TRUE;
		
		$this->load->view('home_view', $data);
	}
}

?>