<?php

add_action( 'wp_ajax_list_set_draft', 'reciprocity_update_list_item' );
add_action( 'wp_ajax_nopriv_list_set_draft', 'reciprocity_update_list_item' );

add_action( 'wp_ajax_list_set_publish', 'reciprocity_update_list_item_publish' );
add_action( 'wp_ajax_nopriv_list_set_publish', 'reciprocity_update_list_item_publish' );

add_action( 'wp_ajax_grocery_sorter', 'reciprocity_grocery_sorter' );
add_action( 'wp_ajax_nopriv_grocery_sorter', 'reciprocity_grocery_sorter' );

add_action( 'wp_ajax_list_set_trash', 'reciprocity_update_list_set_trash' );
add_action( 'wp_ajax_nopriv_list_set_trash', 'reciprocity_update_list_set_trash' );

add_action( 'wp_ajax_redo_menu_order', 'reciprocity_redo_menu_order' );
add_action( 'wp_ajax_nopriv_redo_menu_order', 'reciprocity_redo_menu_order' );

add_action( 'wp_ajax_list_add_ingredients', 'reciprocity_add_ingredients' );
add_action( 'wp_ajax_nopriv_list_add_ingredients', 'reciprocity_add_ingredients' );

add_action( 'wp_ajax_list_clear_meals', 'reciprocity_list_clear_meals' );
add_action( 'wp_ajax_nopriv_list_clear_meals', 'reciprocity_list_clear_meals' );

function reciprocity_update_list_item() {
	$my_post = array(
		'ID'           => $_POST['id'],
		'post_status' => 'draft'
	);

	// Update the post into the database
	$success = wp_update_post( $my_post );
	if ($success == 0) {
		echo 'Error setting post status. Sorry :(';
	} else {
		echo $success;
	}
	die();
}
function reciprocity_add_ingredients() {
	$list = $_POST['list'];
	$list = explode(PHP_EOL, $list);
	$pid = $_POST['pid'];
	//var_dump($list);
	//die();
	update_post_meta( $pid, 'meal_added', true );
	$itemcount = 1;
	foreach($list as $items) {
		
		$postargs = array(
			'post_title' => $items,
			'post_type' => 'item',
			'post_status' => 'publish',
			'post_author' => $userid,
		);
		$success = wp_insert_post($postargs);
		$itemcount++;
	}
	//echo 'true';
	$response = array(
		'status' => true,
		'message' => $itemcount." items added to the list."

	);
	die(json_encode( $response) );
}


function reciprocity_grocery_sorter() {
	
	$post_ID = $_POST['id'];
	$sectionID = array($_POST['catid']);
	
	$success = wp_set_post_terms( $post_ID, $sectionID, 'grocery_category' );
	// Update the post into the database
	//echo $success;
	
}
function reciprocity_update_list_set_trash() {

	// Update the post into the database
	$success = wp_delete_post(intval($_POST['id']), true);

	if ($success == false) {
		echo 'Error';
	} else {
		echo $_POST['id'];
	}
	die();
}

function reciprocity_redo_menu_order() {
//	var_dump($_POST);
//	print_r($_POST['items_to_reorder']);
	$orderIDs = $_POST['items_to_reorder'];
//	print_r($orderIDs);

	foreach ($orderIDs as $order => $pid) {
		$newpost = array(
			'ID'           => $pid,
			'menu_order' => $order
		);
		// Update the post into the database
		$success = wp_update_post( $newpost );
	}
	if ($success > 0) {
		echo 'Success';
	}
}

function reciprocity_update_menu_order() {

	// Update the post into the database
	$success = wp_update_post(intval($_POST['id']), true);

	if ($success == false) {
		echo 'Error';
	} else {
		echo $_POST['id'];
	}
	die();
}



function reciprocity_update_list_item_publish() {
	$my_post = array(
	      'ID'           => $_POST['id'],
	      'post_status' => 'publish'
	  );

	// Update the post into the database
	$success = wp_update_post( $my_post );
	if ($success == 0) {
		echo 'Error setting post status. Sorry :(';
	} else {
		echo $success;
	}
	die();
}

function reciprocity_list_clear_meals() {
	$postvars = array(
		'ids' => $_POST['ids']
	);
	$ids = $postvars[ids];
	//die;
	if (is_array($ids)) {
		foreach ($ids as $id) {
			$status = delete_post_meta( $id, 'meal_added' );
			if ($status !== true) {
				die('Error');
			}
		}
	}
	die('true');
}

add_action('wp_head', 'admin_url_is');
function admin_url_is() { ?>
	<script type="text/javascript">
	    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	</script>
<?php }
wp_enqueue_script( 'reciprocity_ajax', RA_PLUGIN_URL .'/ajax-stuff/ajax-functions.js', 'jQuery', '1.2.1', true );
