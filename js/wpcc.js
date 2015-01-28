jQuery(document).ready(function($) {
	wpcc_url 			= $('.wpcc_url').val();
	wpcc_data 			= {};
	wpcc_data_type 		= {};
	
	/* Add Obj Data */
	$.fn.wpcc_add_obj = function() {
		$(this).each(function() {
			var this_form 			= $(this).closest('.wpcc_form');
			var wpcc_id 			= $('.wpcc_id', this_form).val();
			wpcc_data[wpcc_id] 		= {};
			wpcc_data_type[wpcc_id] = {};
		});
	};
	$('.wpcc_form').wpcc_add_obj();
	
	function wpcc_data_set(wpcc_id, this_fid, val) {
		var this_data 		= $('.wpcc_form_' + wpcc_id + ' .wpcc_jq_action_' + this_fid).data('data');
		var this_convert 	= '';
		if(this_data == 'data_count')
		{
			this_convert = String(val).length;
		}
		else if(this_data == 'data_date_time')
		{
			this_time = Number(val) * 1000;
			this_convert = String(this_time);
		}
		else
		{
			this_convert = String(val);
		}
		
		wpcc_data_type[wpcc_id][this_fid] 	= this_data;
		wpcc_data[wpcc_id][this_fid] 		= this_convert;
	}
	
	function wpcc_sum(str) {
		var str_replace = String(str).replace( /[^0-9.()+\-/*%^]/g, '' );
		var str_eval 	= eval(str_replace);
		return str_eval;
	}
	
	function wpcc_date_zero(n) {
		return (String(n).length < 2 ? '0' : '') + n;
	}
	function wpcc_timestamp(str) {
		var date = new Date(String(str));
		return date.valueOf(); 
	}
	function wpcc_date(timestamp, type) {
		var date 	= new Date(timestamp);
		var dateD 	= wpcc_date_zero(date.getDate());
		var dateM 	= wpcc_date_zero(date.getMonth() + 1);
		var dateY 	= date.getFullYear();

		if(type == 'data_date')
		{
			return dateD + '-' + dateM + '-' + dateY;
		}
		else if(type == 'data_day_number')
		{
			return dateD;
		}
		else if(type == 'data_month')
		{
			return dateM;
		}
		else if(type == 'data_year')
		{
			return dateY;
		}
	}
	
	function wpcc_fn_action(value, this_fid, wpcc_id) {
		wpcc_data_set(wpcc_id, this_fid, value);
		wpcc_rf_action(wpcc_id);
		$('.wpcc_jq_if').each(function() {
			var if_id 			= $(this).data('if_id');
			var if_operator 	= $(this).data('operator');
			var if_val 			= $(this).data('val');
			var if_show_id		= $(this).data('show');
			var if_hide_id		= $(this).data('hide');
			var if_logic		= $(this).data('logic');
			wpcc_jq_if_check_condition(wpcc_data[wpcc_id][if_id], if_val, if_operator, if_show_id, if_hide_id, if_logic, wpcc_id);
		});
		//console.log(wpcc_data);
	}
	
	/* Result Fields */
	function wpcc_rf_action(wpcc_id) {
		$('.wpcc_result_fields').each(function() {
			var this_fiels 	= $(this);
			var this_form 	= this_fiels.closest('.wpcc_form');
			var this_action = this_fiels.data('action');
			var this_fileds = this_fiels.data('fileds');
			var this_fid 	= this_fiels.data('fid');
			var this_data 	= this_fiels.data('data');
			if(this_fileds.length > 0) {
				var fileds_split = this_fileds.split(',');
				var rf_out = [];
				for (i = 0; i < fileds_split.length; i++ ) {
					var rf_val = wpcc_data[wpcc_id][fileds_split[i]];
					rf_out.push(rf_val);
				}
				var this_sum = wpcc_sum(rf_out.join(this_action), this_fid);
				
				if(this_data == 'data_count_day')
				{
					var this_result = Math.floor((this_sum)/(1000*60*60*24));
					wpcc_data_set(wpcc_id, this_fid, this_result);
				}
				else if(this_data == 'data_date' || this_data == 'data_day_number' || this_data == 'data_month' || this_data == 'data_year')
				{
					var this_result = wpcc_date(this_sum, this_data);
					wpcc_data_set(wpcc_id, this_fid, this_result);
				}
				else
				{
					var this_result = this_sum;
					wpcc_data_set(wpcc_id, this_fid, this_result);
				}
				this_fiels.val(this_result);
				$('.rf_' + this_fid, this_form).html(this_result);
			}
		});
	}
	
	/* Load Default Data */
	$.fn.wpcc_load_data = function() {
		$(this).each(function() {
			var this_form 		= $(this).closest('.wpcc_form');
			var wpcc_id 		= $('.wpcc_id', this_form).val();
			var this_type 		= $(this).data('type');
			var this_fid 		= $(this).data('fid');
			
			if(this_type == 'inputtext' || this_type == 'custom_fields' || this_type == 'checkbox' || this_type == 'slider' || this_type == 'ifhidden' || this_type == 'session' || this_type == 'jquery' || this_type == 'result_fields')
			{
				wpcc_data_set(wpcc_id, this_fid, $(this).data('default'));
			}
			else if(this_type == 'date')
			{
				if($(this).data('datadate') == 'string')
				{
					wpcc_data_set(wpcc_id, this_fid, $(this).val());
				}
				else
				{
					wpcc_data_set(wpcc_id, this_fid, wpcc_timestamp($(this).val()));
				}
			}
			else if(this_type == 'hidden')
			{
				wpcc_data_set(wpcc_id, this_fid, $(this).val());
			}
			else if(this_type == 'select')
			{
				wpcc_data_set(wpcc_id, this_fid, $('option:selected', $(this)).val());
			}
			else if(this_type == 'radio')
			{
				if($(this).prop("checked"))
				{
					wpcc_data_set(wpcc_id, this_fid, $(this).val());
				}
			}
		});
	};
	$('.wpcc_jq_action').wpcc_load_data();
	
	/* Fields Action */	
	$.fn.wpcc_field_action = function() {
		$(this).each(function() {
			var this_form 		= $(this).closest('.wpcc_form');
			var wpcc_id 		= $('.wpcc_id', this_form).val();
			var this_type 		= $(this).data('type');
			var this_fid 		= $(this).data('fid');
			field_value 		= '';
			
			if(this_type == 'inputtext')
			{
				$(this).keyup( function()
				{
					var field_val = $(this).val();		
					var this_action		= $(this).data('action');
					var this_price		= $(this).data('price');
					var this_exclude	= $(this).data('exclude');
					if(field_val == '')
					{
						field_value =  $(this).data('default');
					}
					else
					{
						field_value = field_val;
					}
					var this_sum		= wpcc_sum(field_value+this_action+this_price);
					wpcc_fn_action(this_sum, this_fid, wpcc_id);
				});
			}
			else if(this_type == 'custom_fields')
			{	
				$(this).keyup( function()
				{
					var field_val = $(this).val();					
					if(field_val == '')
					{
						field_value =  $(this).data('default');
					}
					else
					{
						field_value = field_value;
					}
					wpcc_fn_action(field_value, this_fid, wpcc_id);
				});
			}
			else if(this_type == 'select')
			{
				$(this).change( function()
				{
					wpcc_fn_action($(this).val(), this_fid, wpcc_id);
				});
			}
			else if(this_type == 'radio')
			{
				$(this).click( function()
				{
					wpcc_fn_action($(this).val(), this_fid, wpcc_id);
				});
			}
			else if(this_type == 'checkbox')
			{
				var this_action = $(this).data('action');
				var this_prop 	= {};
				$('input', $(this)).each(function()
				{
					$(this).click( function()
					{
						if($(this).prop("checked"))
						{
							this_prop[$(this).data('i')] = $(this).val();
						}
						else
						{
							delete this_prop[$(this).data('i')];
						}
						var this_out = []; 
						for (var this_key in this_prop) {
							this_out.push(this_prop[this_key]); 
						}
						var field_value = wpcc_sum(this_out.join(this_action));
						wpcc_fn_action(field_value, this_fid, wpcc_id);
					});
				});
				
			}
			else if(this_type == 'slider')
			{
				/* look fn.wpcc_slider STOP */
			}
			else if(this_type == 'date')
			{
				/* look fn.wpcc_datepicker onSelect */
			}
		});
	};
	$('.wpcc_jq_action').wpcc_field_action();
	
	/* If Change */
	$.fn.wpcc_jq_if_change = function(ids, fn, wpcc_id) {
		ids_string = String(ids);
		if(ids_string.length > 0) {
			var this_check_arr_show	= ids_string.indexOf(",");
			
			if(this_check_arr_show >= '0')
			{
				var this_show_arr 	= ids_string.split(",");
				for (i = 0; i < this_show_arr.length; i++ ) {
					if(fn == 'show')
					{
						$('.wpcc_form_' + wpcc_id + ' .wpcc_box_' + this_show_arr[i]).show();
					}
					else if(fn == 'hide')
					{
						$('.wpcc_form_' + wpcc_id + ' .wpcc_box_' + this_show_arr[i]).hide();
					}
				}
			}
			else
			{
				if(fn == 'show')
				{
					$('.wpcc_form_' + wpcc_id + ' .wpcc_box_' + ids_string).show();
				}
				else if(fn == 'hide')
				{
					$('.wpcc_form_' + wpcc_id + ' .wpcc_box_' + ids_string).hide();
				}
			}
		}
	};
	
	/* Condition */
	function wpcc_jq_if_check_condition (value, if_val, operator, s, h, if_logic, wpcc_id) {
		var this_type = $(this).data('type');
		
		if(this_type == 'checkbox')
		{
			var for_checked = $(this).prop('checked');
		}
		else
		{
			var for_checked = true;
		}
		
		if(operator == 'between')
		{
			var if_value_between 		= String(if_val).split("-");
			var if_value_between_one 	= if_value_between[0];
			var if_value_between_two 	= if_value_between[1];
		}
		if(
			( operator == '==' && for_checked && String(value) == String(if_val) )
			||
			( operator == '>=' && for_checked && Number(value) >= Number(if_val) )
			||
			( operator == '<=' && for_checked && Number(value) <= Number(if_val) )
			||
			( operator == '>' && for_checked && Number(value) > Number(if_val) )
			||
			( operator == '<' && for_checked && Number(value) < Number(if_val) )
			||
			( operator == 'between' && for_checked && Number(value) >= Number(if_value_between_one) && Number(value) <= Number(if_value_between_two) )
			||
			( operator == 'notempty' && for_checked && String(value).length > 0 && String(value) != 'undefined')
		)
		{
			$(this).wpcc_jq_if_change(s, 'show', wpcc_id);
			$(this).wpcc_jq_if_change(h, 'hide', wpcc_id);
		}
		else
		{
			if(if_logic != 'if')
			{
				$(this).wpcc_jq_if_change(s, 'hide', wpcc_id);
				$(this).wpcc_jq_if_change(h, 'show', wpcc_id);
			}
		}
	}
	
	/* Slider */
	$.fn.wpcc_slider = function () {
		$(this).each(function() {
			var this_sl  	= $(this);
			var this_parent	= this_sl.closest('.wpcc_box');
			var this_form 	= this_sl.closest('.wpcc_form');
			var wpcc_id 	= $('.wpcc_id', this_form).val();
			
			var this_fid   = this_sl.data('fid');
			var sl_va = $(this).data('value');
			var sl_mi = $(this).data('min');
			var sl_ma = $(this).data('max');
			var sl_st = $(this).data('step');
			var sl_po = $(this).data('position');
			this_sl.slider({
				orientation: 	sl_po,
				range: 			'min',
				value: 			sl_va,
				min: 			sl_mi,
				max: 			sl_ma,
				step: 			sl_st,
				slide:	function( event, ui ) {
					$( '.wpcc_jq_slider_text', this_parent ).val( ui.value );
				},
				stop:	function( event, ui ) {
					wpcc_fn_action(ui.value, this_fid, wpcc_id);
				}
			});
			$( '.wpcc_jq_slider_text', this_parent ).on('blur', function() {
				if($(this).val() < sl_mi)
				{
					var input_slider_val = sl_mi;
				}
				else if($(this).val() > sl_ma)
				{
					var input_slider_val = sl_ma;
				}
				else
				{
					var input_slider_val = $(this).val();
				}
				$('.wpcc_jq_slider_box', this_parent).slider( "value", input_slider_val );
				$(this).val(input_slider_val);
				wpcc_fn_action(input_slider_val, this_fid, wpcc_id);
			});
		});
	}
	$('.wpcc_jq_slider').wpcc_slider();
	
	/* Datepicker */
	$.fn.wpcc_datepicker = function () {
		$(this).each(function() {
		
			var this_date 		= $(this);
			var this_parent 	= this_date.closest('.wpcc_date');
			var this_form 		= this_date.closest('.wpcc_form');
			var wpcc_id 		= $('.wpcc_id', this_form).val();
			var this_fid   		= this_date.data('fid');
			var this_datadate   = this_date.data('datadate');
			var this_default 	= this_date.data('default');
			var this_datemin 	= this_date.data('datemin');
			var this_datemax 	= this_date.data('datemax');
			if(this_datadate == 'string')
			{
				var this_dateformat = 'dd-mm-yy';
			}
			else
			{
				var this_dateformat = 'yy-mm-dd';
			}
			
			this_date.datepicker({
				changeMonth: 		true,
				changeYear: 		true,
				showOn: 			'button',
				buttonImage: 		wpcc_url + '/images/calendar.jpg',
				buttonImageOnly: 	true,
				dateFormat: 		this_dateformat,
				showOtherMonths:	true,
				selectOtherMonths:	true,
				defaultDate: 		this_default,
				minDate: 			this_datemin,
				maxDate: 			this_datemax,
				altField: 			$('.wpcc_inputdisabled', this_parent),
				altFormat: 			'dd-mm-yy',
				onSelect: function(dateText, inst) {
					if(this_datadate == 'string')
					{
						wpcc_fn_action(dateText, this_fid, wpcc_id);
					}
					else
					{
						wpcc_fn_action(wpcc_timestamp(dateText), this_fid, wpcc_id);
					}
				}
			});
		});
	}
	$('.wpcc_jq_datepicker').wpcc_datepicker();
	
	/* WPCC Validation */
	$.fn.wpcc_validation = function () {
		$(this).each(function() {
			$(this).bind('change keyup input click', function() {
				var this_val = $(this).val();
				
				if($(this).data('validation') == 'only_numbers_one_dot')
				{
					this_new_val = this_val.replace(/[^0-9.]/g, '');
					
					if(this_new_val.match(/\./g)) {
						this_count_dot = this_new_val.match(/\./g).length;
						if(this_count_dot > 1)
						{
							this_new_val = this_new_val.substr(0, this_new_val.lastIndexOf('.'));
						}
						if(this_new_val[0] == '.')
						{
							this_new_val = '0' + this_new_val;
						}
					}					
				}
				else if($(this).data('validation') == 'only_numbers')
				{
					this_new_val = this_val.replace(/[^0-9]/g, '');
				}
				if(this_val != this_new_val)
				{
					$(this).val(this_new_val);
				}
			});
		});
	};
	$('.wpcc_jq_validation').wpcc_validation();
	
	$.fn.wpcc_maxchar = function () {
		$(this).each(function() {
			$(this).bind('change keyup input click', function() {
				var this_maxchar = $(this).data('maxchar') + 0;
				
				if(this_maxchar > 0 &&  $(this).val().length > this_maxchar)
					$(this).val($(this).val().substr(0, this_maxchar));
				
			});
		});
	};
	$('.wpcc_inputtext').wpcc_maxchar();
		
	/* Send Calc */
	$('.wpcc').on('click', '.wpcc_form .wpcc_submit', function() {
		var wpcc_parent = $(this).closest('.wpcc');
		var wpcc_form 	= $(this).closest('form');
		var wpcc_id 	= $('.wpcc_id', wpcc_form).val();
		var wpcc_autos 	= $('.wpcc_id', wpcc_form).data('autoscroll');
		var wpcc_action = $('.wpcc_action', wpcc_form).val();
		
		$('.wpcc_loading div', wpcc_form).show();
		$.post(
			ajaxurl,
			wpcc_form.serialize(),
			function(data){
				if(wpcc_action > 0)
				{
					wpcc_parent.html(data);
				}
				else
				{
					$('.wpcc_result_block_' + wpcc_id).html(data);
					if(wpcc_autos == 'y')
					{
						$('html,body').animate({
							scrollTop: $('.wpcc_result_block_' + wpcc_id).offset().top
						}, 800);
					}
				}
				$('.wpcc_loading div', wpcc_form).hide();
			}
		);
		return false;
	});
	
	/* Send Mail */
	$('.wpcc').on('click', '.wpcc_mail .wpcc_submit', function() {
		var wpcc_this_p = $(this).closest('form');
		var wpcc_id 	= $('.wpcc_mail_id', wpcc_this_p).val();
		$('.wpcc_loading div', wpcc_this_p).show();
		$.post(
			ajaxurl,
			wpcc_this_p.serialize(),
			function(jdata){
				var data = JSON.parse ( jdata );
				
				if(data.error != null && data.error != '' && data.error != 'undefined')
				{
					$('.wpcc_mail_' + wpcc_id + ' .wpcc_error').html(data.error);
				}
				else
				{
					$('.wpcc_result_block_' + wpcc_id).html(data.success);
				}
				$('.wpcc_loading div', wpcc_this_p).hide();
			}
		);
		return false;
	});
	
});