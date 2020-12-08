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

add_action( 'wp_ajax_list_delete_meal', 'reciprocity_list_delete_meal' );
add_action( 'wp_ajax_nopriv_list_delete_meal', 'reciprocity_list_delete_meal' );

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
	$uid = $_POST['uid'];
	$key = 'meal_plan';
	//var_dump($list);
	$pid = intval($pid);
	$mealPlan = get_user_meta( $uid, $key, true );
	if ( empty($mealPlan) ) {
		$mealPlan = array();
	}
	if ( $array_key = array_search($pid, $mealPlan ) !== true ) {
		$mealPlan[] = $pid;
	}
	//unset($mealPlan);
	$user_meta_update = update_user_meta($uid, $key, $mealPlan);

	$itemcount = 0;
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
		'message' => $itemcount." items added to the list.",
		'user_meta_updated' => $user_meta_update

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
	$ids = $_POST['ids'];
	$uid = $_POST['uid'];

	$status = delete_user_meta( $uid, 'meal_plan' );
	$response = array();
	//$response['data'] = $status;
	$response['data'] = $status;
	//$response['action'] = 'reset';
	die(json_encode($response));
}

function reciprocity_list_delete_meal() {
	$id = $_POST['id'];
	$uid = $_POST['uid'];
	//die($uid . ' - ' .$id);
	$key = 'meal_plan';
	$mealPlan = (array)get_user_meta( $uid, $key, true );
	//$array_key = array_search( $id, $mealPlan, TRUE );

/*	foreach($mealPlan as $mealID) {
		if	(strcmp($id, $mealID) !== 0) {
			//var_dump($mealID);
			$array_key = $keyCount;
			//die();
			$deleted_id = $id;
			
		}
		$keyCount++;
	}
*/
	$id = intval($id);
	//var_dump($id);
	//die();
	$array_key = array_search($id, $mealPlan );
	$deleted_id = $mealPlan[$array_key];
	//var_dump($array_key);
	//if ( $array_key ) {
		unset( $mealPlan[ $array_key ] );
	//}
	//$deleted_id = $mealPlan[$array_key];
	//die;
	//array_search($id, $mealPlan);
	//print_r($mealPlan);
	//die($deleted_id . ' - ' . $array_key);

	$changedMealPlan = update_user_meta( $uid, $key, $mealPlan );
	//var_dump($changedMealPlan);
	$response = array();
	$response['action'] = 'delete';
	if ($changedMealPlan) {
		$response['data'] = $deleted_id;
	}
	die( json_encode($response) );
}


add_action('wp_head', 'admin_url_is');
function admin_url_is() { ?>
	<script type="text/javascript">
	    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	</script>
<?php }
wp_enqueue_script( 'reciprocity_ajax', RA_PLUGIN_URL .'/ajax-stuff/ajax-functions.js', 'jQuery', '1.2.2', true );
