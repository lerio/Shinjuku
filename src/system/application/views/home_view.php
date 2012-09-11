<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="IT">
	<head>
		<title><?=$title?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link href="resources/css/style.css" rel="stylesheet" type="text/css"/>
		<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			<?php if ($result) :?>
			var loaded_recipes = 0;

			$("#more_button").click(function(){
				loaded_recipes += 15
				$.get("pagination/get_recipes/<?=$term?>/" + loaded_recipes, function(data){
					$("#other_recipes").append(data);
				});
				$.get("pagination/get_recipes_count/<?=$term?>/" + loaded_recipes, function(data){
					var num_recipes = data;
					if(num_recipes <= 15)
					{
						$("#more_button").hide();
					}					
				});
			})
			<?php endif;?>

			$(function() {
				$( "#autocomplete" ).autocomplete({
					source: function(request, response) {
						$.ajax({
							url: "<?php echo site_url('autocomplete/suggestions'); ?>",
							data: { term: $("#autocomplete").val()},
							dataType: "json",
							type: "POST",
							success: function(data){
								response(data);
							}
						});
					},
					minLength: 3,
					delay: 500,
				})
			});
		});
		</script>
	</head>
	<body>
		<div id="header"><?=anchor('/', 'Home')?></div>
		<div id="search">
			<form id="search_form" action="home/search" method="POST">
				<input type="text" name="term" id="autocomplete" />
				<input type="submit" id="search" value="Search" />
			</form>
		</div>
		<?php if ($result) :?>
		<h1><?=$query?></h1>
		<div id="results">
			<?php if (count($first_recipes > 0)) :?>
			<ul>
				<?php
				foreach($first_recipes as $recipe) {
					$img_src = "image/".$recipe->id."_".$this->main_library->str_to_slug($recipe->title).".jpg";
				?>
				<li><div class="recipeCont"><img class="recipeImg" src="<?=$img_src?>" alt="<?=$recipe->title?>" /></div><p><?=anchor($recipe->url, $recipe->title)?> from <?=anchor($recipe->source_url, $recipe->source_name)?></p><p class="abstract"><?=$recipe->abstract?></p></li>
				<?php }?>
			</ul>
			<?php endif;?>
			<?php if (count($other_recipes > 0)) :?>
			<h2>Other recipes:</h2>
			<ul id="other_recipes">
				<?php
				foreach($other_recipes as $recipe) {
					$img_src = "image/".$recipe->id."_".$this->main_library->str_to_slug($recipe->title).".jpg";
				?>
				<li><div class="recipeCont"><img class="recipeImg" src="<?=$img_src?>" alt="<?=$recipe->title?>" /></div><p><?=anchor($recipe->url, $recipe->title)?> from <?=anchor($recipe->source_url, $recipe->source_name)?></p><p class="abstract"><?=$recipe->abstract?></p></li>
				<?php }?>
			</ul>
			<div id="more_button" <?php if ($recipes_count < 15) {?> style="display:none;"<?php }?>> + more + </div>
			<?php endif;?>
		</div>
		<?php endif;?>
	</body>
</html>