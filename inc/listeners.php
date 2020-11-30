<?php
// This file contains a bunch of things that are listening for "POST" responses and redirecting accordingly to avoid duplicate posting.

add_action('wp', 'ra_what_happened');
function ra_what_happened() {
    if (!empty($_POST)) {
        global $current_user;
        get_currentuserinfo();
        $userid = $current_user->ID;

        if ($_POST['todo_items']):
        	$new_todos = explode( ', ', $_POST['todo_items'] );
        //	print_r($new_todos);
        //	$old_totos = $todos;
        	// redefining new_todos function as both old and new todos, so we don't overwrite old ones.
        	foreach ($new_todos as $key => $value) {
        //		echo $key . ' - '. $value;
        		$postargs = array(
        			'post_title' => $value,
        			'post_type' => 'item',
        			'post_status' => 'publish',
        			'post_author' => $userid
        		);
        		wp_insert_post($postargs);
        	}
            wp_redirect(get_permalink($post->ID));
            exit;
        endif;
        // if the grocery list has asked to reset everything:
        if ($_POST['nukem'] == 'true') {
            $allids = json_decode($_POST['resetIDs']);
            foreach ($allids as $key => $value) {
                $postdata = array(
                      'ID'           => $value,
                      'post_status' => 'publish'
                  );
                  //var_dump($postdata);
                  $success = wp_update_post( $postdata );
            }
            wp_redirect(get_permalink($post->ID));
            exit;
        // if the grocery list has asked to delete everything:
         } else	if ($_POST['bahleted'] == 'true') {
             $allids = json_decode($_POST['deleteIDs']);
            foreach ($allids as $key => $value) {
                $postdata = array(
                      'ID'           => $value,
                      'post_status' => 'trash'
                  );
                  $success = wp_update_post( $postdata );
              }
              wp_redirect(get_permalink($post->ID));
              exit;
            }

        // if it's a new recipe:
        if ($_POST['new_recipe'] == true) {
                $bImage = false;
                $bImageURL = false;
                $catid = $_POST['cat'];
                $message = '';
                // Do some minor form validation to make sure there is content
                if (strlen($_POST['title']) > 0) {
                    $title = $_POST['title'];
                } else {
                    $message .= 'Please enter a title.<br />';
                }
                if (strlen($_POST['ingredients']) > 0)
                {
                    $ingredients =  $_POST['ingredients'];
                }
                else
                {
                    $message .= 'Please enter the ingredients for your recipe.<br />';
                }
                if (strlen($_POST['notes']) > 0)  {
                    $notes =  $_POST['notes'];
                }
                if (strlen($_POST['instructions']) > 0) {
                    $instructions = $_POST['instructions'];
                } else {
                    $message .= 'Please enter a story behind your image.<br />';
                }
                if (strlen($_POST['image_url']) > 0) {
                    $image_url = $_POST['image_url'];
                }
                if (
                    !empty($_FILES['image']) > 0 && (
                    $_FILES['image']['size'] > 0 &&
                    strlen( $_FILES['image']['tmp_name'] ) > 0
                    )
                )
                {
                    //We have a file, is it the right type and size?
                    if ((($_FILES["image"]["type"] == "image/gif")
                    || ($_FILES["image"]["type"] == "image/jpeg")
                    || ($_FILES["image"]["type"] == "image/png")
                    || ($_FILES["image"]["type"] == "image/pjpeg"))
                    && ($_FILES["image"]["size"] < 4096000))
                    {
                        //It is the right type, go ahead and upload
                        if ($_FILES["file"]["error"] > 0)
                        {
                            $message .= "There was an error uploading your image:" . $_FILES["file"]["error"] . "<br />";
                        }
                        else
                        {
                            $bImage = true;
                        }
                    }
                    else
                    {
                        $message .= "File uploaded was either too large or of the wrong type.<br />";
                    }
                }
                else
                {
                    //Ok, we don't have an image.  Do we have a URL?
                    if (isset($image_url))
                    {
                        $bImageURL = true;
                    }
                }
                $info = pathinfo($image_url);
                $info_image_filename = pathinfo($_FILES["image"]["name"]);
                if($info['extension'] != "jpg" && $info['extension'] != "jpeg" && $info['extension'] != "gif" && $info['extension'] != "png")
                {
                    $bImageURL = false;
                }

                if($info_image_filename['extension'] != "jpg" && $info_image_filename['extension'] != "jpeg" && $info_image_filename['extension'] != "gif" && $info_image_filename['extension'] != "png")
                {
                    $bImage = false;
                }

                if(isset($title) && isset($ingredients) && isset($instructions))
                {
                    // Add the content of the form to $post as an array
                    $post = array(
                        'post_status'	=> 'publish',			// Choose: publish, preview, future, etc.
                        'post_type'	=> 'post',  // Use a custom post type if you want to
                        'post_author' => $userid,
                        'post_title' => $title,
                        'post_content' => $instructions
                    );
                    $post_id = wp_insert_post($post);  // Pass  the value of $post to WordPress the insert function
                    // http://codex.wordpress.org/Function_Reference/wp_insert_post

                    if($post_id > 0)
                    {
                         wp_set_post_terms( $post_id,  array($catid), 'category');

                        //Post as succesful, let's add those custom fields
                        add_post_meta($post_id, 'ingredients', $ingredients, true);
                        if (strlen($notes) > 0) {
                            add_post_meta($post_id, 'notes', $notes, true);
                        }

                        // defining the uploads url (in case it's different than default)
                        $uploads_dir = wp_upload_dir();

                        if($bImage)
                        {
                            $filename = $uploads_dir['path'] .'/'. $_FILES["image"]["name"];

                            move_uploaded_file($_FILES["image"]["tmp_name"], $filename);

                            $wp_filetype = wp_check_filetype(basename($filename), null);
                            $attachment = array(
                                'post_mime_type' => $wp_filetype['type'],
                                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                                'post_content' => '',
                                'post_status' => 'inherit'
                            );
                            $attach_id = wp_insert_attachment($attachment, $filename, $post_id);

                            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                            wp_update_attachment_metadata( $attach_id,  $attach_data );

                            add_post_meta($post_id, '_thumbnail_id', $attach_id);
                            //Done?
                        }
                        else if($bImageURL)
                        {
                                $ch = curl_init($image_url);
                                $filename = $uploads_dir['path'].'/'.$info['basename'];
                                $fh = fopen($filename, "w");
                                curl_setopt($ch, CURLOPT_FILE, $fh);
                                curl_exec($ch);
                                curl_close($ch);

                                $wp_filetype = wp_check_filetype(basename($filename), null);
                                $attachment = array(
                                    'post_mime_type' => $wp_filetype['type'],
                                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                                    'post_content' => '',
                                    'post_status' => 'inherit'
                                );
                                $attach_id = wp_insert_attachment($attachment, $filename, $post_id);

                                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                                wp_update_attachment_metadata( $attach_id,  $attach_data );

                                add_post_meta($post_id, '_thumbnail_id', $attach_id);
                        }

                        $permalink = get_permalink($post_id);
            //			mail('clifgriffin@gmail.com,joseph@outthinkgroup.com', "New image Submission" , "A new showcase peice has been submitted, follow this link to review / approve it:\n\rPermalink: $permalink\n\rEdit link: http://sunnibrown.com/imagerevolution/wp-admin/post.php?post=$post_id&action=edit");
                        $message .= "Recipe Added. Thanks so much!";

                        //Clear them out since we're done.
                        $ingredients = '';
                        $instructions = '';
                        $image_url = '';
                        $notes = '';
                        $title = '';
                    }
                }
                else
                {
                    $message .= 'Required fields missing.';
                }
                wp_redirect(get_permalink($post->ID).'?message='.urlencode($message));
                exit;
        }
    }
}
