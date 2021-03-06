<?php
// This file sets up the shortcodes for the plugin

// [ra_ingredients]
function ra_ingredients_checklist($atts) {
        extract(shortcode_atts(array(
		), $atts));
		$current_user = wp_get_current_user();
        ob_start(); ?>
        <?php
        global $post;
		$i_data = get_post_meta($post->ID, 'ingredients', true);
		?>
		<style type="text/css">
	.ingredients-added {	    
		background: #efefef;
		padding: 10px 20px;
		display: block;
		margin-bottom: 20px;
		}
	</style>
	<?php
					if ($i_data) {
						echo '<div class="ingredients-wrapper">';
						echo '<a href="#" data-uid="'.$current_user->ID.'" data-id="'.$post->ID.'" class="ast-button"><i class="fa fa-plus"></i> ADD TO LIST</a>';
						echo '<ul class="ingredients">';
						$ingreds = explode("\n", $i_data);
						foreach ($ingreds as $ingred) {
							if (strlen($ingred) > 1) {
								echo '<li>'. $ingred .'</li>';
							}
						}
						echo '</ul>';
						echo '</div>';
					}
					?>
					<script type="text/javascript" charset="utf-8">
					jQuery(document).ready(function() {
						jQuery('.ingredients li').click(function() {
							jQuery(this).toggleClass('checked');
						});
					});
					</script>
					<style>
						.ingredients-wrapper .ast-button {
							float: right;
							padding: 5px 12px;
  							font-size: 12px;
						}
					</style>
        <?php
        $return = ob_get_clean();
        return $return;
}
add_shortcode("ra_ingredients", "ra_ingredients_checklist");
// end shortcode


