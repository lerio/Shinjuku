<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Main_library {
	function send_mail($from_mail, $from_name, $to_mail, $subject, $body) {

		// getting CI native methods
		$CI =& get_instance();

		$CI->load->library('email');
		$CI->email->from($from_mail, $from_name);
		$CI->email->to($to_mail);
		$CI->email->subject($subject);
		$CI->email->message($body);

		return ($CI->email->send());
	}
	
	function str_to_slug($str) {
		if ($str == "") return $str;
	
		// Strip special and accented characters
		$slug = strtolower(htmlentities($str));
	    $slug = preg_replace("/&(.)(uml);/", "$1e", $slug);
		$slug = preg_replace("/&(.)(grave|acute|cedil|circ|ring|tilde|uml);/", "$1", $slug);
	    $slug = preg_replace("/([^a-z0-9]+)/", "-", html_entity_decode($slug));
	    $slug = trim($slug, "-");
    	
		return $slug;
	}
	
	function is_url($url) {
		if( preg_match('/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}((:[0-9]{1,5})?\/.*)?$/i' ,$url))
			return true;
		else
			return false;
	}
	
	function get_excerpt($text, $length) {
		$excerpt = substr($text, 0, $length);
		$pos = strrpos($excerpt, " ");
		
		if ($pos > 0)
			$excerpt = substr($excerpt, 0, $pos);

		return $excerpt;
	}
	
	function ping_responds($url) {
		$parsed_url = parse_url($url);
		
		$shell_exec_out = shell_exec(escapeshellcmd('ping -c 1 -W 1 '.$parsed_url['host']));

		if (strpos($shell_exec_out, '1 received'))
			return true;
		else
			return false;
	}
	
	function url_responds($url) {
		$url_data = parse_url($url);
		
		if (!$url_data)
			return FALSE;

		$errno = "";
		$errstr = "";
		$fp = 0;

		$fp = fsockopen($url_data['host'], 80, $errno, $errstr, 30);

		if ($fp === 0)
			return FALSE;

		$path = '';

		if (isset($url_data['path']))
			$path .= $url_data['path'];

		if (isset($url_data['query']))
			$path .= '?'.$url_data['query'];

		$out = "GET $path HTTP/1.1\r\n";
		$out .= "Host: {$url_data['host']}\r\n";
		$out .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)\r\n";
		$out .= "Connection: Close\r\n\r\n";

		fwrite($fp, $out);
		$content = fgets($fp);
		$code = trim(substr($content, 9, 4)); //get http code
		fclose($fp);

		// if http code is 2xx or 3xx url should work
		return ($code[0] == 2 || $code[0] == 3) ? TRUE : FALSE;
	}
	
	function create_path($path, $dir) {
		$path_img[] = substr($dir, -1);
		$path_img[] = strrev(substr($dir, -2, 2));
		$tree_path = '';

		foreach ($path_img as $level_img) {
			$tree_path .= $level_img."/";

			if (!is_dir($path.$tree_path))
				@mkdir($path.$tree_path, 0775);
		}
		
		return $tree_path;
	}
	
	function get_thumb_path($thumb) {
		$exploded_thumb = explode('_', $thumb);
		$dir = $exploded_thumb[0];
		
		$path_img[] = substr($dir, -1);
		$path_img[] = strrev(substr($dir, -2, 2));
		$tree_path = '';

		foreach ($path_img as $level_img)
			$tree_path .= $level_img."/";
		
		return RESOURCES_PATH.'images/thumbs/'.$tree_path;
	}

	function get_image_path($image) {
		$exploded_image = explode('_', $image);
		$dir = $exploded_image[0];
		
		$path_img[] = substr($dir, -1);
		$path_img[] = strrev(substr($dir, -2, 2));
		$tree_path = '';

		foreach ($path_img as $level_img)
			$tree_path .= $level_img."/";
		
		return RESOURCES_PATH.'images/galleries/'.$tree_path.$dir.'/';
	}

	function get_thumb_url($thumb) {
		$exploded_thumb = explode('_', $thumb);
		$dir = $exploded_thumb[0];
		
		$path_img[] = substr($dir, -1);
		$path_img[] = strrev(substr($dir, -2, 2));
		$tree_path = '';

		foreach ($path_img as $level_img)
			$tree_path .= $level_img."/";
		
		return 'resources/images/thumbs/'.$tree_path;
	}

	function empty_cache() {
		$cache_dir = BASEPATH.'cache/';
		
		if (!$dh = @opendir($cache_dir))
			return;
		
		while (false !== ($obj = readdir($dh))) {
			if ($obj == '.' || $obj == '..')
				continue;
			
			@unlink($cache_dir.$obj);
		}
	}

	function clear_list_related_cache() {
		
		// getting CI native methods
		$CI =& get_instance();
		
		$CI->load->helper('cache_helper');
		
		$pages = array();
		
		// carico l'array con tutte le pagine da rimuovere dalla cache
		$pages[] = base_url();
		$pages[] = base_url().'/top/month';
		$pages[] = base_url().'/top/year';
		
		// carico tutte le categorie attive
		$active_categories = $CI->Listype_model->select_active_categories();
		
		foreach ($active_categories as $cat) {
			$pages[] = base_url().'/cat/'.$cat->slug;
			
			for ($i = 2; $i <= 5; $i++)
				$pages[] = base_url().'/cat/'.$cat->slug.'/'.$i;
		}
		
		// carico tutte i tag attivi
		$active_tags = $CI->Listype_model->select_tags_for_cloud();
		
		foreach ($active_tags as $tag) {
			$pages[] = str_replace('tag', '/tag', $tag[2]);
			
			for ($i = 2; $i <= 5; $i++)
				$pages[] = str_replace('tag', '/tag', $tag[2]).$i;
		}
		
		// carico tutti le liste attive
		$active_lists = $CI->Listype_model->select_lists(0);
		
		foreach ($active_lists as $list) {
			$pages[] = base_url().'/'.$list->slug;
			
			for ($i = 2; $i <= 5; $i++)
				$pages[] = base_url().'/'.$list->slug.'/'.$i;
		}
		
		// cancello le pagine elencate
		delete_cache($pages);
	}

	function clear_item_related_cache($list_id) {
		
		// getting CI native methods
		$CI =& get_instance();
		
		$CI->load->helper('cache_helper');
		
		$pages = array();
		
		// carico la pagina della lista e le sue paginazioni
		$list_detail = $CI->Listype_model->select_list($list_id);
		
		$pages[] = base_url().'/'.$list_detail->slug;
			
		for ($i = 2; $i <= 5; $i++)
			$pages[] = base_url().'/'.$list_detail->slug.'/'.$i;
		
		// controllo se la lista compare in HP o nelle pagine top
		$lists_HP = $CI->Listype_model->select_lists();
		$is_HP = false;
		$featured = $CI->Listype_model->select_featured_list();
		
		if ($featured['id'] == $list_id)
			$is_HP = true;
		else {
			foreach ($lists_HP as $list_HP) {
				if ($list_HP->id == $list_id) {
					$is_HP = true;
					break;
				}
			}
		}
		
		if ($is_HP) {

			// carico l'array con tutte le pagine da rimuovere dalla cache
			$pages[] = base_url();
			$pages[] = base_url().'/top/month';
			$pages[] = base_url().'/top/year';
		}
		
		// carico la pagina della categoria dell'item e le sue paginazioni
		$category_detail = $CI->Listype_model->select_category($list_detail->category_id);
		
		$pages[] = base_url().'/cat/'.$category_detail->slug;
		
		for ($i = 2; $i <= 5; $i++)
			$pages[] = base_url().'/cat/'.$category_detail->slug.'/'.$i;
		
		// carico le pagine di tutti i tag associati e le loro paginazioni
		$list_tags = $CI->Listype_model->select_list_tags($list_id);
		
		foreach ($list_tags as $tag) {
			$pages[] = base_url().'/tag/'.$tag->slug;
			
			for ($i = 2; $i <= 5; $i++)
				$pages[] = base_url().'/tag/'.$tag->slug.'/'.$i;
		}
		
		// cancello le pagine elencate
		delete_cache($pages);
	}

	function rebuild_sitemap() {
		
		// getting CI native methods
		$CI =& get_instance();
		
		// carico la libreria sitemaps
		$CI->load->library('sitemaps');
		
		// carico gli array delle categorie e tag attivi, insieme a quello delle liste pubblicate
		$categories = $CI->Listype_model->select_active_categories();
		$tags = $CI->Listype_model->select_active_tags();
		$lists = $CI->Listype_model->select_lists($limit = 0);

		// costruisco l'albero della sitemap con tutte le pagine
		$item = array(
			"loc" => site_url(),
			"lastmod" => date("c", time()),
			"changefreq" => "hourly",
			"priority" => "0.8"
		);

		$CI->sitemaps->add_item($item);

		$item = array(
			"loc" => site_url("top_year"),
			"lastmod" => date("c", time()),
			"changefreq" => "hourly",
			"priority" => "0.8"
		);

		$CI->sitemaps->add_item($item);

		$item = array(
			"loc" => site_url("top_month"),
			"lastmod" => date("c", time()),
			"changefreq" => "hourly",
			"priority" => "0.8"
		);

		$CI->sitemaps->add_item($item);
		
		$item = array(
			"loc" => site_url("about_listype"),
			"changefreq" => "hourly",
			"priority" => "0.8"
		);

		$CI->sitemaps->add_item($item);

		$item = array(
			"loc" => site_url("about_us"),
			"changefreq" => "hourly",
			"priority" => "0.8"
		);

		$CI->sitemaps->add_item($item);

		foreach ($categories as $category) {
			$item = array(
				"loc" => site_url("cat/" . $category->slug),
				"lastmod" => date("c", strtotime($category->last_modified)),
				"changefreq" => "hourly",
				"priority" => "0.8"
			);

			$CI->sitemaps->add_item($item);
		}

		foreach ($tags as $tag) {
			$item = array(
				"loc" => site_url("tag/" . $tag->slug),
				"lastmod" => date("c", strtotime($tag->last_modified)),
				"changefreq" => "hourly",
				"priority" => "0.8"
			);

			$CI->sitemaps->add_item($item);
		}

		foreach ($lists as $list) {
			$item = array(
				"loc" => site_url($list->slug),
				"lastmod" => date("c", strtotime($list->insert_date)),
				"changefreq" => "hourly",
				"priority" => "0.8"
			);

			$CI->sitemaps->add_item($item);
		}

		// file name may change due to compression
		$file_name = $CI->sitemaps->build("sitemap.xml");

		// invio la sitemap ai principali motori di ricerca
		// $reponses = $CI->sitemaps->ping(site_url($file_name));
	}
}

?>