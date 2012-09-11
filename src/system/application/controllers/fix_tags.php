<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fix_tags extends CI_Controller {

	function __construct() {
		parent::__construct();
	}
 
	function index($tag) {
		// get the exact match
		$exact = $this->Shinjuku_model->select_tag_exact($tag);
		
		// get the list of %like% matches
		$like = $this->Shinjuku_model->select_tags_like($tag);
		
		// foreach %like% match
		foreach($like as $tag_like) {
			// update recipes_tags relationship with exact match tag
			$this->Shinjuku_model->update_recipes_tags(array('tag_id' => $tag_like->id), array('tag_id' => $exact->id));
			
			// delete %like% match tag
			$this->Shinjuku_model->update_recipes_tags(array('tag_id' => $tag_like->id), array('tag_id' => $exact->id));
		}
		
		print_r($exact);
		print_r($like); exit;
	}	
}

?>