// [ra_grocery_list]
function ra_grocery_list($atts) {
	extract(shortcode_atts(array(), $atts));
	ob_start(); 
	global $current_user;
?>
					<?php
					//$current_url = "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
					$userid = $current_user->ID;
					?>
					<?php if (is_user_logged_in()): ?>
						<div class="grocery-list">
						<?php
						// Query all posts WITHOUT any categories
						$taxonomy = 'grocery_category';
						$terms = get_terms( $taxonomy, array('fields'=>'ids')); /* GET ALL TERMS FROM A TOXNOMY */
						$args = array(
							'posts_per_page' => '-1',
							'post_type' => 'item',
							'post_status' => 'publish',
							'orderby' => 'menu_order',
							'author' => $userid,
							'tax_query' => array(
								array(
									'taxonomy' => $taxonomy,
									'field'    => 'term_id',
									'terms'    => $terms,
									'operator' => 'NOT IN' /* DO THE MAGIC -  Get all post that no in the taxonomy terms */
								)
							)
						);
						$uncats = get_posts($args); ?>
						<div class="<?php if (empty($uncats)) { echo 'hidden-list '; } ?>shopping-list">
							<?php
								//print_r($section);
							echo '<h2>Unsorted</h2>';
							echo '<ul class="groc-list">';
							foreach ($uncats as $uncat) {
								echo '<li>
									<label for="item-'.$uncat->post_name.'" data-id="'.$uncat->ID.'">
										<input value="true" type="checkbox" id="item-'.$uncat->post_name.'">
											'.$uncat->post_title.'
									</label>
									<button data-id="'.$uncat->ID.'" class="btn btn-danger pull-right"><i class="fa fa-trash"></i></button>
								</li>';
							}
							echo '</ul>'; ?>
							</div>
						
					<?php
					$sections = get_terms( $taxonomy, array(
						'hide_empty' => false,
					) );
					foreach ($sections as $section) {
					
						$allids = array();
						$todoargs = array(
							'posts_per_page' => -1,
							'post_type' => 'item',
							'post_status' => 'publish',
							'orderby' => 'menu_order',
							'order' => 'ASC',
							'tax_query' => array(
								array(
									'taxonomy' => $taxonomy,
									'field'    => 'term_id',
									'terms'    => array( $section->term_id ),
								),
							)
						);
						$todos = get_posts($todoargs);
						if	(!empty($todos)) { ?>
							<div id="<?php echo $section->slug; ?>" data-id="<?php echo $section->term_id; ?>" class="shopping-list">
							<?php
								//print_r($section);
							echo '<h2>'.$section->name.'</h2>';
							echo '<ul class="groc-list">';
							foreach ($todos as $todo) {
								echo '<li>
									<label data-category="'. $section->slug.'" for="item-'.$todo->post_name.'" data-id="'.$todo->ID.'">
										<input value="true" type="checkbox" id="item-'.$todo->post_name.'">
											'.$todo->post_title.'
									</label>
									<button data-id="'.$todo->ID.'" class="btn btn-danger pull-right"><i class="fa fa-trash"></i></button>
								</li>';
							}
							echo '</ul>'; ?>
							</div>
						<?php } ?>
						
					<?php }	?>
					<?php $sections = get_terms( $taxonomy, array(
						'hide_empty' => false,
						'pad_counts' => true,
					) );
					foreach($sections as $section) {

						if($section->count == 0) { ?>
							<div id="<?php echo $section->slug; ?>" data-id="<?php echo $section->term_id; ?>" class="unsorted-list shopping-list">
							<?php
								//print_r($section);
							echo '<h2>'.$section->name.'</h2>';
							echo '<ul class="groc-list">';
							echo '</ul>'; ?>
							</div>
						<?php
						} else {
							// do nothing
						}
					} ?>
					<div style="clear:both;"></div>

						<?php
					$doneargs = array(
						'posts_per_page' => -1,
						'post_type' => 'item',
						'post_status' => 'draft',
						'orderby' => 'menu_order',
						'order' => 'ASC',
						'author' => $userid
					);
					$dones = get_posts($doneargs); ?>
					<div class="<?php if (empty($dones)) { echo 'hidden-list '; } ?>checked-list">
					<?php
						echo '<h3>Items checked off the list</h3>';
						echo '<ul class="done-list">';
						$doneids = array();
						foreach ($dones as $done) {
							$category = get_the_terms( $done->ID , array( 'grocery_category') );
							$category = $category[0];
							echo '<li><label data-category="'.$category->slug.'" for="item-'.$done->post_name.'" data-id="'.$done->ID.'"><input value="true" checked="checked" type="checkbox" id="item-'.$done->post_name.'"> '.$done->post_title.'</label>
								<button data-id="'.$done->ID.'" class="btn btn-danger pull-right"><i class="fa fa-trash"></i></button></li>';
							$allids[] = $done->ID;
							$doneIDs[] = $done->ID;
						}
						echo '</ul>';
						?>
						<div class="btn-group" role="group" aria-label="...">
							<form action="" method="POST" accept-charset="utf-8">
								<input type="hidden" name="resetIDs" value="<?php echo json_encode($allids); ?>">
								<input type="hidden" name="nukem" value="true" id="nuke_em">
								<button type="submit" class="btn btn-mini btn-danger"><i class="fa fa-refresh"></i> Reset This List</button>
							</form>
							<form action="" method="POST" accept-charset="utf-8">
								<input type="hidden" name="deleteIDs" value="<?php echo json_encode($allids); ?>">
								<input type="hidden" name="bahleted" value="true" id="nuke_em">
								<button type="submit" class="btn btn-mini btn-danger"><i class="fa fa-trash"></i> Delete All Checked Items</button>
							</form>
						</div>
					</div><!-- End container for hidden div -->

					<form action="" method="POST" accept-charset="utf-8">
						<label for="todo_items">Add todo items below. For multiple items, use a comma to separate. <em>Ex: Bread, cheese, eggs, sugar.</em></label>
						<textarea name="todo_items" rows="8" cols="40" placeholder="Add grocery items here"></textarea>
						<p>
							<input class="btn" type="submit" value="Add List Items">
						</p>
					</form>
					</div>
					<?php else : ?>
				<h3>Login to Add your list items -- sorry, it's just a thing you have to do.</h3>
				<?php wp_login_form(); ?>
					<?php endif; ?>
					<style>
					.unsorted-list {
					float: left;
					width: 50%;
					padding: 10px;
					display: none;
				}
				.unsorted-list h2 {
					font-size: 20px;
				}
				.ui-state-highlight {
					background: yellow;
					border: 1px dotted orange;
				}
				.unsorted-list ul {
					background: #fafafa;
					border: 1px dotted #ddd;
				}
				</style>
				<script>
				jQuery(document).ready(function() {
				jQuery("ul.groc-list").sortable({
				connectWith: ".groc-list",
					placeholder: "ui-state-highlight",
					delay: 150,
					activate: function( event, ui ) {
						jQuery('.unsorted-list').show();
					},
					update: function( event, ui ) {
						//console.log(ui.item.parents('div.shopping-list').attr('id'));
//						  console.log( jQuery(this).parents('div.shopping-list').attr('id') );
					if (this === ui.item.parent()[0]) {
						if (ui.sender !== null) {
							// the movement was from one container to another - do something to process it
							// ui.sender will be the reference to original container
								var $item_id = ui.item.children('label').data('id');
								var $cat_id = jQuery(this).parents('div.shopping-list').data('id');
								var tparent = jQuery(this).parents('div.shopping-list');
								/*
								if (tparent.hasClass('unsorted_list')) {
									alert(tparent.text());
									jQuery(this).removeClass('unsorted-list');
									var mem = tparent.remove();
								}
								jQuery('.grocery-list').append(tparent);
								tparent.show();
								jQuery('.unsorted-list').hide();
								//jQuery('.unsorted-list').toggle();
								//alert( $item_id +' should be in the category '+ $cat_id );
								*/
								ajax_sorter($item_id, $cat_id);
						} else {
							// the move was performed within the same container - do your "same container" stuff
						}
					}

					}
				});
				});
			</script>
	<?php
	$return = ob_get_clean();
	return $return;
}
add_shortcode("ra_grocery_list", "ra_grocery_list");


