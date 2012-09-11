<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shinjuku_model extends CI_Model {

	function __construct() {
		parent::__construct();
	}
 
	function insert_tag($tags, $recipe_id) {
		$sql = "INSERT IGNORE INTO tags_tmp4 (name, slug) VALUES (?, ?)";
	
		// ciclo per ciascuna tag
		foreach ($tags as $tag) {
			// inserisco la tag solo se non e' gia' presente
			$this->db->query($sql, array($tag, $this->main_library->str_to_slug($tag)));

			// se ho inserito la tag: inserisco la relazione tag / entries
			// altrimenti: carico i dati del tag da db
			if ($this->db->affected_rows() == 1) {
				$this->db->insert('recipes_tags_tmp4', array('recipe_id' => $recipe_id, 'tag_id' => $this->db->insert_id()));
			} else {
				$this->db->select('id');
				$tag_query = $this->db->get_where('tags_tmp4', array('slug' => $this->main_library->str_to_slug($tag)));
				$tag_data = $tag_query->result();

				// inserisco la nuova relazione per il post corrente
				$this->db->insert('recipes_tags_tmp4', array('recipe_id' => $recipe_id, 'tag_id' => $tag_data[0]->id));
			}
		}
	}
	
	function insert_recipe($recipe_data, $tags) {
		$sql = "INSERT IGNORE INTO recipes_tmp4 (title, abstract, image, url, source_id) VALUES (?, ?, ?, ?, ?)";

		// getting CI native methods
		$CI =& get_instance();
		
		// se l'inserimento non va a buon fine: preparo il messaggio 'errore nell'inserimento' per la view
		// altrimenti: preparo i dati e il messaggio della nuova lista inserita per la view
		$this->db->query($sql, array($recipe_data['title'], $recipe_data['abstract'], $recipe_data['image'], $recipe_data['url'], $recipe_data['source_id']));
		
		if ($this->db->affected_rows() == 1) {
			$recipe_id = $this->db->insert_id();

			if (count($tags) > 0) {
				
				// inserisco le tag e le relazioni con la lista
				$CI->Shinjuku_model->insert_tag($tags, $recipe_id);
			}
		}

		return $recipe_data;
	}
	
	function recipe_exists($where) {
		$this->db->where($where, NULL, FALSE);
		
		if ($this->db->count_all_results('recipes_tmp4') > 0)
			return true;
		else
			return false;
	}

	function select_search_autocomplete($term) {
		$this->db->select('term');
		$this->db->like('term', $term);
		$this->db->order_by("LENGTH(term)"); 
		$this->db->limit(6);
		
		$query = $this->db->get('searches');
		$result = $query->result();
		
		return $result;
	}

	function select_recipes($term, $start = 0) {
		$this->db->select('recipes.id as id, title, recipes.url as url, abstract, source_id, sources.name as source_name, sources.url as source_url');
		$this->db->join('sources', 'sources.id = recipes.source_id', 'left');
		$this->db->like('LOWER(title)', $term);
		$this->db->order_by("LENGTH(title)");
		$this->db->limit(15, $start);
		
		$query = $this->db->get('recipes');
		$result = $query->result();
		
		return $result;
	}

	function select_recipe($id) {
		$this->db->where('id', $id);
		
		$query = $this->db->get('recipes');
		$result = $query->result();
		
		return $result[0];
	}

	function select_tag_exact($tag) {
		$this->db->select('id');
		$this->db->where('slug', $tag);
		
		$query = $this->db->get('tags');
		$result = $query->result();
		
		return $result[0];
	}

	function select_tags_like($tag) {
		$this->db->select('id, slug');
		$this->db->like('slug', $tag);
		
		$query = $this->db->get('tags');
		$result = $query->result();
		
		return $result;
	}

	function select_recipes_by_source($source_id) {
		$this->db->select('id, url');
		$this->db->where('source_id', $source_id);
		
		$query = $this->db->get('recipes');
		$result = $query->result();
		
		return $result;
	}

	function insert_term($term) {
		$sql = "INSERT INTO searches(term) VALUES('".$term."') ON DUPLICATE KEY UPDATE hits = hits + 1";

		if (!$this->db->query($sql))
			return FALSE;
		else
			return TRUE;
	}

	function count_recipes($term, $start) {
		$this->db->select('id');
		$this->db->like('LOWER(title)', $term);
		$this->db->order_by("LENGTH(title)");
		$this->db->limit(16, $start);
		
		$query = $this->db->get('recipes');
		$result = $query->result();
		
		return $result;
	}

	function update_recipe($where, $recipe_data) {
		
		$this->db->where($where);
		
		if (!$this->db->update('recipes', $recipe_data))
			return -2;
		else
			return 1;
	}
	
	function update_recipes_tags($where, $recipes_tags_data) {
		
		$this->db->where($where);
		
		if (!$this->db->update('recipes', $recipes_tags_data))
			return -2;
		else
			return 1;
	}

	function delete_tag($id) {
		$this->db->where(array('id' => $id));
		
		if (!$this->db->delete('tags'))
			return false;
		else
			return true;
	}
}

?>