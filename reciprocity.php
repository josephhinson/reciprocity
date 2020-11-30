<?php
/*
Plugin Name: Reciprocity App
Plugin URI: http://hinson.co
Description: This plugin enables the functionality of Reciprocity
Author: jhinson
Version: 1.0
Author URI: http://joseph.hinson.co
*/

define("RA_PLUGIN_DIR", dirname( __FILE__ ));
define("RA_PLUGIN_URL", plugin_dir_url( __FILE__ ));


function ra_ready_jQuery() {
	wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script( 'jquery-ui-sortable' );
}
function ra_plugin_styles() {
    wp_enqueue_style( 'reciprocityapp_styles', RA_PLUGIN_URL .'/css/ra_styles.css' );
    wp_enqueue_style( 'FontAwesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', false, '1.0', 'screen' ); // this is where you will put your stylesheet

}
add_action( 'wp_enqueue_scripts', 'ra_plugin_styles', 10 );
add_action('init', 'ra_ready_jQuery');

// checks to make sure that you're seeing only your stuff for the individual meals
function checkUser($meal_id, $user_id) {
	$uservar = get_post_meta($meal_id, 'cal_array', true);
	if ($uservar[0] == $user_id) {
		return true;
	}
}

/* Define the custom box */

// WP 3.0+
add_action('add_meta_boxes', 'reciprocity_meta_box');

/* Do something with the data entered */
add_action('save_post', 'reciprocity_save_postdata');

/* Adds a box to the main column on the Post and Page edit screens */
function reciprocity_meta_box() {
    add_meta_box( 'reciprocity_sectionid', __( 'Meal Details', 'reciprocity_textdomain' ), 'reciprocity_inner_custom_box','post');
}

/* Prints the box content */
function reciprocity_inner_custom_box() {

  // Use nonce for verification
  wp_nonce_field( plugin_basename(__FILE__), 'reciprocity_noncename' );

	global $post;
	$ingredients = get_post_meta($post->ID, 'ingredients', true);
	$notes =  get_post_meta($post->ID, 'notes', true);

  // The actual fields for data entry ?>
<table border="0" cellspacing="5" cellpadding="5">
	<tr><td style="width:150px;vertical-align: top;"><label for="ingredients">
	      Ingredients
	 </label></td>
	<td><textarea name="ingredients" rows="8" cols="50"><?php echo $ingredients; ?></textarea></td>
	</tr>
	<tr>
		<td style="width:150px;vertical-align: top;">
			<label for="notes">
	 			Notes
	 		</label>
		</td>
		<td>
			<textarea name="notes" rows="8" cols="50"><?php echo $notes; ?></textarea>
		</td>
	</tr>
</table>


<?php
}

/* When the post is saved, saves our custom data */
function reciprocity_save_postdata( $post_id ) {

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['reciprocity_noncename'], plugin_basename(__FILE__) ) )
      return $post_id;
  // verify if this is an auto save routine.
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
      return $post_id;


  // Check permissions
  if ( 'post' == $_POST['post_type'] )
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return $post_id;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return $post_id;
  }

  // OK, we're authenticated: we need to find and save the data

  	$ingredients = $_POST['ingredients'];
	$notes = $_POST['notes'];

  // update the data
		update_post_meta($post_id, 'ingredients', $ingredients);
		update_post_meta($post_id, 'notes', $notes);
}

add_action('init','test_custom_post');
function test_custom_post()
{
	$labels = array(
    'name' => _x('Recipes', 'post type general name'),
    'singular_name' => _x('Recipe', 'post type singular name'),
    'add_new' => _x('Add New', 'recipe'),
    'add_new_item' => __('Add New Recipe'),
    'edit_item' => __('Edit Recipe'),
    'new_item' => __('New Recipe'),
    'view_item' => __('View Recipe'),
    'search_items' => __('Search Recipes'),
    'not_found' =>  __('No recipes found'),
    'not_found_in_trash' => __('No recipes found in Trash'),
    'parent_item_colon' => ''
  );

register_post_type( 'post', array(
			'labels' => $labels,
           'public'  => true,
          '_builtin' => true,
          '_edit_link' => 'post.php?post=%d',
           'capability_type' => 'post',
            'hierarchical' => false,
           'rewrite' => false,
            'query_var' => false,
           'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions' ),
       ) );
}
add_filter('menu_order','change_label');
add_filter('custom_menu_order','order');
function change_label($stuff)
{
	global $menu,$submenu;

	$menu[5][0]= 'Recipes';
	$submenu['edit.php'][5][0]  = 'Recipes';
return $stuff;
}
function order()
{
	return true;
}

add_action('init', 'rec_add_list_items');
function rec_add_list_items() {
register_post_type('item', array(
'label' => 'List Items',
'description' => '',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'item', 'with_front' => true),
'query_var' => true,
'has_archive' => true,
'exclude_from_search' => true,
'supports' => array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
'labels' => array (
  'name' => 'List Items',
  'singular_name' => 'List Item',
  'menu_name' => 'List Items',
  'add_new' => 'Add List Item',
  'add_new_item' => 'Add New List Item',
  'edit' => 'Edit',
  'edit_item' => 'Edit List Item',
  'new_item' => 'New List Item',
  'view' => 'View List Item',
  'view_item' => 'View List Item',
  'search_items' => 'Search List Items',
  'not_found' => 'No List Items Found',
  'not_found_in_trash' => 'No List Items Found in Trash',
  'parent' => 'Parent List Item',
)
) ); }

include 'ajax-stuff/ajax-hooks.php';
include 'inc/initialize-shortcodes.php';
include 'inc/listeners.php';
include 'inc/calendar.php';
include 'inc/grocery-sorter.php';
