<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fetch extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->library('main_library');
		$this->load->helper('simple_html_dom');
	}
 
	function index() {
		// do nothing
	}

	function epicurious() {
		set_time_limit(0);
		
		$source_id = 1;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;

		$epicurious_url = 'http://www.epicurious.com';
		$epicurious_cat_url = $epicurious_url.'/tools/browseresults?pageSize=1000&att=';
		$cat_list = array(45, 53, 81, 82, 83, 84, 85, 86, 87, 89, 90, 91, 92, 93, 95, 98, 99, 100, 102, 103, 105, 106, 107, 108, 109, 111, 113, 114, 115, 169, 521);
		$skipped = $valid = 0;
		
		foreach ($cat_list as $cat) {
			$html_src = @file_get_contents($epicurious_cat_url.$cat);
			
			if (!$html_src)
				continue;
			
			$html_obj = str_get_html($html_src);
			$recipe_list_obj = $html_obj->find('div[id=resultstable] td[class=name] a');
			$recipes = array();

			foreach ($recipe_list_obj as $recipe) {
				$recipe_url = $recipe->href;
				$recipe_title = html_entity_decode($recipe->innertext, ENT_COMPAT, 'UTF-8');

				if (!preg_match('/^\/recipes\//', $recipe_url) || $this->Shinjuku_model->recipe_exists("url='".$recipe_url."' OR (LOWER(title)='".strtolower(addslashes($recipe_title))."' AND source_id=".$source_id.")")) {
					$skipped++;
					
					continue;
				}
				
				$recipe_html_src = @file_get_contents($epicurious_url.$recipe_url);
				
				if (!$recipe_html_src)
					continue;
				
				$recipe_html_obj = str_get_html($recipe_html_src);
				$recipe_obj = $recipe_html_obj->find('li[class=ingredient]');
				$ingredients = array();

				foreach ($recipe_obj as $ingredient) {
					$tmp_ingredient = strtolower(html_entity_decode(strip_tags($ingredient->innertext) , ENT_COMPAT, 'UTF-8'));
					
					if (preg_match('/(^a\s|^an\s|^\*|ingredient info|accompaniment|equipment|additional|even easier|toppings|garnish|preparation)/', $tmp_ingredient))
						continue;
					
					$tmp_ingredient = trim(preg_replace('/(\s*\([^)]*\)|\*|\d+|\/|;|%|\.)/', '', $tmp_ingredient));

					$tmp_ingredient = preg_split('/(\ssuch\sas\s|\ssuch\s|\sare\s|\seach\s|\swith\s|\sin\s|\sat\s|,|\sfrom\s)/', $tmp_ingredient);
					$tmp_ingredient = $tmp_ingredient[0];
					$tmp_ingredient = preg_split('/(\sor\s)/', $tmp_ingredient);
					$tmp_ingredient = $tmp_ingredient[count($tmp_ingredient) - 1];
					$tmp_ingredient = trim(preg_replace('/(^heaping\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^kg\s|\s?kg\s|^lb\s|\s?lb\s|^ml\s|\s?ml\s|^oz\s|\s?oz\s|^pt\s|\s?pt\s|^tb?sp\s|\stb?sp\s|^bunch\s|\sbunch\s|^half-pints?\s|\shalf-pints?\s|^pints?\s|\spints?\s|^table?spoons?\s?|\stable?spoons?\s?|^teaspoons?\s|\steaspoons?\s|^-?pounds?\s|\s-?pounds?\s|^pinches\sof\s|\spinches\sof\s|^pinch\sof\s|\spinch\sof\s|^cups?\s|\scups?\s|^-?ounces?\s|\s-?ounces?\s|^-?inch\s|\s?-?inch\s|^-?inches\s|\s-?inches\s|^-cacao\s|\s?-cacao\s|^-?inches-diameter|\s?-?inches-diameter|^-?inch-diameter|\s?-?inch-diameter|^-?inches-thick|\s?-?inches-thick|^-?inch-thick|\s?-?inch-thick|^-?inch-long|\s?-?inch-long|^-?inch-high|\s?-?inch-high|^-?inch-wide|\s?-?inch-wide)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^jars?\s|\sjar?\s|^box\s|\sbox\s|^boxes\s|\sboxes\s|^bottles?\s|\sbottles?\s|^baskets?\s|\sbaskets?\s|^bars?\s|\sbars?\s|^about\s|\s?about\s|^other\s|\sother\s|^cubes?\s|\scubes?\s|^plus?\s|\splus?\s|^of\s|\sof\s|^by\s|\sby\s|^to?\s|\sto?\s|^made?\s|\smade?\s|\smade$|^if\sdesired\s|\sif\sdesired\s|\sif\sdesired$|^and\s|\sand\s|\sand$|^pieces?\s|\spieces?\s|\spieces$|^slices?\s|\sslices?\s|\sslices$|^leaves?\s|\sleaves?\s|\sleaves$|^sticks?\s|\ssticks?\s|\ssticks?$|^cans?\s|\scans?\s|^bags?\s|\sbags?\s|^packages?\s|\spackages?\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^lightly\s|\slightly\s|^firmly\s|\sfirmly\s|^fully\s|\sfully\s|freshly\s|\sfreshly\s|^finely\s|\sfinely\s|^thinly\s|\sthinly\s|^coarsely?\s|\scoarsely?\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^sifted\s|\ssifted\s|^melted\s|\smelted\s|^boiling\s|\sboiling\s|^boiling-hot\s|\sboiling-hot\s|^hot\s|\shot\s|^packed\s|\spacked\s|^crushed\s|\scrushed\s|^purchased\s|\spurchased\s|^cooked\s|\scooked\s|^pitted\s|\spitted\s|halved\s|\shalved\s|bottled\s|\sbottled\s|canned\s|\scanned\s|^peeled\s|\speeled\s|^squeezed\s|\ssqueezed\s|^bulbchopped\s|\sbulbchopped\s|^crumbled\s|\scrumbled\s|^unpeeled\s|\sunpeeled\s|^sliced\s|\ssliced\s|^torn\s|\storn\s|^g?rated\s|\sg?rated\s|^diced?\s|\sdiced?\s|^drained?\s|\sdrained?\s|^minced?\s|\sminced?\s|^well-chilled\s|\swell-chilled\s|^chilled\s|\schilled\s|^roasted\s|\sroasted\s|^brewed\s|\sbrewed\s|^chopped\s|\schopped\s)/', ' ', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^-)/', '', $tmp_ingredient));
					$tmp_ingredient = trim($tmp_ingredient);
					
					if ($tmp_ingredient != '')
						$ingredients[] = trim($tmp_ingredient);
				}
				
				// print_r($ingredients); exit;

				$recipe_data['title'] = $recipe_title;
				$recipe_data['abstract'] = $recipe_title;
				$recipe_data['image'] = $recipe_url;
				$recipe_data['url'] = $recipe_url;
				$recipe_data['source_id'] = $source_id;
				
				$this->Shinjuku_model->insert_recipe($recipe_data, $ingredients);

				$valid++;

				$recipe_html_obj->clear();  
				unset($recipe_html_obj);
			}
			
			$html_obj->clear();  
			unset($html_obj);
		}
		
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime), 0);
		$mins = floor ($totaltime / 60);
		$secs = $totaltime % 60;
		
		echo $valid." new recipe(s) added<br />";
		echo $skipped." recipe(s) skipped<br /><br />";
		die("This page was created in ".$mins." minute(s) and ".$secs." second(s)");
	}

	function epicurious_abstract() {
		set_time_limit(0);
		
		$source_id = 1;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;

		$epicurious_recipes = $this->Shinjuku_model->select_recipes_by_source($source_id);
		
		foreach ($epicurious_recipes as $recipe) {
			if ($recipe->id < 2751)
				continue;
			
			$html_src = @file_get_contents($recipe->url);
			
			if (!$html_src)
				continue;
			
			$html_obj = str_get_html($html_src);
			$abstract_obj = $html_obj->find('span[id=truncatedText]');
			$abstract = strip_tags($abstract_obj[0]);

			$where = array('id' => $recipe->id);
			$recipe_data = array('abstract' => $abstract);
			$this->Shinjuku_model->update_recipe($where, $recipe_data);

			$html_obj->clear();  
			unset($html_obj);
		}
		
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime), 0);
		$mins = floor ($totaltime / 60);
		$secs = $totaltime % 60;
		
		die("This page was created in ".$mins." minute(s) and ".$secs." second(s)");
	}

	function myrecipes() {
		set_time_limit(0);
		
		$source_id = 2;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;

		$myrecipes_url = 'http://search.myrecipes.com/search.html?Ntk=photo_sort&N=';
		$cat_list = array('17%204294967207', '17%204294966444', '17+4294966856', '17+4294966605', '17+4294967117', '17+4294966591', '17+4294967133', '17+4294967246', '17+4294966284', '17+4294966994', '17+4294967086', '17+4294967034', '17+4294967101', '17+2543', '17+4294967064', '17+2544', '17+4294966964', '17+4294967219');
		$skipped = $valid = 0;
		
		foreach ($cat_list as $cat) {
			$page = 0;
			$url_valid = TRUE;
			
			while ($url_valid && $page <= 1000) {
				$myrecipes_cat_url = $myrecipes_url.$cat.'&No='.$page;
				
				$html_src = @file_get_contents($myrecipes_cat_url);
				
				if (!$html_src) {
					$page = $page++;
					
					continue;
				}
				
				$html_obj = str_get_html($html_src);
				$recipe_list_obj = $html_obj->find('div[class=tout] div[class=txt] a');
				$recipes = array();
				
				if (count($recipe_list_obj) > 0) {
					foreach ($recipe_list_obj as $recipe) {
						$recipe_url = $recipe->href;
						$recipe_title = html_entity_decode($recipe->innertext, ENT_COMPAT, 'UTF-8');

						if (!preg_match('/^http:\/\/find\.myrecipes\.com\//', $recipe_url) || $this->Shinjuku_model->recipe_exists("url='".$recipe_url."' OR (LOWER(title)='".strtolower(addslashes($recipe_title))."' AND source_id=".$source_id.")")) {
							$skipped++;

							continue;
						}

						$recipe_html_src = @file_get_contents($recipe_url);

						if (!$recipe_html_src)
							continue;

						$recipe_html_obj = str_get_html($recipe_html_src);
						$recipe_obj = $recipe_html_obj->find('div[id=ingredients] li');
						$ingredients = array();

						foreach ($recipe_obj as $ingredient) {
							$tmp_ingredient = ereg_replace("/\n\r|\r\n|\n|\r/|\t|&#160;", " ", $ingredient->innertext);
							$tmp_ingredient = strtolower(html_entity_decode(strip_tags($tmp_ingredient) , ENT_COMPAT, 'UTF-8'));

							if (preg_match('/(^a\s|^an\s|^\*|ingredient info|accompaniment|equipment|additional|even easier|toppings|garnish|preparation)/', $tmp_ingredient))
								continue;

							$tmp_ingredient = trim(preg_replace('/(\s*\([^)]*\)|\*|\d+|\/|;|%|\.)/', '', $tmp_ingredient));
							$tmp_ingredient = preg_split('/(\ssuch\sas\s|\ssuch\s|\sare\s|\seach\s|\swith\s|\sin\s|\sat\s|,|\sfrom\s)/', $tmp_ingredient);
							$tmp_ingredient = $tmp_ingredient[0];
							$tmp_ingredient = preg_split('/(\sor\s)/', $tmp_ingredient);
							$tmp_ingredient = trim($tmp_ingredient[count($tmp_ingredient) - 1]);
							$tmp_ingredient = trim(preg_replace('/(^heaping\s)/', '', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^kg\s|\s?kg\s|^lb\s|\s?lb\s|^ml\s|\s?ml\s|^oz\s|\s?oz\s|^pt\s|\s?pt\s|^tb?sp\s|\stb?sp\s|^bunch\s|\sbunch\s|^half-pints?\s|\shalf-pints?\s|^pints?\s|\spints?\s|^table?spoons?\s?|\stable?spoons?\s?|^teaspoons?\s|\steaspoons?\s|^-?pounds?\s|\s?-?pounds?\s|^pinches\sof\s|\spinches\sof\s|^pinch\sof\s|\spinch\sof\s|^cups?\s|\scups?\s|^-?ounces?\s|\s-?ounces?\s|^-?inch\s|\s?-?inch\s|^-?inches\s|\s-?inches\s|^-cacao\s|\s?-cacao\s|^-?inches-diameter|\s?-?inches-diameter|^-?inch-diameter|\s?-?inch-diameter|^-?inches-thick|\s?-?inches-thick|^-?inch-thick|\s?-?inch-thick|^-?inch-long|\s?-?inch-long|^-?inch-high|\s?-?inch-high|^-?inch-wide|\s?-?inch-wide)/', '', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^jars?\s|\sjar?\s|^box\s|\sbox\s|^boxes\s|\sboxes\s|^bottles?\s|\sbottles?\s|^baskets?\s|\sbaskets?\s|^bars?\s|\sbars?\s|^about\s|\s?about\s|^other\s|\sother\s|^cubes?\s|\scubes?\s|^plus?\s|\splus?\s|^of\s|\sof\s|^by\s|\sby\s|^to?\s|\sto?\s|^made?\s|\smade?\s|\smade$|^if\sdesired\s|\sif\sdesired\s|\sif\sdesired$|^and\s|\sand\s|\sand$|^pieces?\s|\spieces?\s|\spieces$|^slices?\s|\sslices?\s|\sslices$|^leaves?\s|\sleaves?\s|\sleaves$|^sticks?\s|\ssticks?\s|\ssticks?$|^cans?\s|\scans?\s|^bags?\s|\sbags?\s|^packages?\s|\spackages?\s)/', '', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^lightly\s|\slightly\s|^firmly\s|\sfirmly\s|^fully\s|\sfully\s|freshly\s|\sfreshly\s|^finely\s|\sfinely\s|^thinly\s|\sthinly\s|^coarsely?\s|\scoarsely?\s)/', '', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^sifted\s|\ssifted\s|^melted\s|\smelted\s|^boiling\s|\sboiling\s|^boiling-hot\s|\sboiling-hot\s|^hot\s|\shot\s|^packed\s|\spacked\s|^crushed\s|\scrushed\s|^purchased\s|\spurchased\s|^cooked\s|\scooked\s|^pitted\s|\spitted\s|halved\s|\shalved\s|bottled\s|\sbottled\s|canned\s|\scanned\s|^peeled\s|\speeled\s|^squeezed\s|\ssqueezed\s|^bulbchopped\s|\sbulbchopped\s|^crumbled\s|\scrumbled\s|^unpeeled\s|\sunpeeled\s|^sliced\s|\ssliced\s|^torn\s|\storn\s|^g?rated\s|\sg?rated\s|^diced?\s|\sdiced?\s|^drained?\s|\sdrained?\s|^minced?\s|\sminced?\s|^well-chilled\s|\swell-chilled\s|^chilled\s|\schilled\s|^roasted\s|\sroasted\s|^brewed\s|\sbrewed\s|^chopped\s|\schopped\s)/', ' ', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^-)/', '', $tmp_ingredient));
							$tmp_ingredient = trim($tmp_ingredient);

							if ($tmp_ingredient != '')
								$ingredients[] = trim($tmp_ingredient);
						}

						$recipe_data['title'] = $recipe_title;
						$recipe_data['abstract'] = $recipe_title;
						$recipe_data['image'] = $recipe_url;
						$recipe_data['url'] = $recipe_url;
						$recipe_data['source_id'] = $source_id;

						$this->Shinjuku_model->insert_recipe($recipe_data, $ingredients);

						$valid++;

						$recipe_html_obj->clear();  
						unset($recipe_html_obj);
					}
				} else
					$url_valid = FALSE;

				$page = $page + 15;
				
				$html_obj->clear();  
				unset($html_obj);
			}
		}
		
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime), 0);
		$mins = floor ($totaltime / 60);
		$secs = $totaltime % 60;
		
		echo $valid." new recipe(s) added<br />";
		echo $skipped." recipe(s) skipped<br /><br />";
		die("This page was created in ".$mins." minute(s) and ".$secs." second(s)");
	}

	function myrecipes_abstract() {
		set_time_limit(0);
		
		$source_id = 2;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;

		$myrecipes_recipes = $this->Shinjuku_model->select_recipes_by_source($source_id);
		
		foreach ($myrecipes_recipes as $recipe) {
			$html_src = @file_get_contents($recipe->url);

			if (!$html_src)
				continue;

			$html_obj = str_get_html($html_src);
			$abstract_obj = $html_obj->find('meta[name=description]');
			$abstract = $abstract_obj[0];

			$where = array('id' => $recipe->id);
			$recipe_data = array('abstract' => $abstract->content);
			$this->Shinjuku_model->update_recipe($where, $recipe_data);

			$html_obj->clear();  
			unset($html_obj);
		}
		
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime), 0);
		$mins = floor ($totaltime / 60);
		$secs = $totaltime % 60;
		
		die("This page was created in ".$mins." minute(s) and ".$secs." second(s)");
	}

	function foodnetwork() {
		set_time_limit(0);
		
		$source_id = 3;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;

		$foodnetwork_home = 'http://www.foodnetwork.com';
		$foodnetwork_url = $foodnetwork_home.'/food/about_us/index/0,1000854,FOOD_32959_93219_CAT-NUM,00.html';
		$cat_list = array('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'XYZ');
		$skipped = $valid = 0;
		
		foreach ($cat_list as $cat) {
			$page = 1;
			$url_valid = TRUE;
			
			while ($url_valid) {
				$foodnetwork_cat_url = preg_replace(array('/CAT/', '/NUM/'), array($cat, $page), $foodnetwork_url);

				$html_src = @file_get_contents($foodnetwork_cat_url);

				if (!$html_src) {
					$page = $page++;
					
					continue;
				}

				$html_obj = str_get_html($html_src);
				
				$recipe_pag_obj = $html_obj->find('p[class=subhd]');
				$recipe_pag = $recipe_pag_obj[0]->innertext;
				$recipe_pos_1 = strpos($recipe_pag, '-') + 1;
				$recipe_pos_2 = strpos($recipe_pag, ' Recipes');
				$recipe_pag_ext = substr($recipe_pag, $recipe_pos_1, $recipe_pos_2 - $recipe_pos_1);
				$recipe_exp = explode(' of ', $recipe_pag_ext);

				if ((int)$recipe_exp[0] > 1000 || (int)$recipe_exp[0] == (int)$recipe_exp[1])
					$url_valid = FALSE;
				
				$recipe_list_obj = $html_obj->find('ul[class=idxlist] li a');
				$recipes = array();
				
				if (count($recipe_list_obj) > 0) {
					foreach ($recipe_list_obj as $recipe) {
						$recipe_url = $recipe->href;
						$recipe_title = html_entity_decode($recipe->innertext, ENT_COMPAT, 'UTF-8');

						if (!preg_match('/^\/recipes\//', $recipe_url) || $this->Shinjuku_model->recipe_exists("url='".$recipe_url."' OR (LOWER(title)='".strtolower(addslashes($recipe_title))."' AND source_id=".$source_id.")")) {
							$skipped++;

							continue;
						}

						$recipe_html_src = @file_get_contents($foodnetwork_home.$recipe_url);

						if (!$recipe_html_src)
							continue;

						$recipe_html_obj = str_get_html($recipe_html_src);
						$recipe_meta_obj = $recipe_html_obj->find('meta[name=description]');
						
						$recipe_data['abstract'] = $recipe_meta_obj[0]->content;
						
						$recipe_obj = $recipe_html_obj->find('li[class=ingredient]');
						$ingredients = array();

						foreach ($recipe_obj as $ingredient) {
							$tmp_ingredient = ereg_replace("/\n\r|\r\n|\n|\r/|\t|&#160;", " ", $ingredient->innertext);
							$tmp_ingredient = strtolower(html_entity_decode(strip_tags($tmp_ingredient) , ENT_COMPAT, 'UTF-8'));

							if (preg_match('/(^a\s|^an\s|^\*|ingredient info|accompaniment|equipment|additional|even easier|toppings|garnish|preparation)/', $tmp_ingredient))
								continue;

							$tmp_ingredient = trim(preg_replace('/(\s*\([^)]*\)|\*|\d+|\/|;|%|\.)/', '', $tmp_ingredient));
							$tmp_ingredient = preg_split('/(\ssuch\sas\s|\ssuch\s|\sare\s|\seach\s|\swith\s|\sin\s|\sat\s|,|\sfrom\s)/', $tmp_ingredient);
							$tmp_ingredient = $tmp_ingredient[0];
							$tmp_ingredient = preg_split('/(\sor\s)/', $tmp_ingredient);
							$tmp_ingredient = trim($tmp_ingredient[count($tmp_ingredient) - 1]);
							$tmp_ingredient = trim(preg_replace('/(^heaping\s)/', '', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^kg\s|\s?kg\s|^lb\s|\s?lb\s|^ml\s|\s?ml\s|^oz\s|\s?oz\s|^pt\s|\s?pt\s|^tb?sp\s|\stb?sp\s|^bunch\s|\sbunch\s|^half-pints?\s|\shalf-pints?\s|^pints?\s|\spints?\s|^table?spoons?\s?|\stable?spoons?\s?|^teaspoons?\s|\steaspoons?\s|^-?pounds?\s|\s?-?pounds?\s|^pinches\sof\s|\spinches\sof\s|^pinch\sof\s|\spinch\sof\s|^cups?\s|\scups?\s|^-?ounces?\s|\s-?ounces?\s|^-?inch\s|\s?-?inch\s|^-?inches\s|\s-?inches\s|^-cacao\s|\s?-cacao\s|^-?inches-diameter|\s?-?inches-diameter|^-?inch-diameter|\s?-?inch-diameter|^-?inches-thick|\s?-?inches-thick|^-?inch-thick|\s?-?inch-thick|^-?inch-long|\s?-?inch-long|^-?inch-high|\s?-?inch-high|^-?inch-wide|\s?-?inch-wide)/', '', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^jars?\s|\sjar?\s|^box\s|\sbox\s|^boxes\s|\sboxes\s|^bottles?\s|\sbottles?\s|^baskets?\s|\sbaskets?\s|^bars?\s|\sbars?\s|^about\s|\s?about\s|^other\s|\sother\s|^cubes?\s|\scubes?\s|^plus?\s|\splus?\s|^of\s|\sof\s|^by\s|\sby\s|^to?\s|\sto?\s|^made?\s|\smade?\s|\smade$|^if\sdesired\s|\sif\sdesired\s|\sif\sdesired$|^and\s|\sand\s|\sand$|^pieces?\s|\spieces?\s|\spieces$|^slices?\s|\sslices?\s|\sslices$|^leaves?\s|\sleaves?\s|\sleaves$|^sticks?\s|\ssticks?\s|\ssticks?$|^cans?\s|\scans?\s|^bags?\s|\sbags?\s|^packages?\s|\spackages?\s)/', '', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^lightly\s|\slightly\s|^firmly\s|\sfirmly\s|^fully\s|\sfully\s|freshly\s|\sfreshly\s|^finely\s|\sfinely\s|^thinly\s|\sthinly\s|^coarsely?\s|\scoarsely?\s)/', '', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^sifted\s|\ssifted\s|^melted\s|\smelted\s|^boiling\s|\sboiling\s|^boiling-hot\s|\sboiling-hot\s|^hot\s|\shot\s|^packed\s|\spacked\s|^crushed\s|\scrushed\s|^purchased\s|\spurchased\s|^cooked\s|\scooked\s|^pitted\s|\spitted\s|halved\s|\shalved\s|bottled\s|\sbottled\s|canned\s|\scanned\s|^peeled\s|\speeled\s|^squeezed\s|\ssqueezed\s|^bulbchopped\s|\sbulbchopped\s|^crumbled\s|\scrumbled\s|^unpeeled\s|\sunpeeled\s|^sliced\s|\ssliced\s|^torn\s|\storn\s|^g?rated\s|\sg?rated\s|^diced?\s|\sdiced?\s|^drained?\s|\sdrained?\s|^minced?\s|\sminced?\s|^well-chilled\s|\swell-chilled\s|^chilled\s|\schilled\s|^roasted\s|\sroasted\s|^brewed\s|\sbrewed\s|^chopped\s|\schopped\s)/', ' ', $tmp_ingredient));
							$tmp_ingredient = trim(preg_replace('/(^-)/', '', $tmp_ingredient));
							$tmp_ingredient = trim($tmp_ingredient);

							if ($tmp_ingredient != '')
								$ingredients[] = trim($tmp_ingredient);
						}

						$recipe_data['title'] = $recipe_title;
						$recipe_data['image'] = $recipe_url;
						$recipe_data['url'] = $recipe_url;
						$recipe_data['source_id'] = $source_id;

						$this->Shinjuku_model->insert_recipe($recipe_data, $ingredients);

						$valid++;

						$recipe_html_obj->clear();  
						unset($recipe_html_obj);
					}
				} else
					$url_valid = FALSE;

				$page = $page + 15;
				
				$html_obj->clear();  
				unset($html_obj);
			}
		}
		
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime), 0);
		$mins = floor ($totaltime / 60);
		$secs = $totaltime % 60;
		
		echo $valid." new recipe(s) added<br />";
		echo $skipped." recipe(s) skipped<br /><br />";
		die("This page was created in ".$mins." minute(s) and ".$secs." second(s)");
	}

	function seriouseats() {
		set_time_limit(0);
		
		$source_id = 4;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;
		
		$start_date = "2009-01-04";

		$year = date('Y', strtotime($start_date));
		$month = date('m', strtotime($start_date));
		$day = date('d', strtotime($start_date));

		$seriouseats_url = 'http://www.seriouseats.com/recipes/YYYY/MM/DD-week/';
		$valid_date = TRUE;
		$skipped = $valid = 0;
		
		while ($valid_date) {
			$seriouseats_cat_url = preg_replace(array('/YYYY/', '/MM/', '/DD/'), array($year, $month, $day), $seriouseats_url);

			$html_src = @file_get_contents($seriouseats_cat_url);
			
			if (!$html_src) {
				$tmp_year = date('Y', strtotime($year."-".$month."-".$day." next day"));
				$tmp_month = date('m', strtotime($year."-".$month."-".$day." next day"));
				$tmp_day = date('d', strtotime($year."-".$month."-".$day." next day"));
				$year = $tmp_year;
				$month = $tmp_month;
				$day = $tmp_day;
				
				continue;
			}

			$html_obj = str_get_html($html_src);
			
			$recipe_list_obj = $html_obj->find('div[class=home-excerpt] h3 a');
			$recipes = array();
			
			foreach ($recipe_list_obj as $recipe) {
				$recipe_url = $recipe->href;
				$recipe_title = html_entity_decode($recipe->innertext, ENT_COMPAT, 'UTF-8');
				$recipe_title_exp = explode(":", $recipe_title);
				
				if (count($recipe_title_exp) == 2)
					$recipe_title = trim($recipe_title_exp[1]);
				else
					$recipe_title = trim($recipe_title_exp[0]);
				
				if (!preg_match('/^http:\/\/www\.seriouseats\.com\//', $recipe_url) || $this->Shinjuku_model->recipe_exists("url='".$recipe_url."' OR (LOWER(title)='".strtolower(addslashes($recipe_title))."' AND source_id=".$source_id.")")) {
					$skipped++;

					continue;
				}

				$recipe_html_src = @file_get_contents($recipe_url);

				if (!$recipe_html_src)
					continue;

				$recipe_html_obj = str_get_html($recipe_html_src);
				$recipe_meta_obj = $recipe_html_obj->find('meta[name=description]');
				
				$recipe_data['abstract'] = $recipe_meta_obj[0]->content;
				
				$recipe_obj = $recipe_html_obj->find('span[class=ingredient]');
				
				$ingredients = array();

				foreach ($recipe_obj as $ingredient) {
					if (preg_match('/<strong>/', $ingredient))
						continue;

					$tmp_ingredient = ereg_replace("/\n\r|\r\n|\n|\r/|\t|&#160;", " ", $ingredient->innertext);
					$tmp_ingredient = strtolower(html_entity_decode(strip_tags($tmp_ingredient) , ENT_COMPAT, 'UTF-8'));

					if (preg_match('/(^a\s|^an\s|^\*|ingredient info|accompaniment|equipment|additional|even easier|toppings|garnish|preparation)/', $tmp_ingredient))
						continue;

					$tmp_ingredient = trim(preg_replace('/(\s*\([^)]*\)|\*|\d+|\/|;|%|\.)/', '', $tmp_ingredient));
					$tmp_ingredient = preg_split('/(\ssuch\sas\s|\ssuch\s|\sare\s|\seach\s|\swith\s|\sin\s|\sat\s|,|\sfrom\s)/', $tmp_ingredient);
					$tmp_ingredient = $tmp_ingredient[0];
					$tmp_ingredient = preg_split('/(\sor\s)/', $tmp_ingredient);
					$tmp_ingredient = trim($tmp_ingredient[count($tmp_ingredient) - 1]);
					$tmp_ingredient = trim(preg_replace('/(^heaping\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^kg\s|\s?kg\s|^lb\s|\s?lb\s|^ml\s|\s?ml\s|^oz\s|\s?oz\s|^pt\s|\s?pt\s|^tb?sp\s|\stb?sp\s|^bunch\s|\sbunch\s|^half-pints?\s|\shalf-pints?\s|^pints?\s|\spints?\s|^table?spoons?\s?|\stable?spoons?\s?|^teaspoons?\s|\steaspoons?\s|^-?pounds?\s|\s?-?pounds?\s|^pinches\sof\s|\spinches\sof\s|^pinch\sof\s|\spinch\sof\s|^cups?\s|\scups?\s|^-?ounces?\s|\s-?ounces?\s|^-?inch\s|\s?-?inch\s|^-?inches\s|\s-?inches\s|^-cacao\s|\s?-cacao\s|^-?inches-diameter|\s?-?inches-diameter|^-?inch-diameter|\s?-?inch-diameter|^-?inches-thick|\s?-?inches-thick|^-?inch-thick|\s?-?inch-thick|^-?inch-long|\s?-?inch-long|^-?inch-high|\s?-?inch-high|^-?inch-wide|\s?-?inch-wide)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^jars?\s|\sjar?\s|^box\s|\sbox\s|^boxes\s|\sboxes\s|^bottles?\s|\sbottles?\s|^baskets?\s|\sbaskets?\s|^bars?\s|\sbars?\s|^about\s|\s?about\s|^other\s|\sother\s|^cubes?\s|\scubes?\s|^plus?\s|\splus?\s|^of\s|\sof\s|^by\s|\sby\s|^to?\s|\sto?\s|^made?\s|\smade?\s|\smade$|^if\sdesired\s|\sif\sdesired\s|\sif\sdesired$|^and\s|\sand\s|\sand$|^pieces?\s|\spieces?\s|\spieces$|^slices?\s|\sslices?\s|\sslices$|^leaves?\s|\sleaves?\s|\sleaves$|^sticks?\s|\ssticks?\s|\ssticks?$|^cans?\s|\scans?\s|^bags?\s|\sbags?\s|^packages?\s|\spackages?\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^lightly\s|\slightly\s|^firmly\s|\sfirmly\s|^fully\s|\sfully\s|freshly\s|\sfreshly\s|^finely\s|\sfinely\s|^thinly\s|\sthinly\s|^coarsely?\s|\scoarsely?\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^sifted\s|\ssifted\s|^melted\s|\smelted\s|^boiling\s|\sboiling\s|^boiling-hot\s|\sboiling-hot\s|^hot\s|\shot\s|^packed\s|\spacked\s|^crushed\s|\scrushed\s|^purchased\s|\spurchased\s|^cooked\s|\scooked\s|^pitted\s|\spitted\s|halved\s|\shalved\s|bottled\s|\sbottled\s|canned\s|\scanned\s|^peeled\s|\speeled\s|^squeezed\s|\ssqueezed\s|^bulbchopped\s|\sbulbchopped\s|^crumbled\s|\scrumbled\s|^unpeeled\s|\sunpeeled\s|^sliced\s|\ssliced\s|^torn\s|\storn\s|^g?rated\s|\sg?rated\s|^diced?\s|\sdiced?\s|^drained?\s|\sdrained?\s|^minced?\s|\sminced?\s|^well-chilled\s|\swell-chilled\s|^chilled\s|\schilled\s|^roasted\s|\sroasted\s|^brewed\s|\sbrewed\s|^chopped\s|\schopped\s)/', ' ', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^-)/', '', $tmp_ingredient));
					$tmp_ingredient = trim($tmp_ingredient);

					if ($tmp_ingredient != '')
						$ingredients[] = trim($tmp_ingredient);
				}

				$recipe_data['title'] = $recipe_title;
				$recipe_data['image'] = $recipe_url;
				$recipe_data['url'] = $recipe_url;
				$recipe_data['source_id'] = $source_id;

				$this->Shinjuku_model->insert_recipe($recipe_data, $ingredients);

				$valid++;

				$recipe_html_obj->clear();  
				unset($recipe_html_obj);
			}

			$html_obj->clear();  
			unset($html_obj);

			$tmp_year = date('Y', strtotime($year."-".$month."-".$day." next week"));
			$tmp_month = date('m', strtotime($year."-".$month."-".$day." next week"));
			$tmp_day = date('d', strtotime($year."-".$month."-".$day." next week"));
			$year = $tmp_year;
			$month = $tmp_month;
			$day = $tmp_day;

			if (strtotime($year."-".$month."-".$day) > time())
				$valid_date = FALSE;
		}
		
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime), 0);
		$mins = floor ($totaltime / 60);
		$secs = $totaltime % 60;
		
		echo $valid." new recipe(s) added<br />";
		echo $skipped." recipe(s) skipped<br /><br />";
		die("This page was created in ".$mins." minute(s) and ".$secs." second(s)");
	}

	function allrecipes() {
		set_time_limit(0);
		
		$source_id = 5;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;

		$allrecipes_url = 'http://allrecipes.com/Recipes/ViewAll.aspx?SortBy=Rating&Direction=Descending&Page=';
		$skipped = $valid = 0;
		
		$page = 1;
		$url_valid = TRUE;
		
		while ($page < 501) {
			$allrecipes_cat_url = $allrecipes_url.$page;

			$html_src = @file_get_contents($allrecipes_cat_url);

			if (!$html_src) {
				$page = $page++;
				
				continue;
			}

			$html_obj = str_get_html($html_src);
			$recipe_list_obj = $html_obj->find('div[id=yay] h3 a');
			
			$recipes = array();
			
			foreach ($recipe_list_obj as $recipe) {
				$recipe_url = $recipe->href;
				$recipe_title = html_entity_decode($recipe->innertext, ENT_COMPAT, 'UTF-8');

				if (!preg_match('/^http:\/\/allrecipes\.com\//', $recipe_url) || $this->Shinjuku_model->recipe_exists("url='".$recipe_url."' OR (LOWER(title)='".strtolower(addslashes($recipe_title))."' AND source_id=".$source_id.")")) {
					$skipped++;

					continue;
				}

				$recipe_html_src = @file_get_contents($recipe_url);

				if (!$recipe_html_src)
					continue;

				$recipe_html_obj = str_get_html($recipe_html_src);
				$recipe_meta_obj = $recipe_html_obj->find('meta[name=description]');
				
				$recipe_data['abstract'] = $recipe_meta_obj[0]->content;

				$recipe_obj = $recipe_html_obj->find('div[class=ingredients] li[class=plaincharacterwrap]');
				$ingredients = array();

				foreach ($recipe_obj as $ingredient) {
					$tmp_ingredient = ereg_replace("/\n\r|\r\n|\n|\r/|\t|&#160;", " ", $ingredient->innertext);
					$tmp_ingredient = strtolower(html_entity_decode(strip_tags($tmp_ingredient) , ENT_COMPAT, 'UTF-8'));

					if (preg_match('/(^a\s|^an\s|^\*|ingredient info|accompaniment|equipment|additional|even easier|toppings|garnish|preparation)/', $tmp_ingredient))
						continue;

					$tmp_ingredient = trim(preg_replace('/(\s*\([^)]*\)|\*|\d+|\/|;|%|\.)/', '', $tmp_ingredient));
					$tmp_ingredient = preg_split('/(\ssuch\sas\s|\ssuch\s|\sare\s|\seach\s|\swith\s|\sin\s|\sat\s|,|\sfrom\s)/', $tmp_ingredient);
					$tmp_ingredient = $tmp_ingredient[0];
					$tmp_ingredient = preg_split('/(\sor\s)/', $tmp_ingredient);
					$tmp_ingredient = trim($tmp_ingredient[count($tmp_ingredient) - 1]);
					$tmp_ingredient = trim(preg_replace('/(^heaping\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^kg\s|\s?kg\s|^lb\s|\s?lb\s|^ml\s|\s?ml\s|^oz\s|\s?oz\s|^pt\s|\s?pt\s|^tb?sp\s|\stb?sp\s|^bunch\s|\sbunch\s|^half-pints?\s|\shalf-pints?\s|^pints?\s|\spints?\s|^table?spoons?\s?|\stable?spoons?\s?|^teaspoons?\s|\steaspoons?\s|^-?pounds?\s|\s?-?pounds?\s|^pinches\sof\s|\spinches\sof\s|^pinch\sof\s|\spinch\sof\s|^cups?\s|\scups?\s|^-?ounces?\s|\s-?ounces?\s|^-?inch\s|\s?-?inch\s|^-?inches\s|\s-?inches\s|^-cacao\s|\s?-cacao\s|^-?inches-diameter|\s?-?inches-diameter|^-?inch-diameter|\s?-?inch-diameter|^-?inches-thick|\s?-?inches-thick|^-?inch-thick|\s?-?inch-thick|^-?inch-long|\s?-?inch-long|^-?inch-high|\s?-?inch-high|^-?inch-wide|\s?-?inch-wide)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^jars?\s|\sjar?\s|^box\s|\sbox\s|^boxes\s|\sboxes\s|^bottles?\s|\sbottles?\s|^baskets?\s|\sbaskets?\s|^bars?\s|\sbars?\s|^about\s|\s?about\s|^other\s|\sother\s|^cubes?\s|\scubes?\s|^plus?\s|\splus?\s|^of\s|\sof\s|^by\s|\sby\s|^to?\s|\sto?\s|^made?\s|\smade?\s|\smade$|^if\sdesired\s|\sif\sdesired\s|\sif\sdesired$|^and\s|\sand\s|\sand$|^pieces?\s|\spieces?\s|\spieces$|^slices?\s|\sslices?\s|\sslices$|^leaves?\s|\sleaves?\s|\sleaves$|^sticks?\s|\ssticks?\s|\ssticks?$|^cans?\s|\scans?\s|^bags?\s|\sbags?\s|^packages?\s|\spackages?\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^lightly\s|\slightly\s|^firmly\s|\sfirmly\s|^fully\s|\sfully\s|freshly\s|\sfreshly\s|^finely\s|\sfinely\s|^thinly\s|\sthinly\s|^coarsely?\s|\scoarsely?\s)/', '', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^sifted\s|\ssifted\s|^melted\s|\smelted\s|^boiling\s|\sboiling\s|^boiling-hot\s|\sboiling-hot\s|^hot\s|\shot\s|^packed\s|\spacked\s|^crushed\s|\scrushed\s|^purchased\s|\spurchased\s|^cooked\s|\scooked\s|^pitted\s|\spitted\s|halved\s|\shalved\s|bottled\s|\sbottled\s|canned\s|\scanned\s|^peeled\s|\speeled\s|^squeezed\s|\ssqueezed\s|^bulbchopped\s|\sbulbchopped\s|^crumbled\s|\scrumbled\s|^unpeeled\s|\sunpeeled\s|^sliced\s|\ssliced\s|^torn\s|\storn\s|^g?rated\s|\sg?rated\s|^diced?\s|\sdiced?\s|^drained?\s|\sdrained?\s|^minced?\s|\sminced?\s|^well-chilled\s|\swell-chilled\s|^chilled\s|\schilled\s|^roasted\s|\sroasted\s|^brewed\s|\sbrewed\s|^chopped\s|\schopped\s)/', ' ', $tmp_ingredient));
					$tmp_ingredient = trim(preg_replace('/(^-)/', '', $tmp_ingredient));
					$tmp_ingredient = trim($tmp_ingredient);

					if ($tmp_ingredient != '')
						$ingredients[] = trim($tmp_ingredient);
				}

				$recipe_data['title'] = $recipe_title;
				$recipe_data['image'] = $recipe_url;
				$recipe_data['url'] = $recipe_url;
				$recipe_data['source_id'] = $source_id;

				$this->Shinjuku_model->insert_recipe($recipe_data, $ingredients);

				$valid++;

				$recipe_html_obj->clear();  
				unset($recipe_html_obj);
			}

			$page++;
			
			$html_obj->clear();  
			unset($html_obj);
		}
		
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime), 0);
		$mins = floor ($totaltime / 60);
		$secs = $totaltime % 60;
		
		echo $valid." new recipe(s) added<br />";
		echo $skipped." recipe(s) skipped<br /><br />";
		die("This page was created in ".$mins." minute(s) and ".$secs." second(s)");
	}
}
?>