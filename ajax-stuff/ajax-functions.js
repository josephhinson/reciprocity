jQuery(document).on("click", ".ast-button", function (event) {
  event.preventDefault();
  var items = jQuery(".ingredients")
	.find("li")
	.text()
    // we tred mapping this to an array and it didn't work.

  var data = {
    action: "list_add_ingredients",
    list: items,
    pid: jQuery(".ast-button").data("id"),
  }
  //return data;
   jQuery.post(ajaxurl, data, function (response) {
	   var responseData = JSON.parse(response);
	   if (responseData.status == true) {
		   alert(responseData.message);
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
	// check to see which "list" this item belongs to, if it's the "done list" set it as published
	if (tlist.hasClass('done-list')) {
		var data = {
			action: 'list_set_publish',
			id: theid,
		}
		jQuery.post(ajaxurl, data, function(response) {
			if (response == theid) {
				thisLI.remove();
				jQuery('.groc-list').prepend(thisLI).parents('.hidden-list').show();
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
// This isn't used right now since I removed the button
// but it might be useful later, so I'm leaving it in for reference.
jQuery(document).on("click", "button.reorder", function() {
 	var saveData = {};
	jQuery('.groc-list li').each(function() {
		n = jQuery(this).index();
		var i = jQuery(this).children('label').data('id');
		saveData[n] = jQuery(this).children('label').data('id');

	});
	console.log(saveData);
//	var serialized_data = jQuery.serialize(saveData);
	var data = {
		action: 'redo_menu_order',
		items_to_reorder: saveData,
	}
	jQuery.post(ajaxurl, data, function(response) {
//		if (response == 'Success') {
//			alert("Order Updated");
//		} else {
//			alert(response);
//		}
	alert('List Order Updated!');
	});
} );

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