// end shortcode
function ra_mealplan($atts) {
	extract(shortcode_atts(array(), $atts));
	global $current_user;
	$meal_plan_ids = get_user_meta($current_user->ID, 'meal_plan', true);
	//var_dump($meal_plan_ids);

	$args = array(
		'post_type'  => 'post',
		'post__in' => $meal_plan_ids,
		'orderby'=>'post__in',
		//'meta_key'   => 'meal_added',
	);
	if ($meal_plan_ids) {
		$recipes = get_posts($args);
	}

	//echo $current_user->ID;
	//die;
	ob_start();
	$r_ids = array();
	if (!empty($recipes) ) {
	?>
	<div class="meal-plans">
	<h2>Meal Plan:</h2>
	<style>
	.meal-plans ul {
		margin-left: 0px;
		margin-top: 10px;
		list-style: none;
	}
	.meal-plans li {
		display: block;
		background: #fafafa;
		padding: 5px 10px;
		margin-bottom: 10px;
		font-size: 18px;
		font-weight: bold;
		margin-bottom: 13px;
	}
	button.delete-but {
		padding: 0;
		width: 25px;
		height: 25px;
		font-weight: bold;
		display: inline-block;
		line-height: 23px;
		float: right;
	}
	</style>
	<ul>
		<?php
		foreach ($recipes as $recipe) { ?>
			<li data-id=<?php echo $recipe->ID; ?>>
				<a href="<?php the_permalink($recipe->ID); ?>"><?php echo $recipe->post_title; ?></a> 
				<button data-id="<?php echo $recipe->ID; ?>" data-uid="<?php echo $current_user->ID; ?>" data-action="delete" class="delete-but">-</button>
			</li>
		<?php $r_ids[] = $recipe->ID; ?>
		<?php
		}
		?>
		</ul>
		<hr>
		<button data-uid="<?php echo $current_user->ID; ?>" href="#" data-action="reset" data-ids="<?php echo json_encode($r_ids); ?>" class="ast-button">Reset Meal Plan</button>
	</div>
	<?php
	} // end check for empty
    $return = ob_get_clean();
    return $return;
}
add_shortcode("ra_mealplan", "ra_mealplan");