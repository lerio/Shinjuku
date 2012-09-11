<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->library('main_library');
	}
 
	function index() {
		$data['title'] = "Shinjuku";
		$data['result'] = FALSE;
		
		$this->load->view('home_view', $data);
	}
	
	function search() {

		// carico la libreria per la validazione del form
		$this->load->library('form_validation');

		// carico e applico le regole per i campi del form
		$this->form_validation->set_rules('term', 'Term', 'trim|required');

		// se ho passato la validazione del form: procedo con la verifica
		if ($this->form_validation->run() != FALSE) {
			$term = set_value('term');
			
			redirect('/'.$this->main_library->str_to_slug($term));
		} else
			redirect('/');
	}
}

?>