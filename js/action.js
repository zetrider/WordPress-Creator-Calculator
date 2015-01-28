jQuery(document).ready(function( $ ) {

	wpcc_url 	= $('.jq_wpcc_setting').data('plugin_url');
	wpcc_id 	= $('.jq_wpcc_setting').data('wpcc_id');
	
	/* Row Setting */
	$('.wpcc_fields li .ico').click( function() {
		var this_parent = $(this).closest('li');
		$(this).toggleClass('ico_active');
		$('.setting', this_parent).slideToggle();
	});
	
	/* Sortable */
	$(".wpcc_fields_sortable ul").sortable({
		opacity: 0.8,
		cursor: 'move',
		update: function() {
			$('.jq_wpcc_sortable').val($(this).sortable("toArray"));
		}							  
	});
	
	/* Toggle */
	$(".wpcc_js_toggle").live('click', function() {
		var this_data = $(this).data('container');
		$('.' + this_data).slideToggle();
	});
	
	/* Farbtastic Row ADM */
	$.fn.wpcc_color_picker = function () {
		$(this).each(function() {
			$(this).wpColorPicker({
				defaultColor: '#f7f7f7',
				change: function(event, ui){
					$(this).closest('li').css('background-color', ui.color.toString());
				},
				clear: function() {},
				hide: true,
				palettes: true
			});
		});
	};
	$('.jq_color_picker').wpcc_color_picker();
	
	/* List Rows */
	$('.jq_list_add').live('click', function() {
		var this_parent 	= $(this).closest('li');
		var this_parent_id 	= this_parent.data('fid');
		var div_length 		= $('.list_rows .list_row', this_parent).length;
		if(div_length > 0)
		{
			var div_next 		= $('.list_rows .list_row:last-child', this_parent).data('id') + 1;
		}
		else
		{
			var div_next 		= 0;
		}
		$('.list_rows', this_parent).append(
		'<div class="list_row" data-id="' + div_next + '">'+
		'	<input type="text" name="wpcc_fields[' + this_parent_id + '][list][' + div_next + '][val]" value="" class="list_row_val">'+
		'	<input type="text" name="wpcc_fields[' + this_parent_id + '][list][' + div_next + '][txt]" value="" class="list_row_txt">'+
		'	<input type="text" name="wpcc_fields[' + this_parent_id + '][list][' + div_next + '][img]" value="" class="list_row_img wpcc_media_upload" placeholder="http://">'+
		'	<div class="jq_list_remove">x</div>'+
		'	<div class="clear"></div>'+
		'</div>'
		);
		return false;
	});
	$('.jq_list_remove').live('click', function() {
		$(this).closest('.list_row').remove();
	});
	
	/* Media Upload */
	$('.wpcc_media_upload').live('click', function(event) {
		wpcc_media_upload = $(this);
		var frame;
		event.preventDefault();
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media();
		/*
		frame.on( "select", function() {
			var attachment = frame.state().get("selection").first();
			frame.close();
			wpcc_media_upload.val(attachment.attributes.url);
		});
		*/
		frame.on( "close", function() {
			var attachment = frame.state().get("selection").first();
			frame.close();
			if( attachment == undefined)
			{
				wpcc_media_upload.val('');
			}
			else
			{
				wpcc_media_upload.val(attachment.attributes.url);
			}
		});
		frame.open();
	});
});