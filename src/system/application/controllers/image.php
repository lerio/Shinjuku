<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Image extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->library('main_library');
		$this->load->helper('simple_html_dom');
	}
 
	function index($image) {
		$exploded_image = explode('_', $image);
		$recipe_id = $exploded_image[0];
		$recipe_detail = $this->Shinjuku_model->select_recipe($recipe_id);
		
		if ($recipe_detail->image == '') {
			$html_src = @file_get_contents($recipe_detail->url);

			if ($html_src) {
				$html_obj = str_get_html($html_src);
				
				switch ($recipe_detail->source_id) {
					case 1:
						$img_obj = $html_obj->find('div[id=recipe_thumb] a img');
						break;

					case 2:
						$img_obj = $html_obj->find('div[class=mainImg] img');
						break;

					case 3:
						$img_obj = $html_obj->find('div[class=rcp-imgsm] img');
						break;

					case 4:
						$img_obj = $html_obj->find('div[class=recipe-image-small] img');
						break;

					case 5:
						$img_obj = $html_obj->find('div[class=rec-imagediv] a img');
						break;
				}
				
				if (count($img_obj) > 0) {
					$image_url = ereg_replace("/\n\r|\r\n|\n|\r/|\t|&#160;", "", $img_obj[0]->src);
					$image_url = preg_replace("/\s/", "%20", $image_url);

					if (preg_match('/commrec\.png|img_noPhoto/', $image_url)) {
						$img_path = RESOURCES_PATH.'img/thumbs/'.$recipe_detail->source_id.'_no_img.jpg';
						$this->Shinjuku_model->update_recipe(array('id' => $recipe_id), array('image' => $recipe_detail->source_id.'_no_img'));
					} else {
						if ($recipe_detail->source_id == 1)
							$image_url = 'http://www.epicurious.com'.$image_url;
					
						$recipe_path = $recipe_id;
					
						if ($recipe_id < 100)
							$recipe_path = '0'.$recipe_path;
					
						if ($recipe_id < 10)
							$recipe_path = '0'.$recipe_path;					
					
						$tree_path = $this->main_library->create_path(RESOURCES_PATH.'img/thumbs/', $recipe_path);
						$thumb_file_path = RESOURCES_PATH.'img/thumbs/'.$tree_path.$recipe_id.'.jpg';

						file_put_contents($thumb_file_path, file_get_contents($image_url));
					
						// modifico l'immagine catturata
						$this->load->library('image_lib');

						// resize dell'immagine
						$config_resize[] = $config_crop[] = 'GD2';
						$config_resize['source_image'] = $config_crop['source_image'] = $thumb_file_path;

						$img_size = getimagesize($thumb_file_path);
				
						if (($img_size[0] / $img_size[1]) <= RATIO_LIMIT) {
							$width = RECIPE_IMAGE_WIDTH;
							$height = (int)(($width * $img_size[1]) / $img_size[0]);
							$config_crop['height'] = RECIPE_IMAGE_HEIGHT;								
							$y_axis = (int)($height - RECIPE_IMAGE_HEIGHT) / 2;

							if ($y_axis > 0)
								$config_crop['y_axis'] = $y_axis;
						} else {
							$height = RECIPE_IMAGE_HEIGHT;
							$width = (int)(($height * $img_size[0]) / $img_size[1]);
							$config_crop['width'] = RECIPE_IMAGE_WIDTH;
							$x_axis = (int)($width - RECIPE_IMAGE_WIDTH) / 2;

							if ($x_axis > 0)
								$config_crop['x_axis'] = $x_axis;								
						}

						$config_resize['maintain_ratio'] = true;
						$config_resize['width'] = $width;
						$config_resize['height'] = $height;
						$config_resize['quality'] = IMAGE_QUALITY;

						$this->image_lib->clear();
						$this->image_lib->initialize($config_resize);
						$this->image_lib->resize();

						// crop dell'immagine
						$config_crop['maintain_ratio'] = false;

						$this->image_lib->clear();
						$this->image_lib->initialize($config_crop);
						$this->image_lib->crop();
				
						$img_path = $thumb_file_path;
					
						$this->Shinjuku_model->update_recipe(array('id' => $recipe_id), array('image' => $tree_path.$recipe_id));
					}
				} else {
					$img_path = RESOURCES_PATH.'img/thumbs/'.$recipe_detail->source_id.'_no_img.jpg';
					$this->Shinjuku_model->update_recipe(array('id' => $recipe_id), array('image' => $recipe_detail->source_id.'_no_img'));
				}
			}
		} else
			$img_path = RESOURCES_PATH.'img/thumbs/'.$recipe_detail->image.'.jpg';
		
		header('Content-type: image/jpeg');
		readfile($img_path);
	}
}

?>