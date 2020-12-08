jQuery(document).on("click", ".ingredients-wrapper .ast-button", function (event) {
  event.preventDefault();
  var items = jQuery(".ingredients")
	.find("li")
	.text()
    // we tried mapping this to an array and it didn't work.

  var data = {
    action: "list_add_ingredients",
    list: items,
	pid: jQuery(".ast-button").data("id"),
	uid: jQuery(".ast-button").data("uid"),
  }
  //return data;
   jQuery.post(ajaxurl, data, function (response) {
	   var responseData = JSON.parse(response);
	   if (responseData.status == true) {
		jQuery('.ingredients-wrapper').before('<div class="ingredients-added">'+responseData.message+' <span style="float:right"><a href="/list/">Visit List</a></span></div>');
		setTimeout(function(){
			jQuery('.ingredients-added').fadeOut()
		 }, 10000);
	   }
    });
});

// after clicking on one of the items in the "checked off list",
// it will change the status to published, and put it back on the grocery list
jQuery(document).on("click", '.grocery-list label', function() {
	var t = jQuery(this);
	var thisLI = t.parents('li');
	var tlist = t.parents('ul');
	var theid = jQuery(this).attr('data-id');
	var original_category = jQuery(this).data('category');
	// check to see which "list" this item belongs to, if it's the "done list" set it as published
	if (tlist.hasClass('done-list')) {
		var data = {
			action: 'list_set_publish',
			id: theid
		}
		jQuery.post(ajaxurl, data, function(response) {
			if (response == theid) {
				thisLI.remove();
				jQuery('#'+original_category + ' .groc-list').prepend(thisLI).parents('.hidden-list').show();
			} else {
				alert(response);
			}
		});
	// but, if it's on the "grocery list" set the status to draft
	} else if ( tlist.hasClass('groc-list')) {
		var data = {
			action: 'list_set_draft',
			id: theid,
		}
		jQuery.post(ajaxurl, data, function(response) {
			if (response == theid) {
				thisLI.remove();
				jQuery('.done-list').prepend(thisLI).parents('.hidden-list').show();
			} else {
				alert(response);
			}
		});
	} //endif
});

jQuery(document).on("click", '.grocery-list li button', function() {
	var t = jQuery(this);
	var thisLI = t.parents('li');
	var buttonid = jQuery(this).attr('data-id');

	var data = {
		action: 'list_set_trash',
		id: buttonid,
	}
	var memory = thisLI.remove();
	
	jQuery.post(ajaxurl, data, function(response) {
		if (response == buttonid) {
			
		} else {
			t.append(memory);
			alert(response);
		}
	});
});

jQuery(document).on('click', '.meal-plans button', function(event) {
	event.preventDefault();
	if (jQuery(this).data('action') == 'reset') {
		var meal_ids = jQuery(this).data('ids');
		console.log(meal_ids);
		var data = {
			action: 'list_clear_meals',
			ids: meal_ids,
			uid: jQuery(this).data('uid'),
		}
	} else if ( jQuery(this).data('action') == 'delete' ) {
		var meal_id = jQuery(this).data('id');
		var user_id = jQuery(this).data('uid');
		var data = {
			action: 'list_delete_meal',
			id: meal_id,
			uid: user_id
		}
	}
	jQuery.post(ajaxurl, data, function(response) {
		
		var responseData = JSON.parse( response );
		if (responseData.action == 'reset') {
			if (responseData.data == true) {
				jQuery('.meal-plans').fadeOut();
				
			}
		}
		if (responseData.action == 'delete') { 
			jQuery('.meal-plans li[data-id='+responseData.data+']').hide();	
		}
	});
})

function ajax_sorter(item_id, cat_id) {
	var data = {
		action: 'grocery_sorter',
		id: item_id,
		catid: cat_id,
	}
	jQuery.post(ajaxurl, data, function(response) {
		return response;
	});
}