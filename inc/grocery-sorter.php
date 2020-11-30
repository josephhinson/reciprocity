<?php

add_action("edit_post","sort_groceries");
add_action("publish_post","sort_groceries");
add_action("wp_insert_post","sort_groceries");

// 
function sort_groceries($post_ID) {
    // This section should get all the sections of the grocery store.
    $item = strtolower(get_the_title($post_ID));
        // actions: loop through the categories, build array of description, 
        // then get the post title, lowercase, and string compare.

    $sections = get_terms( 'grocery_category', array(
        'hide_empty' => false,
	) );
	if ( !empty($item) ) {
		foreach ($sections as $section) {
			//print_r($section);
			if (!empty($section->description)) {
				$keywords = explode(',', $section->description);
				foreach ($keywords as $keyword) {
					
					if(strstr($item,$keyword) == TRUE) {
						$sectionID = array($section->term_id);
						wp_set_post_terms( $post_ID, $sectionID, 'grocery_category' );
						$breakit = true;
					}
					if($breakit) {
						break;
					}
				}
				
			}
		}
	}
}