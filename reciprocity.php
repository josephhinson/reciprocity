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
