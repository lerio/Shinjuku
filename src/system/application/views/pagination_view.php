<?php foreach($other_recipes as $recipe) {
	$img_src = "image/".$recipe->id."_".$this->main_library->str_to_slug($recipe->title).".jpg";
?>
<li><div class="recipeCont"><img class="recipeImg" src="<?=$img_src?>" alt="<?=$recipe->title?>" /></div><p><?=anchor($recipe->url, $recipe->title)?> from <?=anchor($recipe->source_url, $recipe->source_name)?></p><p class="abstract"><?=$recipe->abstract?></p></li>
<?php }?>