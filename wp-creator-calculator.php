<?php
/*
Plugin Name: wp-creator-calculator
Plugin URI: http://zetrider.ru/wpcc/
Description: Creating forms calculator, the introduction of the template and write
Version: 3.6.5
Author: ZetRider
Author URI: http://zetrider.ru
Author Email: ZetRider@bk.ru
*/
/*  Copyright 2014  zetrider  (email: zetrider@bk.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'WPCC_PLUGIN_NAME', dirname(plugin_basename(__FILE__)) );
define( 'WPCC_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPCC_PLUGIN_NAME );
define( 'WPCC_PLUGIN_URL', WP_PLUGIN_URL  . '/' . WPCC_PLUGIN_NAME );

$wpcc_DB = $wpdb->prefix.'creator_calculator';

load_plugin_textdomain( 'wpcc', PLUGINDIR . '/' . WPCC_PLUGIN_NAME . '/lang/' );

function wpcc_admin_menu(){
	$ico 	= WPCC_PLUGIN_URL . "/images/wpcc.png";
	$page 	= add_menu_page('WPCC', 'WPCC', 'manage_options', 'wpcc', 'wpcc_setting', "$ico");
	add_action('admin_print_scripts-' . $page, 'wpcc_admin_scripts');
	add_action('admin_print_styles-' . $page, 'wpcc_admin_styles');
}
add_action('admin_menu', 'wpcc_admin_menu');

function wpcc_admin_scripts() {
	wp_enqueue_script( 'jquery-ui-sortable' );	
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_script( 'wpcc-action', WPCC_PLUGIN_URL . '/js/action.js' );
	wp_enqueue_media();
}

function wpcc_admin_styles() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'wpcc_style', WPCC_PLUGIN_URL . '/style.css' );
}

function wpcc_init() {
	if (!session_id())
		session_start();
}
add_action('init', 'wpcc_init');

function wpcc_script() {
	wp_enqueue_script( 'jquery' );
	wp_localize_script( 'jquery', 'ajaxurl', admin_url('admin-ajax.php') );
}
add_action( 'wp_enqueue_scripts', 'wpcc_script' );

function wpcc_ajax_result() {
	$result 		= wpcc_result();
	$wpcc_action 	= intval($_POST['wpcc_action']);
	
	if($wpcc_action > 0)
	{
		echo do_shortcode('[wpcc id="' . $wpcc_action . '"]');
	}
	else
	{
		echo $result;
		echo wpcc_mail_form($_POST['wpcc_id']);
	}
	exit();
}
add_action('wp_ajax_wpcc_ajax_result', 'wpcc_ajax_result');
add_action('wp_ajax_nopriv_wpcc_ajax_result', 'wpcc_ajax_result');

function wpcc_ajax_mail() {
	echo wpcc_mail_send(array('ajax'));
	exit();
}
add_action('wp_ajax_wpcc_ajax_mail', 'wpcc_ajax_mail');
add_action('wp_ajax_nopriv_wpcc_ajax_mail', 'wpcc_ajax_mail');

function wpcc_esc_attr($text='') {
	return trim(stripslashes(esc_html($text)));
}

function wpcc_pr($arr='') {
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

function wpcc_price($price='') {
	$price = trim($price);
	$price = str_replace(',','.', $price);
	return preg_replace('@(bcsqrt|bcpow|round|ceil|floor)\K|[^0-9.()+\-/*%^:]@', '', $price);
}

$wpcc_type_field = array(
	'textblock' => array(
		'name' 	=> __('Text Block','wpcc'),
		'child' => array(
					'title',
					'color',
					'text',
					'hidden_display',
					'delete'
					)
	),
	'select' => array(
		'name' 	=> 'SELECT',
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'list',
					'data',
					'exclude',
					'hidden_display',
					'mail_show',
					'delete'
					)
	),
	'checkbox' => array(
		'name' 	=> 'Checkbox',
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'list',
					'default',
					'action',
					'data',
					'exclude',
					'hidden_display',
					'mail_show',
					'delete'
					)
	),
	'radio' => array(
		'name' 	=> 'Radio',
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'list',
					'data',
					'exclude',
					'hidden_display',
					'mail_show',
					'delete'
					)
	),
	'inputtext' => array(
		'name' 	=> 'Input Text',
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'price',
					'action',
					'default',
					'placeholder',
					'maxchar',
					'validation',
					'data',
					'exclude',
					'hidden_display',
					'mail_show',
					'delete'
					)
	),
	'date' => array(
		'name' 	=> __('Date','wpcc').' [beta]',
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'date',
					'datemin',
					'datemax',
					'datadate',
					'exclude',
					'hidden_display',
					'mail_show',
					'delete'
					)
	),
	'hidden' => array(
		'name' 	=> 'Input Hidden',
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'price',
					'data',
					'exclude',
					'mail_show',
					'delete'
					)
	),
	'session' => array(
		'name' 	=> '$_SESSION',
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'sess_calc_id',
					'sess_calc_results',
					'default',
					'data',
					'exclude',
					'mail_show',
					'delete'
					)
	),
	'jquery' => array(
		'name' 	=> __('jQuery field','wpcc'),
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'jq_id',
					'default',
					'data',
					'exclude',
					'mail_show',
					'delete'
					)
	),
	'slider' => array(
		'name' 	=> 'Slider',
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'slider_min',
					'slider_max',
					'slider_step',
					'slider_position',
					'default',
					'data',
					'exclude',
					'hidden_display',
					'mail_show',
					'delete'
					)
	),
	'if' => array(
		'name' 	=> __('Condition','wpcc'),
		'child' => array(
					'title',
					'color',
					'if_id',
					'if_val',
					'if_show',
					'if_hide',
					'if_operator',
					'if_logic',
					'delete'
					)
	),
	'ifhidden' => array(
		'name' 	=> __('Condition Hidden','wpcc'),
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'if_id',
					'list',
					'if_operator',
					'action',
					'default',
					'data',
					'exclude',
					'mail_show',
					'delete'
					)
	),
	'armtc' => array(
		'name' 	=> __('Arithmetic Function','wpcc'),
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'armtc_fn',
					'delete'
					)
	),
	'result_fields' => array(
		'name' 	=> __('Result Fields','wpcc'),
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'action',
					'rf_fields',
					'default',
					'datarf',
					'exclude',
					'mail_show',
					'delete'
					)
	),
	'custom_fields' => array(
		'name' 	=> __('Custom Fields Post','wpcc'),
		'child' => array(
					'title',
					'color',
					'signto',
					'signaf',
					'singular_id',
					'singular_key',
					'default',
					'validation',
					'data',
					'exclude',
					'hidden_display',
					'mail_show',
					'delete'
					)
	)
);

$wpcc_name_field = array(
	'title' 				=> __('Title','wpcc'),
	'color' 				=> __('Color','wpcc'),
	'signto' 				=> __('Sign to','wpcc'),
	'signaf' 				=> __('Sign after','wpcc'),
	'default' 				=> __('Default','wpcc'),
	'maxchar' 				=> __('Max Char','wpcc'),
	'placeholder' 			=> __('Placeholder','wpcc'),
	'price' 				=> __('Price','wpcc'),
	'action' 				=> __('Action','wpcc'),
	'text' 					=> __('Text','wpcc'),
	'list' 					=> __('List','wpcc'),
	'exclude' 				=> __('Field involved in the calculations?','wpcc'),
	'datadate' 				=> __('Data','wpcc'),
	'data' 					=> __('Data','wpcc'),
	'datarf' 				=> __('Data','wpcc'),
	'validation' 			=> __('Validation','wpcc'),
	'hidden_display' 		=> __('Visually hide a field?','wpcc'),
	'sess_calc_id' 			=> __('ID calculator','wpcc'),
	'sess_calc_results' 	=> __('ID field or the sum','wpcc'),
	'jq_id' 				=> __('ID field','wpcc'),
	'if_id' 				=> __('ID fields for comparison','wpcc'),
	'if_val' 				=> __('The field value for the condition','wpcc'),
	'if_show' 				=> __('Which fields are displayed','wpcc'),
	'if_hide' 				=> __('What is hidden','wpcc'),
	'if_operator' 			=> __('IF Operator','wpcc'),
	'if_logic' 				=> __('IF Logic','wpcc'),
	'armtc_fn' 				=> __('What is the function use','wpcc'),
	'slider_min' 			=> __('The minimum value of','wpcc'),
	'slider_max' 			=> __('Maximum','wpcc'),
	'slider_step' 			=> __('Step','wpcc'),
	'slider_position' 		=> __('Position','wpcc'),
	'rf_fields'				=> __('ID list of fields separated by commas','wpcc'),
	'mail_show'				=> __('Display in the email body','wpcc'),
	'singular_id'			=> __('ID post','wpcc'),
	'singular_key'			=> __('Custom fields key','wpcc'),
	'date'					=> __('Date default','wpcc'),
	'datemin'				=> __('Date min','wpcc'),
	'datemax'				=> __('Date max','wpcc'),
	'delete'				=> __('Delete field','wpcc')
);

function wpcc_cache_field($wpcc_query='', $field_id='') {
	foreach($wpcc_query as $wpcc_row)
	{
		if($wpcc_row->wpcc_field == $field_id)
		{
			$wpcc_value [$wpcc_row->wpcc_type] = $wpcc_row->wpcc_value;
		}
	}
	return $wpcc_value;
}
	
/* wpcc row add and edit */
function wpcc_row_field($type, $wpcc_query='', $field_id='') {
	global $wpdb, $wpcc_DB, $wpcc_type_field, $wpcc_name_field;
	
	$wpcc_id 	= intval($_GET['wpcc_id']);
	$wpcc_value = wpcc_cache_field($wpcc_query, $field_id);
	
	/* Option Hide Value */
	$select_exclude_arr = array(
		'1' 	=> __('Yes','wpcc'),
		'2' 	=> __('No','wpcc')
	);
	$select_exclude = '';
	foreach($select_exclude_arr as $select_exclude_k => $select_exclude_v)
	{
		$select_exclude .= '<option value="'.$select_exclude_k.'" '.(($select_exclude_k == $wpcc_value['exclude'])?'selected':'').'>'.$select_exclude_v.'</option>';
	}
	/* Option Hide Display Value */
	$select_hidden_display_arr = array(
		'0' 	=> __('No','wpcc'),
		'1' 	=> __('Yes, add display: none','wpcc')
	);
	$select_hidden_display = '';
	foreach($select_hidden_display_arr as $select_hidden_display_k => $select_hidden_display_v)
	{
		$select_hidden_display .= '<option value="'.$select_hidden_display_k.'" '.(($select_hidden_display_k == $wpcc_value['hidden_display'])?'selected':'').'>'.$select_hidden_display_v.'</option>';
	}
	/* Option Armtc Value */
	$select_armtc_arr = array(
		'bcsqrt' 	=> __('The square root of the number sqrt($number)','wpcc'),
		'bcpow' 	=> __('Modular power of pow ($number, $exp)','wpcc'),
		'round' 	=> __('Rounding ROUND() function 0 characters. Example: 4 = 3.7, 3.1 = 3', 'wpcc'),
		'ceil' 		=> __('Rounding function CEIL(). Example: 4 = 3.7, 3.1 = 4', 'wpcc'),
		'floor' 	=> __('Rounding function FLOOR(). Example: 3.7 = 3, 3.1 = 3', 'wpcc')
	);
	$select_armtc = '';
	foreach($select_armtc_arr as $select_armtc_k => $select_armtc_v)
	{
		$select_armtc .= '<option value="'.$select_armtc_k.'" '.(($select_armtc_k == $wpcc_value['armtc_fn'])?'selected':'').'>'.$select_armtc_v.'</option>';
	}
	/* Option Slider Value */
	$select_slider_arr = array(
		'horizontal'	=> __('Horizontal','wpcc'),
		'vertical' 		=> __('Vertical','wpcc')
	);
	$select_slider = '';
	foreach($select_slider_arr as $select_slider_k => $select_slider_v)
	{
		$select_slider .= '<option value="'.$select_slider_k.'" '.(($select_slider_k == $wpcc_value['slider_position'])?'selected':'').'>'.$select_slider_v.'</option>';
	}
	/* Option If Operator Value */
	$select_if_operator_arr = array(
		'=='		=> '==',
		'<'			=> '<',
		'<='		=> '<=',
		'>'			=> '>',
		'>='		=> '>=',
		'between' 	=> __('Between','wpcc'),
		'notempty' 	=> __('Not empty','wpcc')
	);
	$select_if_operator = '';
	foreach($select_if_operator_arr as $select_if_operator_k => $select_if_operator_v)
	{
		$select_if_operator .= '<option value="'.$select_if_operator_k.'" '.(($select_if_operator_k == $wpcc_value['if_operator'])?'selected':'').'>'.$select_if_operator_v.'</option>';
	}
	/* Option If Logic Value */
	$select_if_logic_arr = array(
		'ifelse'	=> 'IF {} ELSE {}',
		'if'		=> 'IF'
	);
	$select_if_logic = '';
	foreach($select_if_logic_arr as $select_if_logic_k => $select_if_logic_v)
	{
		$select_if_logic .= '<option value="'.$select_if_logic_k.'" '.(($select_if_logic_k == $wpcc_value['if_logic'])?'selected':'').'>'.$select_if_logic_v.'</option>';
	}
	/* Option Validation */
	$select_validation_arr = array(
		''						=> '---',
		'only_numbers'			=> __('Only numbers','wpcc'),
		'only_numbers_one_dot'	=> __('Only numbers and one dot','wpcc')
	);
	$select_validation = '';
	foreach($select_validation_arr as $select_validation_k => $select_validation_v)
	{
		$select_validation .= '<option value="'.$select_validation_k.'" '.(($select_validation_k == $wpcc_value['validation'])?'selected':'').'>'.$select_validation_v.'</option>';
	}
	/* Option Data */
	$select_data_arr = array(
		'data'				=> __('Findings','wpcc'),
		'data_count'		=> __('Number of characters','wpcc'),
		'data_date_time'	=> __('Date','wpcc')
	);
	$select_data = '';
	foreach($select_data_arr as $select_data_k => $select_data_v)
	{
		$select_data .= '<option value="'.$select_data_k.'" '.(($select_data_k == $wpcc_value['data'])?'selected':'').'>'.$select_data_v.'</option>';
	}
	/* Option Data Date*/
	$select_datadate_arr = array(
		'unixtime'	=> __('Date','wpcc'),
		'string'	=> __('As a string','wpcc')		
	);
	$select_datadate = '';
	foreach($select_datadate_arr as $select_datadate_k => $select_datadate_v)
	{
		$select_datadate .= '<option value="'.$select_datadate_k.'" '.(($select_datadate_k == $wpcc_value['datadate'])?'selected':'').'>'.$select_datadate_v.'</option>';
	}
	/* Option Data RF */
	$select_datarf_arr = array(
		'data'				=> __('Findings','wpcc'),
		'data_count'		=> __('Number of characters','wpcc'),
		'data_date'			=> __('Date','wpcc'),
		'data_count_day'	=> __('Number of days','wpcc'),
		'data_day_number'	=> __('Day number','wpcc'),
		'data_month'		=> __('Month','wpcc'),
		'data_year'			=> __('Year','wpcc')
	);
	$select_datarf = '';
	foreach($select_datarf_arr as $select_datarf_k => $select_datarf_v)
	{
		$select_datarf .= '<option value="'.$select_datarf_k.'" '.(($select_datarf_k == $wpcc_value['datarf'])?'selected':'').'>'.$select_datarf_v.'</option>';
	}
	/* Option Mail Show Body */
	$select_mail_show_arr = array(
		'yes_if_condition_not_excluded'		=> __('Yes, if the condition is not excluded.', 'wpcc'),
		'no'								=> __('No','wpcc')
	);
	$select_mail_show = '';
	foreach($select_mail_show_arr as $select_mail_show_k => $select_mail_show_v)
	{
		$select_mail_show .= '<option value="'.$select_mail_show_k.'" '.(($select_mail_show_k == $wpcc_value['mail_show'])?'selected':'').'>'.$select_mail_show_v.'</option>';
	}
	
	/* List */
	if(wpcc_check_list($wpcc_value['list']))
	{
		$wpcc_value_list_arr = unserialize(wpcc_check_list($wpcc_value['list']));
		foreach($wpcc_value_list_arr AS $wpcc_value_list_k => $wpcc_value_list_v)
		{
			$wpcc_value_list .= '
		<div class="list_row" data-id="'.$wpcc_value_list_k.'">
			<input type="text" name="wpcc_fields['.$field_id.'][list]['.$wpcc_value_list_k.'][val]" value="'.wpcc_esc_attr($wpcc_value_list_v['val']).'" class="list_row_val">
			<input type="text" name="wpcc_fields['.$field_id.'][list]['.$wpcc_value_list_k.'][txt]" value="'.wpcc_esc_attr($wpcc_value_list_v['txt']).'" class="list_row_txt">
			<input type="text" name="wpcc_fields['.$field_id.'][list]['.$wpcc_value_list_k.'][img]" value="'.wpcc_esc_attr($wpcc_value_list_v['img']).'" class="list_row_img wpcc_media_upload" placeholder="http://">
			<div class="jq_list_remove">x</div>
			<div class="clear"></div>
		</div>
			';
		}
	}
	else
	{
			$wpcc_value_list .= '
		<div class="list_row" data-id="0">
			<input type="text" name="wpcc_fields['.$field_id.'][list][0][val]" value="" class="list_row_val">
			<input type="text" name="wpcc_fields['.$field_id.'][list][0][txt]" value="" class="list_row_txt">
			<input type="text" name="wpcc_fields['.$field_id.'][list][0][img]" value="" class="list_row_img wpcc_media_upload" placeholder="http://">
			<div class="jq_list_remove">x</div>
			<div class="clear"></div>
		</div>
			';
	}
	
	$child['title'] 			= '<div class="name">'. $wpcc_name_field['title'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][title]" class="input" value="'.wpcc_esc_attr($wpcc_value['title']).'"><br><div class="clear"></div>';
	$child['color'] 			= '<div class="name">'. $wpcc_name_field['color'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][color]" class="input jq_color_picker" value="'.wpcc_esc_attr($wpcc_value['color']).'"><br><div class="clear"></div>';
	$child['signto'] 			= '<div class="name">'. $wpcc_name_field['signto'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][signto]" class="input" value="'.wpcc_esc_attr($wpcc_value['signto']).'"><br><div class="clear"></div>';
	$child['signaf'] 			= '<div class="name">'. $wpcc_name_field['signaf'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][signaf]" class="input" value="'.wpcc_esc_attr($wpcc_value['signaf']).'"><br><div class="clear"></div>';
	$child['default'] 			= '<div class="name">* '. $wpcc_name_field['default'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][default]" class="input" value="'.wpcc_esc_attr($wpcc_value['default']).'" placeholder="'. __('Required','wpcc') .'"><br><div class="clear"></div>';
	$child['maxchar'] 			= '<div class="name">'. $wpcc_name_field['maxchar'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][maxchar]" class="input" value="'.wpcc_esc_attr($wpcc_value['maxchar']).'"><br><div class="clear"></div>';
	$child['placeholder'] 		= '<div class="name">'. $wpcc_name_field['placeholder'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][placeholder]" class="input" value="'.wpcc_esc_attr($wpcc_value['placeholder']).'"><br><div class="clear"></div>';
	$child['price'] 			= '<div class="name">'. $wpcc_name_field['price'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][price]" class="input" value="'.wpcc_esc_attr($wpcc_value['price']).'"><br><div class="clear"></div>';
	$child['action'] 			= '<div class="name">'. $wpcc_name_field['action'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][action]" class="input" value="'.wpcc_esc_attr($wpcc_value['action']).'"><br><div class="clear"></div>';
	$child['text'] 				= '<div class="name">'. $wpcc_name_field['text'] .'</div> <textarea name="wpcc_fields['.$field_id.'][text]" class="textarea">'.wpcc_esc_attr($wpcc_value['text']).'</textarea><br><div class="clear"></div>';
	
	$child['list'] 				= '<div class="name">'. $wpcc_name_field['list'] .':<br> <small><a href="#" class="jq_list_add" data-fid="'.$field_id.'">'. __('Add row','wpcc') .'</a></small></div> 
	<div class="list_rows list_rows_'.$type.'">
		<div class="list_title">
			'. (($type != 'ifhidden') ? '<div class="desc">'. __('Value','wpcc') .'</div>' : '<div class="desc">'. __('Entrance','wpcc') .'</div>' ) .'
			'. (($type != 'ifhidden') ? '<div class="desc">'. __('Text','wpcc') .'</div>' : '<div class="desc">'. __('Result','wpcc') .'</div>' ) .'
			'. (($type != 'ifhidden' AND $type != 'select') ? '<div class="desc">'. __('Images','wpcc') .'</div>' : '' ) .'
			<div class="clear"></div>
		</div>
		'.$wpcc_value_list.'
	</div>
	<br><div class="clear"></div>';
	
	$child['exclude'] 			= '<div class="name">'. $wpcc_name_field['exclude'] .':</div> <select name="wpcc_fields['.$field_id.'][exclude]" class="select">'.$select_exclude.'</select><br><div class="clear"></div>';
	$child['hidden_display'] 	= '<div class="name">'. $wpcc_name_field['hidden_display'] .':</div> <select name="wpcc_fields['.$field_id.'][hidden_display]" class="select">'.$select_hidden_display.'</select><br><div class="clear"></div>';
	$child['validation'] 		= '<div class="name">'. $wpcc_name_field['validation'] .':</div> <select name="wpcc_fields['.$field_id.'][validation]" class="select">'.$select_validation.'</select><br><div class="clear"></div>';
	$child['data'] 				= '<div class="name">'. $wpcc_name_field['data'] .':</div> <select name="wpcc_fields['.$field_id.'][data]" class="select">'.$select_data.'</select><br><div class="clear"></div>';
	$child['datarf'] 			= '<div class="name">'. $wpcc_name_field['datarf'] .':</div> <select name="wpcc_fields['.$field_id.'][datarf]" class="select">'.$select_datarf.'</select><br><div class="clear"></div>';
	$child['datadate'] 			= '<div class="name">'. $wpcc_name_field['datadate'] .':</div> <select name="wpcc_fields['.$field_id.'][datadate]" class="select">'.$select_datadate.'</select><br><div class="clear"></div>';
	
	$child['sess_calc_id'] 		= '<div class="name">'. $wpcc_name_field['sess_calc_id'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][sess_calc_id]" class="input" value="'.wpcc_esc_attr($wpcc_value['sess_calc_id']).'"><br><div class="clear"></div>';
	$child['sess_calc_results'] = '<div class="name">'. $wpcc_name_field['sess_calc_results'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][sess_calc_results]" class="input" value="'.wpcc_esc_attr($wpcc_value['sess_calc_results']).'"><br><div class="clear"></div>';
	
	$child['jq_id'] 			= '<div class="name">'. $wpcc_name_field['jq_id'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][jq_id]" class="input" value="'.wpcc_esc_attr($wpcc_value['jq_id']).'"><br><div class="clear"></div>';
	
	$child['if_id'] 			= '<div class="name">'. $wpcc_name_field['if_id'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][if_id]" class="input" value="'.wpcc_esc_attr($wpcc_value['if_id']).'"><br><div class="clear"></div>';
	$child['if_val'] 			= '<div class="name">'. $wpcc_name_field['if_val'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][if_val]" class="input" value="'.wpcc_esc_attr($wpcc_value['if_val']).'"><br><div class="clear"></div>';
	$child['if_show'] 			= '<div class="name">'. $wpcc_name_field['if_show'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][if_show]" class="input" value="'.wpcc_esc_attr($wpcc_value['if_show']).'" placeholder="1,2,3,4"><br><div class="clear"></div>';
	$child['if_hide'] 			= '<div class="name">'. $wpcc_name_field['if_hide'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][if_hide]" class="input" value="'.wpcc_esc_attr($wpcc_value['if_hide']).'" placeholder="1,2,3,4"><br><div class="clear"></div>';
	$child['if_operator'] 		= '<div class="name">'. $wpcc_name_field['if_operator'] .':</div> <select name="wpcc_fields['.$field_id.'][if_operator]" class="select">'.$select_if_operator.'</select><br><div class="clear"></div>';
	$child['if_logic'] 			= '<div class="name">'. $wpcc_name_field['if_logic'] .':</div> <select name="wpcc_fields['.$field_id.'][if_logic]" class="select">'.$select_if_logic.'</select><br><div class="clear"></div>';
	
	$child['armtc_fn'] 			= '<div class="name">'. $wpcc_name_field['armtc_fn'] .':</div> <select name="wpcc_fields['.$field_id.'][armtc_fn]" class="select">'.$select_armtc.'</select><br><div class="clear"></div>';
	
	$child['rf_fields'] 		= '<div class="name">'. $wpcc_name_field['rf_fields'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][rf_fields]" class="input" value="'.wpcc_esc_attr($wpcc_value['rf_fields']).'"><br><div class="clear"></div>';
	
	$child['singular_id'] 		= '<div class="name">'. $wpcc_name_field['singular_id'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][singular_id]" class="input" value="'.wpcc_esc_attr($wpcc_value['singular_id']).'"><br><div class="clear"></div>';
	$child['singular_key'] 		= '<div class="name">'. $wpcc_name_field['singular_key'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][singular_key]" class="input" value="'.wpcc_esc_attr($wpcc_value['singular_key']).'"><br><div class="clear"></div>';
	
	$child['slider_min'] 		= '<div class="name">'. $wpcc_name_field['slider_min'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][slider_min]" class="input" value="'.wpcc_esc_attr($wpcc_value['slider_min']).'"><br><div class="clear"></div>';
	$child['slider_max'] 		= '<div class="name">'. $wpcc_name_field['slider_max'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][slider_max]" class="input" value="'.wpcc_esc_attr($wpcc_value['slider_max']).'"><br><div class="clear"></div>';
	$child['slider_step'] 		= '<div class="name">'. $wpcc_name_field['slider_step'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][slider_step]" class="input" value="'.wpcc_esc_attr($wpcc_value['slider_step']).'"><br><div class="clear"></div>';
	$child['slider_position'] 	= '<div class="name">'. $wpcc_name_field['slider_position'] .':</div> <select name="wpcc_fields['.$field_id.'][slider_position]" class="select">'.$select_slider.'</select><br><div class="clear"></div>';
	
	$child['date'] 				= '<div class="name">'. $wpcc_name_field['date'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][date]" class="input" value="'.wpcc_esc_attr($wpcc_value['date']).'" placeholder="'. __('+1d +1w +1m +1y','wpcc') .'"><br><div class="clear"></div>';
	$child['datemin'] 			= '<div class="name">'. $wpcc_name_field['datemin'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][datemin]" class="input" value="'.wpcc_esc_attr($wpcc_value['datemin']).'" placeholder="'. __('+1d +1w +1m +1y','wpcc') .'"><br><div class="clear"></div>';
	$child['datemax'] 			= '<div class="name">'. $wpcc_name_field['datemax'] .':</div> <input type="text" name="wpcc_fields['.$field_id.'][datemax]" class="input" value="'.wpcc_esc_attr($wpcc_value['datemax']).'" placeholder="'. __('+1d +1w +1m +1y','wpcc') .'"><br><div class="clear"></div>';
	
	$child['delete'] 			= '<div class="name"><label>'. $wpcc_name_field['delete'] .' &nbsp;&nbsp;<input type="checkbox" name="wpcc_fields['.$field_id.'][delete]" value="1"></label> </div> <br><div class="clear"></div>';
	
	if(get_option('wpcc_mail_check_'.$wpcc_id) == 1)
	{
		$child['mail_show'] 		= '<div class="name">'. $wpcc_name_field['mail_show'] .':</div> <select name="wpcc_fields['.$field_id.'][mail_show]" class="select">'.$select_mail_show.'</select><br><div class="clear"></div>';
	}
	
	$child_select = $wpcc_type_field[$type]['child'];

	foreach($child_select AS $child_row)
	{
		$return .= $child[$child_row];
		/* If there is no field */
		if(!array_key_exists($child_row, $wpcc_value))
		{
			$wpdb->query(
				$wpdb->prepare("
				INSERT INTO $wpcc_DB
				(
					wpcc_id, 
					wpcc_field, 
					wpcc_type, 
					wpcc_value, 
					wpcc_order
				) 
				VALUES (
					'%d', 
					'%d', 
					'%s', 
					'%s', 
					'%d'
				)
				", 
					$wpcc_id, 
					$field_id, 
					$child_row, 
					'', 
					'0'
				)
			);
		}
	}
	return $return;
}

function wpcc_arr_options($wpcc_id = '') {
	return array(
		'wpcc_submit_'.$wpcc_id,
		'wpcc_show_result_'.$wpcc_id,
		'wpcc_field_results_'.$wpcc_id,
		'wpcc_action_'.$wpcc_id,
		'wpcc_scroll_res_'.$wpcc_id,
		'wpcc_mail_check_'.$wpcc_id,
		'wpcc_mail_subject_'.$wpcc_id,
		'wpcc_mail_emailto_'.$wpcc_id,
		'wpcc_mail_form_fields_'.$wpcc_id,
		'wpcc_mail_validation_'.$wpcc_id,
		'wpcc_mail_text_adm_'.$wpcc_id,
		'wpcc_mail_text_'.$wpcc_id,
		'wpcc_mail_text_success_'.$wpcc_id,
		'wpcc_mail_copies_user_'.$wpcc_id,
		'wpcc_mail_text_footer_'.$wpcc_id,
		'wpcc_mail_result_string_'.$wpcc_id,
		'wpcc_theme_'.$wpcc_id,
		'wpcc_enable_script_'.$wpcc_id
	);
}

function wpcc_all_calc() {
	global $wpdb, $wpcc_DB;
	$return = array();
	$res = $wpdb->get_results("SELECT wpcc_id, wpcc_value FROM $wpcc_DB WHERE wpcc_type = 'cat' ORDER BY wpcc_id");
	if(count($res) > '0')
	{
		foreach($res as $row)
		{
			$return [$row->wpcc_id]= array(
				'wpcc_id' 	=> $row->wpcc_id,
				'wpcc_name' => $row->wpcc_value
			);
		}
	}
	return $return;
}

function wpcc_check_list($data='') {
	if(!is_serialized($data) AND $data != '')
	{
		$return = array();
		$arr 	= preg_split('/\\r\\n?|\\n/', $data);
		foreach ($arr AS $arr_k => $arr_v)
		{
			$arr_e = explode(':', $arr_v);
			$return [$arr_k]['val'] = $arr_e[0];
			$return [$arr_k]['txt'] = $arr_e[1];
			$return [$arr_k]['img'] = '';
		}
		return serialize($return);
	}
	elseif(is_serialized($data))
	{
		return $data;
	}
	else
	{
		return false;
	}
}

function wpcc_date_conv($date='', $conv='', $format='Y-m-d') {
	if($date == '')
		$date = date('Y-m-d');
	
	$conv_js_to_php = array(
		'd' => 'day',
		'w' => 'week',
		'm' => 'month',
		'y' => 'year'
	);
	
	$convert = strtr($conv, $conv_js_to_php);
	return date($format, strtotime($date.' '.$convert));
}
			
function wpcc_default_field_res() {
	$before = __('Result','wpcc');
	$after 	= __('$','wpcc');
	return array(
		'1' 		=> array(
			'before' 	=> get_option('wpcc_text_to_'.$wpcc_id, $before),
			'after' 	=> get_option('wpcc_text_af_'.$wpcc_id, $after),
			'minamount' => '',
			'round' 	=> get_option('wpcc_round_sum_'.$wpcc_id, 'none'),
			'formula' 	=> '$wpcc_sum'
		)
	);
}

function wpcc_option_array($option) {
	$get = get_option($option);
	return is_array($get) ? $get : array();
}

function wpcc_field_results($wpcc_id) {
	$wpcc_field_result_upd = array();
	if(isset($_POST['wpcc_field_results_'.$wpcc_id]))
	{
		foreach($_POST['wpcc_field_results_'.$wpcc_id] AS $wpcc_field_result_upd_k => $wpcc_field_result_upd_v)
		{
			if($wpcc_field_result_upd_v['delete'] == 1 OR ($wpcc_field_result_upd_v['formula'] == '' AND $wpcc_field_result_upd_k != 1))
			{
				unset($wpcc_field_result_upd[$wpcc_field_result_upd_k]);
			}
			else
			{
				if($wpcc_field_result_upd_k == 1 AND $wpcc_field_result_upd_v['formula'] == '')
				{
					$wpcc_field_result_upd_v['formula'] = '$wpcc_sum';
				}
				$wpcc_field_result_upd[$wpcc_field_result_upd_k] = $wpcc_field_result_upd_v;
			}
			
		}
		update_option('wpcc_field_results_'.$wpcc_id, $wpcc_field_result_upd);
	}
	return isset($_POST['wpcc_field_results_'.$wpcc_id]) ? $wpcc_field_result_upd : get_option('wpcc_field_results_'.$wpcc_id, wpcc_default_field_res());
}

function wpcc_setting() {
	global $wpdb, $wpcc_DB, $wpcc_type_field;
	
	$wpcc_section 	= wpcc_esc_attr($_GET['section']);
	$wpcc_id 		= intval($_GET['wpcc_id']);
	$wpcc_delete 	= intval($_GET['delete']);
	$wpcc_all_arr 	= array();
	
	/* Check DB */
	$wpcc_check_table = $wpdb->get_results("SHOW TABLES LIKE '$wpcc_DB'");
	
	/* Install */
	if($wpcc_section == 'install')
	{
		$wpcc_create_tb = $wpdb->query("
		CREATE TABLE IF NOT EXISTS $wpcc_DB (
		id INT NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		wpcc_id INT NOT NULL DEFAULT '0',
		wpcc_field INT NOT NULL DEFAULT '0',
		wpcc_type VARCHAR(255) NOT NULL,
		wpcc_value TEXT NOT NULL,
		wpcc_order INT NOT NULL DEFAULT '0'
		) ENGINE=MyISAM CHARACTER SET=utf8;
		");
		if($wpdb->last_error != '')
		{
			$wpcc_updated .= '<p>'. __('Error creating table','wpcc') .' '.$wpcc_DB.':<br><b>'.$wpdb->last_error.'</b></p>';
		}
		else
		{
			$wpcc_updated .= '<p>'. __('The new table','wpcc') .' '.$wpcc_DB.' '. __('successfully created.','wpcc') .'</p>';
			$wpcc_install  = true;
		}
	}
	
	/* Add New Calculator */
	if(isset($_POST['wpcc_new']))
	{
		$wpcc_max_wpcc_id = $wpdb->get_row("
			SELECT MAX(wpcc_id) AS wpcc_id_max
			FROM $wpcc_DB
		");
		$wpcc_new_id = $wpcc_max_wpcc_id->wpcc_id_max + 1;
				
		$wpdb->query(  
			$wpdb->prepare("
			INSERT INTO $wpcc_DB
			(
				wpcc_id, 
				wpcc_field, 
				wpcc_type, 
				wpcc_value, 
				wpcc_order
			) 
			VALUES (
				'%d', 
				'%d', 
				'%s', 
				'%s', 
				'%d'
			)
			", 
				$wpcc_new_id, 
				'0', 
				'cat', 
				wpcc_esc_attr($_POST['wpcc_value']), 
				'0'
			)
		);
		
		/* Setting Calc */
		update_option( 'wpcc_submit_'.$wpcc_new_id, __('Calculate','wpcc') );
		update_option( 'wpcc_show_result_'.$wpcc_new_id, '1' );
		update_option( 'wpcc_action_'.$wpcc_new_id, '0' );
		update_option( 'wpcc_scroll_res_'.$wpcc_new_id, '1' );
		update_option( 'wpcc_field_results_'.$wpcc_new_id, wpcc_default_field_res());
		update_option( 'wpcc_theme_'.$wpcc_new_id, 'default' );
		update_option( 'wpcc_enable_script_'.$wpcc_new_id, array() );
		
		update_option( 'wpcc_mail_check_'.$wpcc_new_id, '2' );
		update_option( 'wpcc_mail_subject_'.$wpcc_new_id, __('The calculation of the calculator from the user','wpcc') );
		update_option( 'wpcc_mail_emailto_'.$wpcc_new_id, get_option('admin_email') );
		update_option( 'wpcc_mail_form_fields_'.$wpcc_new_id, array() );
		update_option( 'wpcc_mail_validation_'.$wpcc_new_id, array() );
		update_option( 'wpcc_mail_text_adm_'.$wpcc_new_id, __('Calculating the cost of site','wpcc') );
		update_option( 'wpcc_mail_text_'.$wpcc_new_id, __('Send an administrator account?','wpcc') );
		update_option( 'wpcc_mail_text_success_'.$wpcc_new_id, __('Thank you! Your payment is sent! We will contact you soon.','wpcc') );
		update_option( 'wpcc_mail_copies_user_'.$wpcc_new_id, '2' );
		update_option( 'wpcc_mail_text_footer_'.$wpcc_new_id, '1' );
		update_option( 'wpcc_mail_result_string_'.$wpcc_new_id, '1' );

		$wpcc_new_result = '
		<div class="wpcc_updated"> 
			<p>'. __('Calculator added', 'wpcc') .', <a href="admin.php?page=wpcc&wpcc_id='.$wpcc_new_id.'">'. __('go to settings', 'wpcc') .'</a></p>
		</div>
		';
	}
	/* Edit Name Calculator */
	if(isset($_POST['wpcc_update']))
	{
		$wpdb->query(  
			$wpdb->prepare("
			UPDATE $wpcc_DB SET 
				wpcc_value = '%s'
			WHERE 
				wpcc_id = '%d'
			AND 
				wpcc_type = 'cat'
			", 
			wpcc_esc_attr($_POST['wpcc_value']), 
			$wpcc_id
			)
		);
		$wpcc_updated .= '<p>'. __('Action completed','wpcc') .'</p>';
	}
	/* Del Calc */
	if($wpcc_delete > '0') {
		$wpdb->query("
		DELETE FROM $wpcc_DB WHERE
			wpcc_id = '$wpcc_delete'
		");
		if($wpcc_id == '0')
		{
			$wpcc_delete_option = wpcc_arr_options($wpcc_delete);
			foreach ($wpcc_delete_option as $wpcc_delete_option_row) {
				delete_option($wpcc_delete_option_row);
			}
			$wpcc_updated .= '<p>'. __('Calculator deleted','wpcc') .'</p>';
		}
	}
	
	/* Add/Update Row */
	if(isset($_POST['wpcc_add_field']))
	{
		$wpcc_max_var = $wpdb->get_row("
			SELECT MAX(wpcc_order) AS wpcc_order_max, MAX(wpcc_field) AS wpcc_field_max
			FROM $wpcc_DB
			WHERE wpcc_id = '$wpcc_id'
		");
		$wpcc_order = $wpcc_max_var->wpcc_order_max + 1;
		$wpcc_field = $wpcc_max_var->wpcc_field_max + 1;
		
		$wpdb->query(
			$wpdb->prepare("
			INSERT INTO $wpcc_DB
			(
				wpcc_id, 
				wpcc_field, 
				wpcc_type, 
				wpcc_value, 
				wpcc_order
			) 
			VALUES (
				'%d', 
				'%d', 
				'%s', 
				'%s', 
				'%d'
			)
			", 
				$wpcc_id, 
				$wpcc_field, 
				'field_type', 
				wpcc_esc_attr($_POST['wpcc_value']), 
				$wpcc_order
			)
		);
		
		$cild_arr = $wpcc_type_field[$_POST['wpcc_value']]['child'];
		foreach($cild_arr AS $child_row)
		{
			$wpdb->query(
				$wpdb->prepare("
				INSERT INTO $wpcc_DB
				(
					wpcc_id, 
					wpcc_field, 
					wpcc_type, 
					wpcc_value, 
					wpcc_order
				) 
				VALUES (
					'%d', 
					'%d', 
					'%s', 
					'%s', 
					'%d'
				)
				", 
					$wpcc_id, 
					$wpcc_field, 
					$child_row, 
					'', 
					$wpcc_order
				)
			);
		}
	}
	elseif(isset($_POST['wpcc_update_field']))
	{
		/* Update Child */
		foreach($_POST['wpcc_fields'] AS $field_id => $field_values)
		{
			if($field_values['delete'] == '1')
			{
				$wpdb->query("
				DELETE FROM $wpcc_DB WHERE
					wpcc_id = '$wpcc_id'
				AND
					wpcc_field = '$field_id'
				");
			}
			else
			{
				foreach($field_values AS $field_name => $field_val)
				{
					if($field_name == 'list')
						$field_val = serialize($field_val);
					
					$wpdb->query(  
						$wpdb->prepare("
						UPDATE $wpcc_DB SET 
							wpcc_value = '%s'
						WHERE 
							wpcc_id = '%d'
						AND
							wpcc_field = '%d'
						AND
							wpcc_type = '%s'
						", 
						$field_val,
						$wpcc_id,
						$field_id,
						$field_name
						)
					);
				}
			}
		}
		$wpcc_updated .= '<p>'. __('Action completed','wpcc') .'</p>';
		
	}
	/* Sortable */
	if(isset($_POST['wpcc_sortable']))
	{
		if (isset($_POST['wpcc_sortable_str']) AND $_POST['wpcc_sortable_str'] != '')
		{
			$wpcc_sortable_str 		= preg_replace ('/[^0-9,]/', '', $_POST['wpcc_sortable_str']);
			$wpcc_sortable_arr 		= explode(",", $wpcc_sortable_str);
			$wpcc_sortable_count 	= count($wpcc_sortable_arr);
			for($i_sortable = 0; $i_sortable < $wpcc_sortable_count; $i_sortable++)
			{
				$wpdb->query($wpdb->prepare("UPDATE $wpcc_DB SET wpcc_order = %d WHERE id = %d ", $i_sortable, $wpcc_sortable_arr[$i_sortable]));
			}
			$wpcc_updated .= '<p>'. __('The field order is saved','wpcc') .'</p>';
		}
	}
	/* Arr All Calc */
	$wpcc_all_arr = wpcc_all_calc();
?>
<div class="wrap wpcc_wrap">
	<div class="jq_wpcc_setting" data-plugin_url="<?php echo WPCC_PLUGIN_URL; ?>" data-wpcc_id="<?php echo $wpcc_id; ?>"></div>
	
	<h2>
		WPCC 3.6.5
		<a href="http://zetrider.ru/forum/viewforum.php?id=8" target="_blank" class="add-new-h2">README HERE</a>
	</h2>
	
	<div class="zetrider">
		<a href="http://zetrider.ru" target="_blank" class="site"><b>ZETRIDER</b><span>web developer</span></a>
		<a href="http://zetrider.ru/donate" target="_blank" class="thanks"></a>
	</div>
	
	<?php
	echo (($wpcc_updated != '')?'<div class="wpcc_updated wpcc_updated_top">'.$wpcc_updated.'</div>':'');
	if(count($wpcc_check_table) == '0' AND $wpcc_install != TRUE)
	{
		echo '<a href="admin.php?page=wpcc&section=install" class="button button-primary">'. __('Install calculator','wpcc') .'</a>';
	}
	else
	{
	?>
	<a href="admin.php?page=wpcc" class="button button-primary<?php echo (($wpcc_section == '' AND $wpcc_id == '0')?' active':''); ?>"><?php _e('List of calculators', 'wpcc'); ?></a>
	<a href="admin.php?page=wpcc&section=new" class="button button-primary<?php echo (($wpcc_section == 'new')?' active':''); ?>"><?php _e('New calculator', 'wpcc'); ?></a>
	<?php
	}
	?>
	<div class="clear"></div>
	<br>
	
<?php
if($wpcc_section == 'uninstall')
{
	if($_GET['action'] == 'all_options')
	{
		$wpcc_delete_res = $wpdb->get_col( "SELECT option_name FROM ".$wpdb->prefix."options WHERE option_name LIKE 'wpcc_%'" );
		foreach($wpcc_delete_res AS $wpcc_delete_row)
		{
			delete_option($wpcc_delete_row);
		}
	}
	elseif($_GET['action'] == 'option')
	{
		delete_option($_GET['name']);
	}
	elseif($_GET['action'] == 'table')
	{
		$wpdb->query( "DROP TABLE " . wpcc_esc_attr($_GET['name']) );
	}
	$wpcc_uninstall_table_old 	= $wpdb->get_results( "SHOW TABLES LIKE 'wp_wpcc'", ARRAY_N );
	$wpcc_uninstall_table 		= $wpdb->get_results( "SHOW TABLES LIKE '$wpcc_DB'", ARRAY_N );
	$wpcc_uninstall_options 	= $wpdb->get_col( "SELECT option_name FROM ".$wpdb->prefix."options WHERE option_name LIKE 'wpcc_%'" );
	$wpcc_unistall_check 		= count($wpcc_uninstall_table_old) + count($wpcc_uninstall_table) + count($wpcc_uninstall_options);
?>
	<h2><?php _e('Deleting records from the database plugin', 'wpcc'); ?></h2>
	<div class="wpcc_updated">
		<?php
		if($wpcc_unistall_check > 0)
		{
			echo '
			<p>
				'.__('<strong>Attention!</strong> These are removed forever!', 'wpcc').'
			</p>
			';
		}
		else
		{
			echo '
			<p>
				'.__('Data not found.', 'wpcc').'
			</p>
			';
		}
		?>
	</div>
	<hr>
<?php
	if(count($wpcc_uninstall_table_old) > 0)
	{
		echo '
		<div class="wpcc_uninstall">
		<a href="admin.php?page=wpcc&section=uninstall&action=table&name='.$wpcc_uninstall_table_old[0][0].'" class="button">'.__('Delete table', 'wpcc').'</a> '.__('Found the old version of the plugin table', 'wpcc').': <strong>'.$wpcc_uninstall_table_old[0][0].'</strong>
		</div>
		<hr>
		';
		
	}
	if(count($wpcc_uninstall_table) > 0)
	{
		echo '
		<div class="wpcc_uninstall">
		<a href="admin.php?page=wpcc&section=uninstall&action=table&name='.$wpcc_uninstall_table[0][0].'" class="button">'.__('Delete table', 'wpcc').'</a> '.__('Found table plugin', 'wpcc').': <strong>'.$wpcc_uninstall_table[0][0].'</strong>
		</div>
		<hr>
		';
	}
	if(count($wpcc_uninstall_options) > 0)
	{
		echo '
		<div class="wpcc_uninstall">
		<a href="admin.php?page=wpcc&section=uninstall&action=all_options" class="button">'.__('Remove all', 'wpcc').'</a>'.__('Found the following entries in the table', 'wpcc').' <strong>'.$wpdb->prefix.'options:</strong>
		</div>
		';
		foreach($wpcc_uninstall_options AS $wpcc_uninstall_option)
		{
			echo '
			<div class="wpcc_uninstall">
			<a href="admin.php?page=wpcc&section=uninstall&action=option&name='.$wpcc_uninstall_option.'" class="button">X</a>'.$wpcc_uninstall_option.'
			</div>
			';
		}
	}

}
elseif($wpcc_id > '0' AND is_array($wpcc_all_arr[$wpcc_id]))
{
?>
	<a href="admin.php?page=wpcc&wpcc_id=<?php echo $wpcc_id; ?>" class="button button-primary<?php echo (($wpcc_section == '')?' active':''); ?>"><?php _e('Constructor fields', 'wpcc'); ?></a>
	<a href="admin.php?page=wpcc&wpcc_id=<?php echo $wpcc_id; ?>&section=export" class="button button-primary<?php echo (($wpcc_section == 'export')?' active':''); ?>"><?php _e('Export formula', 'wpcc'); ?></a>
	<a href="admin.php?page=wpcc&wpcc_id=<?php echo $wpcc_id; ?>&section=import" class="button button-primary<?php echo (($wpcc_section == 'import')?' active':''); ?>"><?php _e('Import formula', 'wpcc'); ?></a>
	<a href="admin.php?page=wpcc&wpcc_id=<?php echo $wpcc_id; ?>&section=setting" class="button button-primary<?php echo (($wpcc_section == 'setting')?' active':''); ?>"><?php _e('Settings', 'wpcc'); ?></a>
	<br><br>
	<form class="form" method="POST">
		<b><?php _e('Shortcode', 'wpcc'); ?>:</b> [wpcc id="<?php echo $wpcc_id; ?>"]
		<br>
		<input type="hidden" name="wpdb_id" value="<?php echo $wpcc_id; ?>">
		<input type="hidden" name="wpcc_field" value="0">
		<input type="hidden" name="wpcc_type" value="cat">
		<input type="text" name="wpcc_value" class="input" value="<?php echo $wpcc_all_arr[$wpcc_id]['wpcc_name']; ?>" placeholder="<?php _e('Name calculator', 'wpcc'); ?>">
		<input type="hidden" name="wpcc_order" value="0">
		<input type="submit" name="wpcc_update" value="<?php _e('Change', 'wpcc'); ?>" class="button button-primary">
	</form>
<?php
	if($wpcc_section == 'setting')
	{
?>
		<br>
		
		<form method="post" action="options.php" class="wpcc_type" >
			<?php
			wp_nonce_field('update-options');
			$wpcc_submit_id 				= 'wpcc_submit_'.$wpcc_id;
			$wpcc_show_result_id 			= 'wpcc_show_result_'.$wpcc_id;
			$wpcc_mail_result_string_id 	= 'wpcc_mail_result_string_'.$wpcc_id;
			$wpcc_action_id 				= 'wpcc_action_'.$wpcc_id;
			$wpcc_scroll_res_id 			= 'wpcc_scroll_res_'.$wpcc_id;
			$wpcc_theme_id 					= 'wpcc_theme_'.$wpcc_id;
			$wpcc_enable_script_id 			= 'wpcc_enable_script_'.$wpcc_id;
			$wpcc_mail_check_id 			= 'wpcc_mail_check_'.$wpcc_id;
			$wpcc_mail_subject_id 			= 'wpcc_mail_subject_'.$wpcc_id;
			$wpcc_mail_emailto_id 			= 'wpcc_mail_emailto_'.$wpcc_id;
			$wpcc_mail_form_fields_id 		= 'wpcc_mail_form_fields_'.$wpcc_id;
			$wpcc_mail_validation_id 		= 'wpcc_mail_validation_'.$wpcc_id;
			$wpcc_mail_text_adm_id			= 'wpcc_mail_text_adm_'.$wpcc_id;
			$wpcc_mail_text_id 				= 'wpcc_mail_text_'.$wpcc_id;
			$wpcc_mail_text_success_id 		= 'wpcc_mail_text_success_'.$wpcc_id;
			$wpcc_mail_copies_user_id 		= 'wpcc_mail_copies_user_'.$wpcc_id;
			$wpcc_mail_text_footer_id 		= 'wpcc_mail_text_footer_'.$wpcc_id;
			?>
			
			<h3><?php _e('Settings calculator', 'wpcc'); ?></h3>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php _e('Button name', 'wpcc'); ?>:</th>
						<td><input type="text" name="<?php echo $wpcc_submit_id; ?>" class="input" value="<?php echo get_option($wpcc_submit_id); ?>"></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Show the results of calculations', 'wpcc'); ?>:</th>
						<td>
							<select name="<?php echo $wpcc_show_result_id; ?>" class="select">
								<option value="1"><?php _e('Yes', 'wpcc'); ?></option>
								<option value="2" <?php if(get_option($wpcc_show_result_id) == '2') { echo 'selected'; } ?>><?php _e('No', 'wpcc'); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Open calculator by pressing', 'wpcc'); ?>:</th>
						<td>
							<select name="<?php echo $wpcc_action_id; ?>" class="select">
								<option value="0"><?php _e('No', 'wpcc'); ?></option>
								<?php
								foreach($wpcc_all_arr as $wpcc_all_row)
								{
									echo '<option value="'.$wpcc_all_row['wpcc_id'].'" '.((get_option($wpcc_action_id) == $wpcc_all_row['wpcc_id'])?'selected':'').'>'.$wpcc_all_row['wpcc_name'].'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Auto scroll to the result', 'wpcc'); ?>:</th>
						<td>
							<select name="<?php echo $wpcc_scroll_res_id; ?>" class="select">
								<?php
								$wpcc_auto_scroll = array(
									'1' 		=> __('Yes', 'wpcc'),
									'2' 		=> __('No', 'wpcc')
								);
								foreach($wpcc_auto_scroll as $wpcc_auto_scroll_k => $wpcc_auto_scroll_v)
								{
									echo '<option value="'.$wpcc_auto_scroll_k.'" '.((get_option($wpcc_scroll_res_id) == $wpcc_auto_scroll_k)?'selected':'').'>'.$wpcc_auto_scroll_v.'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Design calculator', 'wpcc'); ?>:</th>
						<td>
							<select name="<?php echo $wpcc_theme_id;?>" class="select">
								<?php
								$wpcc_themes_arr = array(
									'default' 		=> __('Standard', 'wpcc'),
									'light_shadows' => __('The light from the shadows', 'wpcc'),
									'bulk' 			=> __('Bulk', 'wpcc'),
									'striped' 		=> __('Striped', 'wpcc'),
									'none' 			=> __('None theme', 'wpcc')
								);
								foreach($wpcc_themes_arr as $wpcc_themes_k => $wpcc_themes_v)
								{
									echo '<option value="'.$wpcc_themes_k.'" '.((get_option($wpcc_theme_id) == $wpcc_themes_k)?'selected':'').'>'.$wpcc_themes_v.'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Disable scripts', 'wpcc'); ?>:</th>
						<td>
						<?php
						$wpcc_enable_script 			= wpcc_option_array($wpcc_enable_script_id);
						$wpcc_enable_script_option 		= array(
							'slider' 		=> __('jQuery UI Slider', 'wpcc'),
							'datepicker' 	=> __('jQuery UI DatePicker', 'wpcc')
						);
						foreach($wpcc_enable_script_option AS $wpcc_enable_script_k => $wpcc_enable_script_v)
						{
							echo '<label><input type="checkbox" name="'.$wpcc_enable_script_id.'[]" value="'.$wpcc_enable_script_k.'" '. ((in_array($wpcc_enable_script_k, $wpcc_enable_script))?'checked':'').'> '.$wpcc_enable_script_v.'</label><br>';
						}
						?>
						</td>
					</tr>
				</tbody>
			</table>

			<h3><?php _e('Setting Mail', 'wpcc'); ?></h3>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php _e('Enable Send E-Mail', 'wpcc'); ?>:</th>
						<td>
							<select name="<?php echo $wpcc_mail_check_id; ?>" class="select">
								<option value="1"><?php _e('Yes', 'wpcc'); ?></option>
								<option value="2" <?php if(get_option($wpcc_mail_check_id) == '2') { echo 'selected'; } ?>><?php _e('No', 'wpcc'); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Subject', 'wpcc'); ?>:</th>
						<td><input type="text" name="<?php echo $wpcc_mail_subject_id; ?>" class="input" value="<?php echo wpcc_esc_attr(get_option($wpcc_mail_subject_id)); ?>"></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('For how to send E-Mail', 'wpcc'); ?>:</th>
						<td><input type="text" name="<?php echo $wpcc_mail_emailto_id; ?>" class="input" value="<?php echo wpcc_esc_attr(get_option($wpcc_mail_emailto_id)); ?>"></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Form fields', 'wpcc'); ?>:</th>
						<td>
						<?php
						$wpcc_mail_form_fields 			= wpcc_option_array($wpcc_mail_form_fields_id);
						$wpcc_mail_form_fields_option 	= array(
							'name' 		=> __('Hide Name', 'wpcc'),
							'email' 	=> __('Hide Email', 'wpcc'),
							'phone' 	=> __('Hide Phone', 'wpcc'),
							'comment' 	=> __('Hide Comment', 'wpcc')
						);
						foreach($wpcc_mail_form_fields_option AS $wpcc_mail_form_fields_k => $wpcc_mail_form_fields_v)
						{
							echo '<label><input type="checkbox" name="'.$wpcc_mail_form_fields_id.'[]" value="'.$wpcc_mail_form_fields_k.'" '. ((in_array($wpcc_mail_form_fields_k, $wpcc_mail_form_fields))?'checked':'').'> '.$wpcc_mail_form_fields_v.'</label><br>';
						}
						?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Validation', 'wpcc'); ?>:</th>
						<td>
						<?php
						$wpcc_mail_validation_get 		= wpcc_option_array($wpcc_mail_validation_id);
						$wpcc_mail_validation_option 	= array(
							'name' 		=>  __('Required Name', 'wpcc'),
							'email' 	=> __('Required Email', 'wpcc'),
							'phone' 	=> __('Required Phone', 'wpcc'),
							'comment' 	=> __('Required Comment', 'wpcc')
						);
						foreach($wpcc_mail_validation_option AS $wpcc_mail_validation_k => $wpcc_mail_validation_v)
						{
							echo '<label><input type="checkbox" name="'.$wpcc_mail_validation_id.'[]" value="'.$wpcc_mail_validation_k.'" '. ((in_array($wpcc_mail_validation_k, $wpcc_mail_validation_get))?'checked':'').'> '.$wpcc_mail_validation_v.'</label><br>';
						}
						?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('The text of a letter', 'wpcc'); ?>:</th>
						<td><textarea name="<?php echo $wpcc_mail_text_adm_id; ?>" class="textarea"><?php echo wpcc_esc_attr(get_option($wpcc_mail_text_adm_id)); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('The text before sending form', 'wpcc'); ?>:</th>
						<td><textarea name="<?php echo $wpcc_mail_text_id; ?>" class="textarea"><?php echo wpcc_esc_attr(get_option($wpcc_mail_text_id)); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Message sent successfully', 'wpcc'); ?>:</th>
						<td><textarea name="<?php echo $wpcc_mail_text_success_id; ?>" class="textarea"><?php echo wpcc_esc_attr(get_option($wpcc_mail_text_success_id)); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Show line calculation', 'wpcc'); ?>:</th>
						<td>
							<select name="<?php echo $wpcc_mail_result_string_id; ?>" class="select">
								<option value="1"><?php _e('Yes', 'wpcc'); ?></option>
								<option value="2" <?php if(get_option($wpcc_mail_result_string_id) == '2') { echo 'selected'; } ?>><?php _e('No', 'wpcc'); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Allow send copies of the letter to the visitor', 'wpcc'); ?>:</th>
						<td>
							<select name="<?php echo $wpcc_mail_copies_user_id; ?>" class="select">
								<option value="2" ><?php _e('No', 'wpcc'); ?></option>
								<option value="1" <?php echo ((get_option($wpcc_mail_copies_user_id) == 1)?'selected':''); ?>><?php _e('Yes', 'wpcc'); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Information about the calculator in email', 'wpcc'); ?>:</th>
						<td>
							<select name="<?php echo $wpcc_mail_text_footer_id; ?>" class="select">
								<option value="1"><?php _e('Yes', 'wpcc'); ?></option>
								<option value="2" <?php echo ((get_option($wpcc_mail_text_footer_id) == 2)?'selected':''); ?>><?php _e('No', 'wpcc'); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div class="clear"></div><br>
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="page_options" value="<?php echo $wpcc_submit_id.','.$wpcc_show_result_id.','.$wpcc_action_id.','.$wpcc_scroll_res_id.','.$wpcc_theme_id.','.$wpcc_enable_script_id.','.$wpcc_mail_check_id.','.$wpcc_mail_subject_id.','.$wpcc_mail_emailto_id.','.$wpcc_mail_form_fields_id.','.$wpcc_mail_validation_id.','.$wpcc_mail_text_adm_id.','.$wpcc_mail_text_id.','.$wpcc_mail_text_success_id.','.$wpcc_mail_result_string_id.','.$wpcc_mail_copies_user_id.','.$wpcc_mail_text_footer_id; ?>">
			<input type="submit" name="update" value="<?php _e('Save', 'wpcc'); ?>" class="button-primary">
		</form>
		
		<br>
		<form method="post" action="options.php" class="wpcc_type" >
			<h3><?php _e('Remove the database from the plug', 'wpcc'); ?></h3>
			<?php _e('If you do not plan to use the plugin and want to completely remove all <br> database records associated with wpcc, click on this link <a href="admin.php?page=wpcc&section=uninstall"> </ a>. <br> After clicking on the link will take you to the section for cleaning.', 'wpcc'); ?>
			<div class="clear"></div><br>
		</form>
<?php
	}
	elseif($wpcc_section == 'export')
	{
		echo '
		<h2>'. __('Export array formula', 'wpcc') .'</h2>
		<div class="wpcc_updated"> 
			<p>
				'. __('Select all the contents of the fields below and export to a new calculator.', 'wpcc') .'
			</p>
		</div>
		';
		/* Fields */
		$wpcc_export = $wpdb->get_results("SELECT * FROM $wpcc_DB WHERE wpcc_id = '$wpcc_id' AND wpcc_type != 'cat' ORDER BY wpcc_order");
		if(count($wpcc_export) > '0') {
			foreach($wpcc_export as $wpcc_export_row) {				
				if($wpcc_export_row->wpcc_type == 'sess_calc_id')
				{
					$wpcc_export_value = '/*WPCC_ID*/';
				}
				elseif($wpcc_export_row->wpcc_type == 'text')
				{
					$wpcc_export_value = preg_replace("|\[session id=\"(.*)\"\](.*)\[/session\]|", "[session id=\"/*WPCC_ID*/\"]$2[/session]", stripslashes($wpcc_export_row->wpcc_value));
				}
				else
				{
					$wpcc_export_value = $wpcc_export_row->wpcc_value;
				}
				
				$wpcc_export_fields ['wpcc_fields'][] = array(
					'wpcc_field' 	=> stripslashes($wpcc_export_row->wpcc_field),
					'wpcc_type' 	=> stripslashes($wpcc_export_row->wpcc_type),
					'wpcc_value' 	=> stripslashes($wpcc_export_value),
					'wpcc_order' 	=> stripslashes($wpcc_export_row->wpcc_order),
				);
				
			}
			/* Options */
			$wpcc_export_get_options = wpcc_arr_options('');
			foreach($wpcc_export_get_options AS $wpcc_export_options_row)
			{
				$wpcc_export_options ['wpcc_options'][] = array(
					$wpcc_export_options_row => get_option($wpcc_export_options_row.$wpcc_id)
				);
			}
			$wpcc_export_array_merge = array_merge($wpcc_export_fields, $wpcc_export_options);
			echo '
			<textarea class="wpcc_export">'.serialize($wpcc_export_array_merge).'</textarea>
			<a href="admin.php?page=wpcc&wpcc_id='.$wpcc_id.'" class="button button-primary">'. __('Cancel', 'wpcc') .'</a>
			';
		} else {
			echo '<p>'. __('Your formula is empty', 'wpcc') .'</p>';
		}
	}
	elseif($wpcc_section == 'import')
	{
		echo '
		<h2>'. __('Importing an array formula', 'wpcc') .'</h2>
		<div class="wpcc_updated"> 
			<p>
				'. __('Paste the entire array of data calculator and click the Import button.', 'wpcc') .'
			</p>
		</div>
		<form method="POST">
			<textarea name="wpcc_import" class="wpcc_import"></textarea>
			<input type="submit" name="wpcc_insert_import" value="'. __('Import', 'wpcc') .'" class="button button-primary">
			<a href="admin.php?page=wpcc&wpcc_id='.$wpcc_id.'" class="button button-primary">'. __('Cancel', 'wpcc') .'</a>
		</form>
		';
		$wpcc_import = unserialize(stripslashes($_POST['wpcc_import']));
	
		if(is_array($wpcc_import))
		{
			/* Import Fields */
			foreach($wpcc_import['wpcc_fields'] AS $wpcc_import_arr)
			{
			
				if(in_array($wpcc_import_arr['wpcc_type'], array('sess_calc_id', 'text')))
				{
					$wpcc_import_value = str_replace('/*WPCC_ID*/', $wpcc_id, $wpcc_import_arr['wpcc_value']);
				}
				else
				{
					$wpcc_import_value = $wpcc_import_arr['wpcc_value'];
				}
				$wpdb->query(  
					$wpdb->prepare("
					INSERT INTO $wpcc_DB
					(
						wpcc_id, 
						wpcc_field, 
						wpcc_type, 
						wpcc_value, 
						wpcc_order
					) 
					VALUES (
						'%d', 
						'%d', 
						'%s', 
						'%s', 
						'%d'
					)
					", 
						$wpcc_id, 
						$wpcc_import_arr['wpcc_field'], 
						$wpcc_import_arr['wpcc_type'], 
						$wpcc_import_value, 
						$wpcc_import_arr['wpcc_order']
					)
				);
			}
			/* Import Fields */
			foreach($wpcc_import['wpcc_options'] AS $wpcc_import_arr)
			{
				foreach($wpcc_import_arr AS $wpcc_import_k => $wpcc_import_v)
				{
					$wpcc_import_v_i = (( $wpcc_import_k == 'wpcc_field_results_' AND $wpcc_import_v == '') ? wpcc_default_field_res() : $wpcc_import_v );
					update_option( $wpcc_import_k.$wpcc_id, $wpcc_import_v_i );
				}
			}
			$wpcc_updated .= '<p>'. __('Import completed','wpcc') .'</p>';
		}
	}
	else
	{
?>
	<br>
	<form class="form" action="admin.php?page=wpcc&wpcc_id=<?php echo $wpcc_id; ?>" method="POST">
		<select class="wpcc_menu" name="wpcc_value">
			<?php
			foreach($wpcc_type_field AS $wpcc_menu_k => $wpcc_menu_v)
			{
				echo '<option value="'.$wpcc_menu_k.'">'.$wpcc_menu_v['name'].'</option>';
			}
			?>
		</select>
		<input type="submit" name="wpcc_add_field" value="<?php _e('Add', 'wpcc'); ?>" class="button button-primary">
	</form>
	<br>
	<h2><?php _e('Added fields', 'wpcc'); ?></h2>
	<div class="wpcc_fields wpcc_fields_sortable">
		<?php	
		$wpcc_row_arr = $wpdb->get_results("SELECT * FROM $wpcc_DB WHERE wpcc_id = '$wpcc_id' AND wpcc_type != 'cat' ORDER BY wpcc_order");
		if(count($wpcc_row_arr) > '0') {
			echo '
			<form method="POST">
			<ul>
			';
			foreach($wpcc_row_arr as $wpcc_row) {
				$wpcc_value = wpcc_cache_field($wpcc_row_arr, $wpcc_row->wpcc_field);
				if($wpcc_row->wpcc_type == 'field_type')
				{
					$wpcc_row_color = preg_replace ('/[^a-zA-Z0-9#]/', '', $wpcc_value['color']);
					echo '
				<li id="wpcc_sortable_'.$wpcc_row->id.'" class="button button-small wpcc_type" '.(($wpcc_row_color != '')?'style="background-color:'.$wpcc_row_color.'"':'').' data-fid="'.$wpcc_row->wpcc_field.'">
					<div>
						<div class="ico"></div>
						[ID-'.$wpcc_row->wpcc_field.']
						'.$wpcc_type_field[$wpcc_row->wpcc_value]['name'].''.((strip_tags($wpcc_value['title']) != '')?' ( '.mb_substr(strip_tags($wpcc_value['title']), 0, '80').' )':'').'
						<div class="clear"></div>
					</div>
					<div class="setting">
						'.wpcc_row_field($wpcc_row->wpcc_value, $wpcc_row_arr, $wpcc_row->wpcc_field).'
					</div>
				</li>
					';
				}
			}
			echo '
			</ul>
			<input type="submit" name="wpcc_update_field" value="'. __('Save field values', 'wpcc') .'" class="button-primary">
			</form>
			<div class="wpcc_updated wpcc_sortable"> 
				<p>
					'. __('Sort the order of the fields with the mouse.', 'wpcc') .'
				</p>
				<form method="post">
					<input type="hidden" class="jq_wpcc_sortable" name="wpcc_sortable_str" />
					<input type="submit" name="wpcc_sortable" class="button-primary" value="'. __('Save the order of the fields', 'wpcc') .'" />
				</form>
				<div class="clear"></div>
			</div>
			';
		} else {
			echo __("Formula is empty", "wpcc");
		}
		?>
	</div>
	
	<h2><?php _e("Manage the results of calculations", "wpcc"); ?></h2>
	<div class="wpcc_fields">
		<form method="post" class="wpcc_types" >
		<?php		
		$wpcc_field_results 		= wpcc_field_results($wpcc_id);
		$wpcc_field_results_round 	= array(
			'none' 		=> __('Do not round', 'wpcc'),
			'round' 	=> __('Rounding ROUND() function 0 characters. Example: 4 = 3.7, 3.1 = 3', 'wpcc'),
			'round_1' 	=> __('Rounding ROUND() function 1 character. Example: 3.46 = 3.5', 'wpcc'),
			'round_2' 	=> __('Rounding ROUND() function 2 characters. Example: 3.467 = 3.47', 'wpcc'),
			'round_3' 	=> __('Rounding ROUND() function 3 digits. Example: 3.4678 = 3.468', 'wpcc'),
			'round_4' 	=> __('Rounding ROUND() function 4 digits. Example: 3.46789 = 3.4679', 'wpcc'),
			'round_m1' 	=> __('Rounding to the nearest whole number. Example: 346 = 350, 344 = 340', 'wpcc'),
			'ceil' 		=> __('Rounding function CEIL(). Example: 4 = 3.7, 3.1 = 4', 'wpcc'),
			'floor' 	=> __('Rounding function FLOOR(). Example: 3.7 = 3, 3.1 = 3', 'wpcc')
		);
		?>
		<ul>
			<?php
			$i = 1;
			foreach($wpcc_field_results AS $wpcc_field_result)
			{
				echo '
			<li class="button button-small wpcc_type wpcc_c_d">
					<div>
						<div class="ico"></div>
						'.(($i == 1) ? __("The main result of the calculation", "wpcc") : __("The result of calculation ", "wpcc") . $i ).'
						<div class="clear"></div>
					</div>
					<div class="setting">
						<div class="name">'. __("Text before the result", "wpcc") .': </div> <input type="text" name="wpcc_field_results_'.$wpcc_id.'['.$i.'][before]" class="input" value="'.wpcc_esc_attr($wpcc_field_results[$i]['before']).'"><br><div class="clear"></div>
						<div class="name">'. __("Text after result", "wpcc") .': </div> <input type="text" name="wpcc_field_results_'.$wpcc_id.'['.$i.'][after]" class="input" value="'.wpcc_esc_attr($wpcc_field_results[$i]['after']).'"><br><div class="clear"></div>
						<div class="name">'. __("Minimum amount", "wpcc") .': </div> <input type="text" name="wpcc_field_results_'.$wpcc_id.'['.$i.'][minamount]" class="input" value="'.wpcc_esc_attr($wpcc_field_results[$i]['minamount']).'"><br><div class="clear"></div>
						<div class="name">'. __("Arithmetic expression", "wpcc") .': </div> <input type="text" name="wpcc_field_results_'.$wpcc_id.'['.$i.'][formula]" class="input" value="'.wpcc_esc_attr($wpcc_field_results[$i]['formula']).'"><br><div class="clear"></div>
						<div class="name">'. __('Rounding amount:', 'wpcc') .' </div>
						<select name="wpcc_field_results_'.$wpcc_id.'['.$i.'][round]" class="select">
				';
						foreach($wpcc_field_results_round as $wpcc_round_sum_k => $wpcc_round_sum_v)
						{
							echo '<option value="'.$wpcc_round_sum_k.'" '.(($wpcc_field_results[$i]['round'] == $wpcc_round_sum_k)?'selected':'').'>'.$wpcc_round_sum_v.'</option>';
						}
				echo '
						</select><br><div class="clear"></div>
						'.(($i != 1)?'<div class="name">'. __("Remove the calculation result", "wpcc") .': </div> <input type="checkbox" name="wpcc_field_results_'.$wpcc_id.'['.$i.'][delete]" class="checkbox" value="1"><br><div class="clear"></div>':'').'
					</div>
			</li>
				';
				
				if($i == count($wpcc_field_results))
				{
					$i_new = $i + 1;
					echo '
			<li class="button button-small wpcc_type wpcc_c_d">
					<div>
						<div class="ico"></div>
						'. __("Add result of calculation", "wpcc") .'
						<div class="clear"></div>
					</div>
					<div class="setting">
						<div class="name">'. __("Text before the result", "wpcc") .': </div> <input type="text" name="wpcc_field_results_'.$wpcc_id.'['.$i_new.'][before]" class="input" value=""><br><div class="clear"></div>
						<div class="name">'. __("Text after result", "wpcc") .': </div> <input type="text" name="wpcc_field_results_'.$wpcc_id.'['.$i_new.'][after]" class="input" value=""><br><div class="clear"></div>
						<div class="name">'. __("Minimum amount", "wpcc") .': </div> <input type="text" name="wpcc_field_results_'.$wpcc_id.'['.$i_new.'][minamount]" class="input" value=""><br><div class="clear"></div>
						<div class="name">'. __("Arithmetic expression", "wpcc") .': </div> <input type="text" name="wpcc_field_results_'.$wpcc_id.'['.$i_new.'][formula]" class="input" value=""><br><div class="clear"></div>
						<div class="name">'. __('Rounding amount:', 'wpcc') .' </div>
						<select name="wpcc_field_results_'.$wpcc_id.'['.$i_new.'][round]" class="select">
				';
						foreach($wpcc_field_results_round as $wpcc_round_sum_k => $wpcc_round_sum_v)
						{
							echo '<option value="'.$wpcc_round_sum_k.'" '.(($wpcc_field_results[$i_new]['round'] == $wpcc_round_sum_k)?'selected':'').'>'.$wpcc_round_sum_v.'</option>';
						}
				echo '
						</select><br><div class="clear"></div>
					</div>
			</li>
					';
				}
				$i++;
				
			}
			?>
		</ul>
			<div class="clear"></div>
			<input type="submit" name="update" value="<?php _e("Save the results of calculations", "wpcc"); ?>" class="button-primary">
		</form>
	</div>
	
	<h2><?php _e('Text formula ID fields', 'wpcc'); ?></h2>
	<div class="wpcc_text_formula">
	<?php
	$wpcc_text = $wpdb->get_results("SELECT * FROM $wpcc_DB WHERE wpcc_id = '$wpcc_id' AND wpcc_type != 'cat' ORDER BY wpcc_order");
	if(count($wpcc_text) > '0') {
		foreach($wpcc_text as $wpcc_text_row) {
			$wpcc_cache = wpcc_cache_field($wpcc_text, $wpcc_text_row->wpcc_field);
			if($wpcc_text_row->wpcc_type == 'field_type' AND !in_array($wpcc_text_row->wpcc_value, array('textblock', 'if')) AND $wpcc_cache['exclude'] != '2')
			{
				$wpcc_row_color = preg_replace ('/[^a-zA-Z0-9#]/', '', $wpcc_cache['color']);					
				echo '<div class="box_color" '.(($wpcc_row_color != '')?'style="background-color:'.$wpcc_row_color.'"':'').'>'.$wpcc_cache['signto'].(($wpcc_text_row->wpcc_value == 'armtc')?'[':'').$wpcc_text_row->wpcc_field.(($wpcc_text_row->wpcc_value == 'armtc')?']':'').$wpcc_cache['signaf'].'</div>';
			}
		}
	} else {
		_e('Formula is empty', 'wpcc');
	}
	?>
		<div class="clear"></div>
	</div>
	
	<h2><?php _e('Preview calculator', 'wpcc'); ?></h2>
	<div class="wpcc_form_calc">
			<?php echo do_shortcode('[wpcc id="'.$wpcc_id.'" moderator="true"]'); ?>
	</div>
	
<?php
	}
}
elseif($wpcc_section == '')
{
	if(count($wpcc_all_arr) == '0')
	{
		echo '
		<div class="wpcc_updated"> 
			<p>'. __('You have not added any calculator', 'wpcc') .'</p>
		</div>
		';
	}
	else
	{
		echo '
		<div class="list">';
		foreach($wpcc_all_arr AS $wpcc_all_list)
		{
			echo '
			<div class="button button-small">
				<a href="admin.php?page=wpcc&wpcc_id='.$wpcc_all_list['wpcc_id'].'">'.$wpcc_all_list['wpcc_id'].' - '.$wpcc_all_list['wpcc_name'].'</a>
				<a href="admin.php?page=wpcc&delete='.$wpcc_all_list['wpcc_id'].'" class="alignright">['. __('Delete', 'wpcc') .']</a>
			</div>
			';
		}
		echo '</div>';
	}
}
elseif($wpcc_section == 'new' AND count($wpcc_check_table) > '0')
{
	if($wpcc_new_result != '')
	{
		echo $wpcc_new_result;
	}
	else
	{
?>
<form class="form" method="POST">
	<input type="hidden" name="wpdb_id" value="0">
	<input type="hidden" name="wpcc_field" value="0">
	<input type="hidden" name="wpcc_type" value="cat">
	<input type="text" name="wpcc_value" class="input" value="<?php echo wpcc_esc_attr($_POST['wpcc_value']); ?>" placeholder="<?php _e('Name calculator', 'wpcc'); ?>">
	<input type="hidden" name="wpcc_order" value="0">
	<input type="submit" name="wpcc_new" value="<?php _e('Add', 'wpcc'); ?>"  class="button button-primary">
</form>
<?php
	}
}
?>
	<div class="clear"></div>
</div>
<?php
}

function wpcc_if_check($if_operator='', $input_val='', $if_val='') {

	$if_operator = $if_operator == '' ? '==' : $if_operator;
	
	if($if_operator == 'between')
	{
		$if_value_between 		=  explode('-',$if_val);
		$if_value_between_one 	= $if_value_between[0];
		$if_value_between_two 	= $if_value_between[1];
	}
	
	if(
		( $if_operator == '==' AND $input_val == $if_val )
		||
		( $if_operator == '>=' AND $input_val >= $if_val )
		||
		( $if_operator == '<=' AND $input_val <= $if_val )
		||
		( $if_operator == '>' AND $input_val > $if_val )
		||
		( $if_operator == '<' AND $input_val < $if_val )
		||
		( $if_operator == 'between' AND $input_val >= $if_value_between_one AND $input_val <= $if_value_between_two )
		||
		( $if_operator == 'notempty' AND mb_strlen($input_val) > 0 )
	){
		return true;
	}
	else
	{
		return false;
	}
}

function wpcc_if_parse($wpcc_id) {
	global $wpdb, $wpcc_DB;
	
	$return = array(
		'if_hide' => array(),
		'if_show' => array()
	);
	$hide_id = array();
	$show_id = array();
	
	$if_ids 	= $_POST['wpcc_structure_if'];
	$field_ids	= preg_replace ('/[^0-9,]/', '', $_POST['wpcc_structure_id']);
	if(count($if_ids) > 0)
	{
		$field_arr 	= explode(',', $field_ids);
		$array_ids 	= array_merge($if_ids, $field_arr);
		foreach($array_ids AS $array_id)
		{
			$query_ids [] = preg_replace ('/[^0-9]/', '', $array_id);
		}
		
		$fields_id = implode("','", $query_ids);
		$wpcc_query = $wpdb->get_results("
			SELECT * FROM $wpcc_DB 
			WHERE
				wpcc_id = '$wpcc_id'
			AND
				wpcc_field IN ('$fields_id')
		");
		
		foreach($wpcc_query AS $wpcc_row)
		{
			$wpcc_cache = wpcc_cache_field($wpcc_query, $wpcc_row->wpcc_field);
			if($wpcc_row->wpcc_type == 'field_type')
			{
				if($wpcc_row->wpcc_value == 'if')
				{
					$value = wpcc_value($wpcc_query, $wpcc_cache['if_id']);
					if(wpcc_if_check($wpcc_cache['if_operator'], $value, $wpcc_cache['if_val']))
					{
						$hide_id[] = (($wpcc_cache['if_hide'] == '')?'0':$wpcc_cache['if_hide']);
						$show_id[] = (($wpcc_cache['if_show'] == '')?'0':$wpcc_cache['if_show']);
					}
					else
					{
						if($wpcc_cache['if_logic'] != 'if')
						{
							$show_id[] = (($wpcc_cache['if_hide'] == '')?'0':$wpcc_cache['if_hide']);
							$hide_id[] = (($wpcc_cache['if_show'] == '')?'0':$wpcc_cache['if_show']);
						}
					}
				}
			}
		}
		$if_hide_str_im = implode(',',$hide_id);
		$if_show_str_im = implode(',',$show_id);
		$return['if_hide'] = explode(',',$if_hide_str_im);
		$return['if_show'] = explode(',',$if_show_str_im);
	}
	
	return $return;
}

function wpcc_sum_round($wpcc_sum = '', $round = '') {
	if($round == 'round')
	{
		return round($wpcc_sum);
	}
	elseif($round == 'round_1')
	{
		return round($wpcc_sum, 1);
	}
	elseif($round == 'round_2')
	{
		return round($wpcc_sum, 2);
	}
	elseif($round == 'round_3')
	{
		return round($wpcc_sum, 3);
	}
	elseif($round == 'round_4')
	{
		return round($wpcc_sum, 4);
	}
	elseif($round == 'round_m1')
	{
		return round($wpcc_sum, -1);
	}
	elseif($round == 'ceil')
	{
		return ceil($wpcc_sum);
	}
	elseif($round == 'floor')
	{
		return floor($wpcc_sum);
	}
	else
	{
		return $wpcc_sum;
	}
}

function wpcc_what_number($sign='') {
	if($sign == '+' OR $sign == '-')
	{
		return 0;
	}
	elseif($sign == '*' OR $sign == '/')
	{
		return 1;
	}
	else
	{
		return 1;
	}
}

function wpcc_eval($eval='', $wpcc_sum='') {
	$return = '0';
	eval ('$return = '.$eval.';');
	return $return;
}

function wpcc_value_type($data = '', $type = '') {
	if($type == 'data_count')
	{
		return mb_strlen($data);
	}
	elseif($type == 'data_date_time')
	{
		return $data;
	}
	else
	{
		return $data;
	}
}

function wpcc_value($wpcc_query = '', $field_id = '', $option=array()) {
	$post 		= $_POST['wpcc_structure'];
	$value 		= ((in_array('no_filter', $option) OR is_array($post[$field_id])) ? $post[$field_id] : wpcc_price($post[$field_id]) );
	$cache 		= wpcc_cache_field($wpcc_query, $field_id);
	$type 		= $cache['field_type'];
	$data_type 	= $cache['data'];
	
	$return 	= '';
	
	if($type == 'select')
	{
		$return = $value;
	}
	elseif($type == 'checkbox')
	{
		if(is_array($value) AND $cache['action'] != '')
		{
			foreach($value AS $val)
			{
				$value_arr []= wpcc_price($val);
			}
			$values_eval 	= implode($cache['action'], $value_arr);
			$return 		= wpcc_eval($values_eval);
		}
		else
		{
			$return = $cache['default'];
		}
	}
	elseif($type == 'radio')
	{
		$return = $value;
	}
	elseif($type == 'inputtext')
	{
		$maxchar = intval($cache['maxchar']);
		
		$value = $value != '' ? $value : $cache['default'];
		
		if($maxchar > 0)
			mb_substr($value, 0, $maxchar);
		
		if(in_array('inputval', $option))
		{
			$return = $value;
		}
		elseif($cache['price'] >= 0 AND $cache['action'] != '')
		{
			$values_eval 	= $value.$cache['action'].$cache['price'];
			$return 		= wpcc_eval($values_eval);
		}
		else
		{
			$return = $value;
		}
	}
	elseif($type == 'hidden')
	{
		$return = $value;
	}
	elseif($type == 'session')
	{
		$return = $value;
	}
	elseif($type == 'jquery')
	{
		$return = (($post[$cache['jq_id']] == '' OR $cache['jq_id'] == $field_id) ? $cache['default'] : wpcc_value($wpcc_query, $cache['jq_id']) );
	}
	elseif($type == 'slider')
	{
		if($value < $cache['slider_min'])
		{
			$return = $cache['slider_min'];
		}
		elseif($value > $cache['slider_max'])
		{
			$return = $cache['slider_max'];
		}
		else
		{
			$return = $value;
		}
	}
	elseif($type == 'if')
	{
		/* Skip */
	}
	elseif($type == 'ifhidden')
	{
	
		if(wpcc_check_list($cache['list']))
		{
			$list_arr = unserialize(wpcc_check_list($cache['list']));
			foreach($list_arr AS $list_k => $list_v)
			{
				if($cache['if_id'] != $field_id)
				{
					$list_val = wpcc_value($wpcc_query, $cache['if_id']);
					if(wpcc_if_check($cache['if_operator'], $list_val, $list_v['val']))
					{
						$value_arr[] = $list_v['txt'];
					}
				}
			}
		}
		if(count($value_arr) > 0)
		{
			$values_eval 	= implode($cache['action'], $value_arr);
			$return 		= wpcc_eval($values_eval);
		}
		else
		{
			$return = $cache['default'];
		}
	}
	elseif($type == 'armtc')
	{
		$return = $value;
	}
	elseif($type == 'result_fields')
	{
		$ids = explode(',', $cache['rf_fields']);
		foreach($ids AS $id)
		{
			if($id != $field_id)
			{
				$fields_val = wpcc_value($wpcc_query, $id);
				$fields_arr[] = $fields_val;
			}
		}
		
		if(count($fields_arr) > 0)
		{
			$values_eval 	= implode($cache['action'], $fields_arr);
			$return_eval 	= wpcc_eval($values_eval);
			
			if($cache['datarf'] == 'data' OR $cache['datarf'] == 'data_count_day')
			{
				$return = $return_eval;
			}
			elseif($cache['datarf'] == 'data_count')
			{
				$return = mb_strlen($return_eval);
			}
			elseif($cache['datarf'] == 'data_date')
			{
				$return = date('d-m-Y', $return_eval);
			}
			elseif($cache['datarf'] == 'data_day_number')
			{
				$return = date('d', $return_eval);
			}
			elseif($cache['datarf'] == 'data_month')
			{
				$return = date('m', $return_eval);
			}
			elseif($cache['datarf'] == 'data_year')
			{
				$return = date('y', $return_eval);
			}
		}
		else
		{
			$return = $cache['default'];
		}
	}
	elseif($type == 'custom_fields')
	{
		$return = $value != '' ? $value : $cache['default'];
	}
	elseif($type == 'date')
	{
		$date 	= $value != '' ? $value : $cache['date'];
		if($cache['datadate'] == 'string')
		{
			$return = $date;
		}
		else
		{
			$return = strtotime(wpcc_date_conv($date));
		}
		
	}
	
	return wpcc_value_type($return, $data_type);
}

/* WPCC Parser Array Value */
function wpcc_parsing_post() {
	global $wpdb, $wpcc_DB;
	
	$wpcc_id 			= intval($_POST['wpcc_id']);
	$wpcc_structure		= $_POST['wpcc_structure'];
	$wpcc_structure_id	= preg_replace ('/[^0-9,]/', '', $_POST['wpcc_structure_id']);
		
	if($wpcc_structure_id != '' AND $wpcc_id > 0)
	{
		unset($_SESSION['wpcc_'.$wpcc_id]);
		
		$wpcc_if_parse 	= wpcc_if_parse($wpcc_id);
		$wpcc_if_hide 	= count($wpcc_if_parse['if_hide']) > 0 ? $wpcc_if_parse['if_hide'] : array();
		
		$wpcc_structure_list 	= explode(',', $wpcc_structure_id);
		$wpcc_structure_q 		= implode("','", $wpcc_structure_list);
		
		$wpcc_query = $wpdb->get_results("
			SELECT * FROM $wpcc_DB 
			WHERE
				wpcc_id = '$wpcc_id'
			AND
				wpcc_field IN ('$wpcc_structure_q')
		");
		
		foreach ($wpcc_query AS $wpcc_query_row)
		{
			$feild_id 	= $wpcc_query_row->wpcc_field;
			$cache 		= wpcc_cache_field($wpcc_query, $feild_id);
			if($wpcc_query_row->wpcc_type == 'field_type')
			{
				if($cache['exclude'] == '2' OR in_array($feild_id, $wpcc_if_hide))
				{
					$value_result = wpcc_value($wpcc_query, $feild_id, array('no_filter'));
				}
				else
				{
					$value_result = wpcc_value($wpcc_query, $feild_id);
					$return[$wpcc_query_row->wpcc_order] .= $cache['signto'].$value_result.$cache['signaf'];
				}
				
				$fields_value[$feild_id] = $value_result;
				
				if(!in_array($feild_id, $wpcc_if_hide))
				{
					/* User selected data */
					$data_array = '';
					$data_array_val = array();
					if($cache['field_type'] == 'select' OR $cache['field_type'] == 'radio' OR $cache['field_type'] == 'checkbox')
					{
						if(wpcc_check_list($cache['list']))
						{
							$list_arr = unserialize(wpcc_check_list($cache['list']));
							foreach($list_arr AS $list_k => $list_v)
							{
								$list_key 				= $cache['field_type'] == 'checkbox' ? $list_k : $list_v['val'];
								$data_array[$list_key] 	= $list_v['txt'];
							}							
							if(is_array($wpcc_structure[$feild_id]))
							{
								foreach($wpcc_structure[$feild_id] AS $data_strukture_k => $data_strukture_v)
								{
									$data_array_val[] = $cache['field_type'] == 'checkbox' ? $data_array[$data_strukture_k] : $data_array[$data_strukture_v];
								}
							}
							else
							{
								$data_array_val[] = $data_array[$wpcc_structure[$feild_id]];
							}
						}
					}
					elseif($cache['field_type'] == 'date')
					{
						$data_array_val[] = $cache['datadate'] == 'string' ? $value_result : date('d-m-Y', $value_result);
					}
					elseif($cache['field_type'] == 'inputtext')
					{
						$data_array_val[] = wpcc_value($wpcc_query, $feild_id, array('no_filter', 'inputval'));
					}
					else
					{
						$data_array_val[] = $value_result;
					}
					$fields_user_choice[$feild_id] = implode(', ',$data_array_val);
				}
			}
		}
		$_SESSION['wpcc_'.$wpcc_id] 						= $fields_value;
		$_SESSION['wpcc_'.$wpcc_id]['fields_user_choice'] 	= $fields_user_choice;
		
		if(!empty($return))
		{
			ksort($return);
		}
		return $return;
	}
	else
	{
		return false;
	}
}

function wpcc_result($moderator='false') {
	$wpcc_id = intval($_POST['wpcc_id']);
		$return = '
	<div class="wpcc_result wpcc_result_'.$wpcc_id.'" id="wpcc_result">
		';
			
			$wpcc_parsing_post = wpcc_parsing_post();
			
			if(!empty($wpcc_parsing_post))
			{
				foreach($wpcc_parsing_post as $eval_row)
				{
					$eval_return .= $eval_row;
				}
			}
			else
			{
				$eval_return = '0';
			}
			
			$wpcc_sum 			= wpcc_eval($eval_return);
			$wpcc_field_results = wpcc_field_results($wpcc_id);
			
			$_SESSION['wpcc_'.$wpcc_id]['calculation'] = $eval_return;
			
			foreach($wpcc_field_results AS $wpcc_field_result_k => $wpcc_field_result_v)
			{
				$minamount = wpcc_price($wpcc_field_result_v['minamount']);
				
				$return .= '
				<p class="wpcc_field_result_'.$wpcc_field_result_k.'">'.$wpcc_field_result_v['before'].' ';
				
				$wpcc_sum_result = wpcc_eval($wpcc_field_result_v['formula'], $wpcc_sum);
				$wpcc_sum_result = apply_filters('wpcc_result_filter', $wpcc_sum_result, $wpcc_id, $wpcc_field_result_k);
				$wpcc_sum_result = wpcc_sum_round($wpcc_sum_result, $wpcc_field_result_v['round']);
				
				if($minamount > 0 AND $wpcc_sum_result < $minamount)
				{
					$return .= $minamount;
				}
				else
				{
					$return .= $wpcc_sum_result;
				}
				
				$return .= ' '.$wpcc_field_result_v['after'].'</p>';
				
				$wpcc_sum_sess_name = $wpcc_field_result_k == 1 ? 'sum' : 'sum_'.$wpcc_field_result_k;
				
				$_SESSION['wpcc_'.$wpcc_id][$wpcc_sum_sess_name] = $wpcc_sum_result;
			}
			
			$return .= '
	</div>
		';
	
	if ($moderator == 'true' OR $_POST['wpcc_moderator'] == 'true') {
		echo '
		<br>
		<hr>
		<h3 class="wpcc_js_toggle button-primary" data-container="wpcc_progress_moderate">'. __('View the progress of calculation', 'wpcc') .'</h3>
		<div class="wpcc_progress_moderate wpcc_dnone">
		<b>'. __('Line calculation', 'wpcc') .':</b>
		';
		wpcc_pr($eval_return);
		echo '<b>'. __('The data in the current session of the calculator', 'wpcc') .' $_SESSION[\'wpcc_'.$wpcc_id.'\']:</b>';
		wpcc_pr($_SESSION['wpcc_'.$wpcc_id]);
		echo '<b>'. __('Formula in order of execution', 'wpcc') .':</b>';
		wpcc_pr($wpcc_parsing_post);
		echo '<hr>
		</div>
		';
	}
	if(get_option('wpcc_show_result_'.$wpcc_id) != '2')
	{
		return $return;
	}
}

function wpcc_mail_form($id) {
	$wpcc_id 				= intval($id);
	$wpcc_mail_check 		= get_option('wpcc_mail_check_'.$wpcc_id);
	$wpcc_mail_text 		= get_option('wpcc_mail_text_'.$wpcc_id);
	$wpcc_mail_copies_user 	= get_option('wpcc_mail_copies_user_'.$wpcc_id);
	$wpcc_mail_form_fields 	= wpcc_option_array('wpcc_mail_form_fields_'.$wpcc_id);
	$wpcc_mail_send 		= wpcc_mail_send();
	
	if($wpcc_mail_check == '1')
	{
		if($wpcc_mail_send['success'] == '')
		{
			$return .= '
	<form method="POST" action="#wpcc_mail_scroll" class="wpcc_mail wpcc_mail_'.$wpcc_id.'" id="wpcc_mail_scroll">
		'.(($wpcc_mail_text != '')?'<div class="wpcc_text">'.nl2br($wpcc_mail_text).'</div>':'').'
		'.((!in_array('name', $wpcc_mail_form_fields))?'
		<p>
			<input type="text" name="wpcc_user_name" value="" placeholder="'. __('Your name', 'wpcc') .'" class="input">
		</p>
		':'').'
		'.((!in_array('email', $wpcc_mail_form_fields))?'
		<p>
			<input type="email" name="wpcc_user_email" value="" placeholder="'. __('Your E-Mail', 'wpcc') .'" class="input">
		</p>
		':'').'
		'.((!in_array('phone', $wpcc_mail_form_fields))?'
		<p>
			<input type="text" name="wpcc_user_phone" value="" placeholder="'. __('Your Phone', 'wpcc') .'" class="input">
		</p>
		':'').'
		'.((!in_array('comment', $wpcc_mail_form_fields))?'
		<p>
			<textarea name="wpcc_user_comment" placeholder="'. __('Your comment', 'wpcc') .'" value="" class="textarea"></textarea>
		</p>
		':'').'
		';
		
		if($wpcc_mail_copies_user != '2' AND !in_array('email', $wpcc_mail_form_fields))
		{
			$return .= '
			<p>
				<label>
					<input type="checkbox" name="wpcc_user_copy" value="1">'. __('Send me a copy.', 'wpcc') .'
				</label>
			</p>
			';
		}
		
		$return .= '
		<div class="wpcc_error">'.$wpcc_mail_send['error'].'</div>
		<input type="hidden" name="action" value="wpcc_ajax_mail">
		<input type="hidden" name="wpcc_mail_id" class="wpcc_mail_id" value="'.$wpcc_id.'">
		<input type="submit" class="wpcc_submit" value="'. __('Send', 'wpcc') .'">
		<div class="wpcc_loading"><div></div></div>
	</form>
			';
		}
		else
		{
			$return = $wpcc_mail_send['success'];
		}
		
		return $return;
	}
}

function wpcc_mail_send($option=array()) {
	global $wpdb, $wpcc_DB, $wpcc_type_field;
	
	$error 						= '';
	$success 					= '';
	$wpcc_id 					= intval($_POST['wpcc_mail_id']);
	$wpcc_show_result 			= get_option('wpcc_show_result_'.$wpcc_id);
	$wpcc_mail_result_string 	= get_option('wpcc_mail_result_string_'.$wpcc_id);
	$wpcc_mail_check 			= get_option('wpcc_mail_check_'.$wpcc_id);
	$wpcc_mail_subject 			= get_option('wpcc_mail_subject_'.$wpcc_id);
	$wpcc_mail_emailto 			= get_option('wpcc_mail_emailto_'.$wpcc_id);
	$wpcc_mail_form_fields 		= wpcc_option_array('wpcc_mail_form_fields_'.$wpcc_id);
	$wpcc_mail_validation 		= wpcc_option_array('wpcc_mail_validation_'.$wpcc_id);
	$wpcc_mail_text_adm			= get_option('wpcc_mail_text_adm_'.$wpcc_id);
	$wpcc_mail_text_success 	= get_option('wpcc_mail_text_success_'.$wpcc_id);
	$wpcc_mail_text_footer 		= get_option('wpcc_mail_text_footer_'.$wpcc_id);
	$wpcc_mail_copies_user 		= get_option('wpcc_mail_copies_user_'.$wpcc_id);
	
	if($wpcc_mail_check == '1' AND $wpcc_id > 0)
	{						
		$wpcc_user_name 			= wpcc_esc_attr($_POST['wpcc_user_name']);
		$wpcc_user_email 			= wpcc_esc_attr($_POST['wpcc_user_email']);
		$wpcc_user_phone 			= wpcc_esc_attr($_POST['wpcc_user_phone']);
		$wpcc_user_comment 			= wpcc_esc_attr($_POST['wpcc_user_comment']);
		$wpcc_user_copy 			= intval($_POST['wpcc_user_copy']);
		$fields_user_choice_arr 	= array();
		
		if($wpcc_user_name == '' AND in_array('name', $wpcc_mail_validation) AND !in_array('name', $wpcc_mail_form_fields))
		{
			$error .= '<p>'. __('Name required field', 'wpcc') .'</p>';
		}
		if(!is_email($wpcc_user_email) AND in_array('email', $wpcc_mail_validation) AND !in_array('email', $wpcc_mail_form_fields))
		{
			$error .= '<p>'. __('Email required field', 'wpcc') .'</p>';
		}
		if($wpcc_user_phone == '' AND in_array('phone', $wpcc_mail_validation) AND !in_array('phone', $wpcc_mail_form_fields))
		{
			$error .= '<p>'. __('Phone required field', 'wpcc') .'</p>';
		}
		if($wpcc_user_comment == '' AND in_array('comment', $wpcc_mail_validation) AND !in_array('comment', $wpcc_mail_form_fields))
		{
			$error .= '<p>'. __('Comment required field', 'wpcc') .'</p>';
		}
		
		if($error == '' AND $wpcc_id > 0)
		{
			$wpcc_all_calc = wpcc_all_calc();
			$wpcc_mail_body = '
			<p>'.nl2br($wpcc_mail_text_adm).'</p>
				<table width="100%" border="0" cellpadding="2" cellspacing="0">
				'.((count($wpcc_mail_form_fields) < 4)?'
				<tr>
					<td colspan="2" align="left" valign="top" bgcolor="#fff" style="border-bottom: 5px solid #FFFFFF"><b>'. __('Information about the sender', 'wpcc') .'</b></td>
				</tr>
				':'').'
				'.((!in_array('name', $wpcc_mail_form_fields))?'
				<tr>
					<td width="250" align="left" valign="top" bgcolor="#FFFFFF" style="border-bottom: 5px solid #FFFFFF">'. __('Name', 'wpcc') .'</td>
					<td style="border-bottom: 5px solid #FFFFFF">'.(($wpcc_user_name == '')?'---':$wpcc_user_name).'</td>
				</tr>
				':'').'
				'.((!in_array('email', $wpcc_mail_form_fields))?'
				<tr>
					<td width="250" align="left" valign="top" bgcolor="#FFFFFF" style="border-bottom: 5px solid #FFFFFF">E-Mail</td>
					<td style="border-bottom: 5px solid #FFFFFF">'.(($wpcc_user_email == '')?'---':$wpcc_user_email).'</td>
				</tr>
				':'').'
				'.((!in_array('phone', $wpcc_mail_form_fields))?'
				<tr>
					<td width="250" align="left" valign="top" bgcolor="#FFFFFF" style="border-bottom: 5px solid #FFFFFF">'. __('Phone', 'wpcc') .'</td>
					<td style="border-bottom: 5px solid #FFFFFF">'.(($wpcc_user_phone == '')?'---':$wpcc_user_phone).'</td>
				</tr>
				':'').'
				'.((!in_array('comment', $wpcc_mail_form_fields))?'
				<tr>
					<td width="250" align="left" valign="top" bgcolor="#FFFFFF" style="border-bottom: 5px solid #FFFFFF">'. __('Comment', 'wpcc') .'</td>
					<td style="border-bottom: 5px solid #FFFFFF">'.(($wpcc_user_comment == '')?'---':nl2br($wpcc_user_comment)).'</td>
				</tr>
				':'').'
				<tr>
					<td colspan="2" align="left" valign="top" bgcolor="#fff" style="border-bottom: 5px solid #FFFFFF"><b>'. __('The calculation', 'wpcc') .'</b></td>
				</tr>
			';
			
			$wpcc_session = $_SESSION['wpcc_'.$wpcc_id];
			
			$fields_user_choice =  $_SESSION['wpcc_'.$wpcc_id]['fields_user_choice'];
			foreach($fields_user_choice AS $fields_user_choice_k => $fields_user_choice_v)
			{
				$fields_user_choice_arr []= intval($fields_user_choice_k);
			}
			$fields_id = implode("','", $fields_user_choice_arr);
			$wpcc_query = $wpdb->get_results("
				SELECT * FROM $wpcc_DB 
				WHERE
					wpcc_id = '$wpcc_id'
				AND
					wpcc_field IN ('$fields_id')
				AND
					wpcc_type != 'cat'
				ORDER BY wpcc_order
			");
			foreach ($wpcc_query as $row)
			{
				if($row->wpcc_type == 'field_type')
				{
					$wpcc_cache 		= wpcc_cache_field($wpcc_query, $row->wpcc_field);
					$mail_body_title 	= (($wpcc_cache['title'] == '')?$wpcc_type_field[$row->wpcc_value]['name']:$wpcc_cache['title']);
					
					if($wpcc_cache['mail_show'] != 'no' AND $wpcc_cache['field_type'] != 'armtc')
					{
					$wpcc_mail_body .= '
				<tr>
					<td width="250" align="left" valign="top" bgcolor="#FFFFFF" style="border-bottom: 5px solid #FFFFFF">'.$mail_body_title.'</td>
					<td style="border-bottom: 5px solid #FFFFFF">'.$fields_user_choice[$row->wpcc_field].'</td>
				</tr>
					';
					}
				}
			}
			$wpcc_mail_body .= '
			</table>
			';
			if($wpcc_mail_result_string != '2')
			{
				$wpcc_mail_body .= '
					<p>
						<b>'. __('Calculation of the amount', 'wpcc') .':</b> '.$_SESSION['wpcc_'.$wpcc_id]['calculation'].' 
					</p>
				';
			}
			if($wpcc_show_result != '2')
			{
				$wpcc_field_results = wpcc_field_results($wpcc_id);
				foreach($wpcc_field_results AS $wpcc_field_result_k => $wpcc_field_result_v)
				{
					$wpcc_session_name = $wpcc_field_result_k == 1 ? 'sum' : 'sum_'.$wpcc_field_result_k;
					$wpcc_mail_body .= '
						<p>
							<b>'.$wpcc_field_result_v['before'].'</b> '.$wpcc_session[$wpcc_session_name].' '.$wpcc_field_result_v['after'].' 
						</p>
					';
				}
			}
			if($wpcc_mail_text_footer != '2')
			{
				$wpcc_mail_body .= '
			<hr>
			<small>
				<p>'. __('Name calculator', 'wpcc') .': '.$wpcc_all_calc[$wpcc_id]['wpcc_name'].' - ID: '.$wpcc_id.'</p>
				<p><a href="http://www.zetrider.ru" target="_blank" title="'. __('Posted by plugin', 'wpcc') .'">Wordpress Creator Calculator</a></p>
			</small>
				';
			}

			$wpcc_mail_headers[] = 'From: '.(($wpcc_user_name != '')?$wpcc_user_name:'No name').' <'.(($wpcc_user_email != '')?$wpcc_user_email:get_option('admin_email')).'>';
			$wpcc_mail_headers[] = 'Content-type: text/html; charset=utf-8';
			
			$wp_mail_emails [] = $wpcc_mail_emailto;
			if($wpcc_mail_copies_user == '1' AND $wpcc_user_copy == '1' AND is_email($wpcc_user_email))
			{
				$wp_mail_emails [] = $wpcc_user_email;
			}
			wp_mail($wp_mail_emails, $wpcc_mail_subject.' '.$wpcc_user_name, $wpcc_mail_body, $wpcc_mail_headers);
			$success = '
		<div class="wpcc_mail wpcc_mail_success" id="wpcc_mail_scroll">
			<div class="wpcc_text">
				'.nl2br($wpcc_mail_text_success).'
			</div>
		</div>
			';
		}
		
		$return = array(
			'error' 	=> $error,
			'success' 	=> $success
		);
		if(isset($_POST['wpcc_mail_id']) AND !in_array('ajax', $option))
		{
			return $return;
		}
		if(in_array('ajax', $option))
		{
			return wpcc_json_encode_cyr($return);
		}
	}
}

/* WPCC ShortCode */
function wpcc_shortcode($atts) {
	global $wpdb, $wpcc_DB;
	extract(
		shortcode_atts(
			array(
				'id' 		=> '1', 
				'moderator' => 'false',
				'container' => 'page'
			), $atts
		)
	);
		
	$return 			= '';
	$return_hidden 		= '';
	$wpcc_id 			= (($_POST['wpcc_action'] > 0)?intval($_POST['wpcc_action']):intval($id));
	$wpcc_action 		= get_option('wpcc_action_'.$wpcc_id);
	$wpcc_submit 		= get_option('wpcc_submit_'.$wpcc_id);
	$wpcc_theme 		= get_option('wpcc_theme_'.$wpcc_id);
	$wpcc_enable_script = wpcc_option_array('wpcc_enable_script_'.$wpcc_id);
	$wpcc_autoscroll 	= get_option('wpcc_scroll_res_'.$wpcc_id, '1');
	$wpcc_structure_id 	= array();
	
	wp_enqueue_script('wpcc_js', WPCC_PLUGIN_URL . '/js/wpcc.js', '', filemtime( WPCC_PLUGIN_DIR . '/js/wpcc.js' ));
	
	if(!in_array('datepicker', $wpcc_enable_script))
	{
		wp_enqueue_script('wpcc_js_datepicker', WPCC_PLUGIN_URL . '/js/datepicker/jquery-ui-datepicker.js');
		wp_enqueue_style('wpcc_css_datepicker', WPCC_PLUGIN_URL . '/js/datepicker/jquery-ui-datepicker.css');
		if(WPLANG == 'ru_RU')
			wp_enqueue_script('wpcc_js_datepicker_ru', WPCC_PLUGIN_URL . '/js/datepicker/jquery-ui-datepicker-ru.js');
	}
	
	if(!in_array('slider', $wpcc_enable_script))
		wp_enqueue_script('jquery-ui-slider');
	
	if(count($wpcc_enable_script) != 2)
		wp_enqueue_script('jquery-ui-core');
	
	if($wpcc_theme != '' AND $wpcc_theme != 'none')
	{
		 wp_enqueue_style('wpcc_theme', WPCC_PLUGIN_URL . '/theme/'.$wpcc_theme.'/style.css');
	}
	$res = $wpdb->get_results("SELECT * FROM $wpcc_DB WHERE wpcc_id = '$wpcc_id' AND wpcc_type != 'cat' ORDER BY wpcc_order");

	if (count($res) == '0')
	{
		$return .= __("Formula is empty", "wpcc")."<br>"; 
	}
	else
	{
		if($_POST['wpcc_action'] > 0) /* to do */
		{
			if($wpcc_theme != '' AND $wpcc_theme != 'none')
				$return .= '<link rel="stylesheet" href="' . WPCC_PLUGIN_URL . '/theme/'.$wpcc_theme.'/style.css'.'" type="text/css" media="all" />';
			
			$return .= '<script type="text/javascript" src="' . WPCC_PLUGIN_URL . '/js/wpcc.js?'. filemtime( WPCC_PLUGIN_DIR . '/js/wpcc.js' ) .'"></script>';
		}
		$return .= '
<div class="wpcc'.(($container == 'sidebar')?' wpcc_widget':'').'">
	<form method="POST" action="'.(($wpcc_action == '')?'#wpcc_result':'').'" class="wpcc_form wpcc_form_'.$wpcc_id.'">
	';

		/* IF FIELD	*/
		$wpcc_form_if_hide 	= array();
		$wpcc_form_if_show 	= array();
		$wpcc_if_parse 		= wpcc_if_parse($wpcc_id);
		if(count($wpcc_if_parse['if_hide']) > '0')
		{
			$wpcc_form_if_hide = $wpcc_if_parse['if_hide'];
		}
		if(count($wpcc_if_parse['if_show']) > '0')
		{
			$wpcc_form_if_show = $wpcc_if_parse['if_show'];
		}
		/* IF FIELD END */
		
		foreach ($res as $row)
		{
			if($row->wpcc_type == 'field_type')
			{
				$wpcc_cache 	= wpcc_cache_field($res, $row->wpcc_field);
				$if_show_id 		= '';
				$if_hide_id 		= '';
				$wpcc_if_display 	= 'block';
				
				if(in_array($row->wpcc_field, $wpcc_form_if_hide) OR $wpcc_cache['hidden_display'] == '1')
				{
					$wpcc_if_display = 'none';
				}
				elseif(in_array($row->wpcc_field, $wpcc_form_if_show))
				{
					$wpcc_if_display = 'block';
				}
				
				$wpcc_f_title = $wpcc_cache['title'] == '' ? '&nbsp;' : stripslashes($wpcc_cache['title']);
				
				/* Visible Fields */
				if ($row->wpcc_value == "textblock")
				{			
					$return .= '
		<div class="wpcc_box wpcc_box_'.$row->wpcc_field.'" style="display: '.$wpcc_if_display.';">
			<div class="wpcc_description'.(($wpcc_cache['title'] == '')?' wpcc_description_empty':'').'">
				'.$wpcc_f_title.'
			</div>
			<div class="wpcc_fields wpcc_text wpcc_text_'.$row->wpcc_field.'">
				'.preg_replace("|\[session id=\"(.*)\"\](.*)\[/session\]|e", "\$_SESSION['wpcc_\\1']['\\2']", stripslashes(nl2br($wpcc_cache['text']))).'
			</div>
			<div class="wpcc_clear"></div>
		</div>
			';
				}
			
				if ($row->wpcc_value == "select")
				{
					$return .= '
		<div class="wpcc_box wpcc_box_'.$row->wpcc_field.'" style="display: '.$wpcc_if_display.';">
			<div class="wpcc_description'.(($wpcc_cache['title'] == '')?' wpcc_description_empty':'').'">
				'.$wpcc_f_title.'
			</div>
			<div class="wpcc_fields">
				<select name="wpcc_structure['.$row->wpcc_field.']" class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.' wpcc_select wpcc_select_'.$row->wpcc_field.'" data-fid="'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-data="'.$wpcc_cache['data'].'">';
						if(wpcc_check_list($wpcc_cache['list']))
						{
							$wpcc_value_list_arr = unserialize(wpcc_check_list($wpcc_cache['list']));
							foreach($wpcc_value_list_arr AS $wpcc_value_list_k => $wpcc_value_list_v)
							{
								$return .= '
					<option value="'.wpcc_esc_attr($wpcc_value_list_v['val']).'"'.(($wpcc_value_list_v['val'] == $_POST['wpcc_structure'][$row->wpcc_field])?' selected':'').' data-images="'.wpcc_esc_attr($wpcc_value_list_v['img']).'">'.$wpcc_value_list_v['txt'].'</option>';
							}
						}
						$return .= '
				</select>
			</div>
			<div class="wpcc_clear"></div>
		</div>
			';
				}

				if ($row->wpcc_value == "checkbox")
				{
					$return .= '
		<div class="wpcc_box wpcc_box_'.$row->wpcc_field.'" style="display: '.$wpcc_if_display.';">
			<div class="wpcc_description'.(($wpcc_cache['title'] == '')?' wpcc_description_empty':'').'">
				'.$wpcc_f_title.'
			</div>';
					
					if(wpcc_check_list($wpcc_cache['list']))
					{
						$return .= '
			<div class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.' wpcc_fields wpcc_checkbox wpcc_checkbox_'.$row->wpcc_field.'" data-action="'.$wpcc_cache['action'].'" data-fid="'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-default="'.$wpcc_cache['default'].'" data-data="'.$wpcc_cache['data'].'">';
			
						$wpcc_value_list_arr = unserialize(wpcc_check_list($wpcc_cache['list']));
						foreach($wpcc_value_list_arr AS $wpcc_value_list_k => $wpcc_value_list_v)
						{
							$return .= '
				<label>
				'. (($wpcc_value_list_v['img'] != '') ? '<div class="images"><img src="'.wpcc_esc_attr($wpcc_value_list_v['img']).'"></div>' : '' ) .'
				<input type="checkbox" name="wpcc_structure['.$row->wpcc_field.']['.$wpcc_value_list_k.']" value="'.wpcc_esc_attr($wpcc_value_list_v['val']).'"'.((($wpcc_value_list_v['val'] == $_POST['wpcc_structure'][$row->wpcc_field][$wpcc_value_list_k]))?' checked':'').' data-i="'.$wpcc_value_list_k.'"> '.$wpcc_value_list_v['txt'].'
				</label>';
						}
						$return .= '
			</div>';
					}
					
				$return .= '
			<div class="wpcc_clear"></div>
		</div>
					';
				}

				if ($row->wpcc_value == "radio")
				{
					$return .= '
		<div class="wpcc_box wpcc_box_'.$row->wpcc_field.'" style="display: '.$wpcc_if_display.';">
			<div class="wpcc_description'.(($wpcc_cache['title'] == '')?' wpcc_description_empty':'').'">
				'.$wpcc_f_title.'
			</div>';
					if(wpcc_check_list($wpcc_cache['list']))
					{
						$return .= '
			<div class="wpcc_fields wpcc_radio wpcc_radio_'.$row->wpcc_field.'">';
			
						$wpcc_value_list_arr = unserialize(wpcc_check_list($wpcc_cache['list']));
						foreach($wpcc_value_list_arr AS $wpcc_value_list_k => $wpcc_value_list_v)
						{
							$return .= '
				<label>
				'. (($wpcc_value_list_v['img'] != '') ? '<div class="images"><img src="'.wpcc_esc_attr($wpcc_value_list_v['img']).'"></div>' : '' ) .'
				<input type="radio" name="wpcc_structure['.$row->wpcc_field.']" value="'.wpcc_esc_attr($wpcc_value_list_v['val']).'"'.((($wpcc_value_list_v['val'] == $_POST['wpcc_structure'][$row->wpcc_field]) OR ($wpcc_value_list_k == '0' AND $_POST['wpcc_structure'][$row->wpcc_field] == ''))?' checked':'').' class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-fid="'.$row->wpcc_field.'" data-data="'.$wpcc_cache['data'].'"> '.$wpcc_value_list_v['txt'].'
				</label>';
						}
						$return .= '
			</div>';
					}
					
					$return .= '
			<div class="wpcc_clear"></div>
		</div>
		';
				}
				
				if ($row->wpcc_value == "inputtext")
				{
					$return .= '
		<div class="wpcc_box wpcc_box_'.$row->wpcc_field.'" style="display: '.$wpcc_if_display.';">
			<div class="wpcc_description'.(($wpcc_cache['title'] == '')?' wpcc_description_empty':'').'">
				'.$wpcc_f_title.'
			</div>
			<div class="wpcc_fields">
				<input type="text" name="wpcc_structure['.$row->wpcc_field.']" value="'.(($_POST['wpcc_structure'][$row->wpcc_field] != '')?wpcc_esc_attr($_POST['wpcc_structure'][$row->wpcc_field]):'').'" class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.' wpcc_inputtext wpcc_inputtext_'.$row->wpcc_field.''.(($wpcc_cache['validation'] != '')?' wpcc_jq_validation':'').'" placeholder="'.wpcc_esc_attr($wpcc_cache['placeholder']).'" data-type="'.$row->wpcc_value.'" data-fid="'.$row->wpcc_field.'" data-action="'.$wpcc_cache['action'].'" data-price="'.$wpcc_cache['price'].'" data-default="'.$wpcc_cache['default'].'" data-validation="'.$wpcc_cache['validation'].'" data-maxchar="'.$wpcc_cache['maxchar'].'" data-exclude="'.$wpcc_cache['exclude'].'" data-data="'.$wpcc_cache['data'].'">
			</div>
			<div class="wpcc_clear"></div>
		</div>
			';
				}
				
				if ($row->wpcc_value == "date")
				{
					$date_fomat = $wpcc_cache['datadate'] == 'string' ? 'd-m-Y' : 'Y-m-d';
					
					$return .= '
		<div class="wpcc_box wpcc_box_'.$row->wpcc_field.'" style="display: '.$wpcc_if_display.';">
			<div class="wpcc_description'.(($wpcc_cache['title'] == '')?' wpcc_description_empty':'').'">
				'.$wpcc_f_title.'
			</div>
			<div class="wpcc_fields wpcc_fields_date">
				<div class="wpcc_date wpcc_date_'.$row->wpcc_field.'">
				<input type="text" class="wpcc_inputdisabled" value="'.wpcc_date_conv('', $wpcc_cache['date'], 'd-m-Y').'" disabled>
				<input type="hidden" name="wpcc_structure['.$row->wpcc_field.']" value="'.( ($_POST['wpcc_structure'][$row->wpcc_field] != '') ? wpcc_esc_attr($_POST['wpcc_structure'][$row->wpcc_field]) : wpcc_date_conv('',$wpcc_cache['date'], $date_fomat) ).'" class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.' wpcc_jq_datepicker" data-type="'.$row->wpcc_value.'" data-fid="'.$row->wpcc_field.'" data-default="'.$wpcc_cache['date'].'" data-datemin="'.$wpcc_cache['datemin'].'" data-datemax="'.$wpcc_cache['datemax'].'" data-data="data_date" data-datadate="'.$wpcc_cache['datadate'].'">
				</div>
			</div>
			<div class="wpcc_clear"></div>
		</div>
			';
				}
				
				if ($row->wpcc_value == "custom_fields")
				{
					$custom_fields_get = get_post_meta($wpcc_cache['singular_id'], $wpcc_cache['singular_key'], true);
					if($custom_fields_get == '')
					{
						$custom_fields_get = $wpcc_cache['default'];
					}
					$return .= '
		<div class="wpcc_box wpcc_box_'.$row->wpcc_field.'" style="display: '.$wpcc_if_display.';">
			<div class="wpcc_description'.(($wpcc_cache['title'] == '')?' wpcc_description_empty':'').'">
				'.$wpcc_f_title.'
			</div>
			<div class="wpcc_fields">
				<input type="text" name="wpcc_structure['.$row->wpcc_field.']" value="'.(($_POST['wpcc_structure'][$row->wpcc_field] != '')?wpcc_esc_attr($_POST['wpcc_structure'][$row->wpcc_field]):$custom_fields_get).'" class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.' wpcc_custom_fields wpcc_custom_fields_'.$row->wpcc_field.''.(($wpcc_cache['validation'] != '')?' wpcc_jq_validation':'').'" data-type="'.$row->wpcc_value.'" data-fid="'.$row->wpcc_field.'" data-default="'.$wpcc_cache['default'].'" data-validation="'.$wpcc_cache['validation'].'" data-data="'.$wpcc_cache['data'].'">
			</div>
			<div class="wpcc_clear"></div>
		</div>
			';
				}
				
				if ($row->wpcc_value == "slider")
				{
					$slider_val = (($_POST['wpcc_structure'][$row->wpcc_field] != '')?wpcc_price($_POST['wpcc_structure'][$row->wpcc_field]):wpcc_price($wpcc_cache['default']));
					$return .= '
		<div class="wpcc_box wpcc_box_'.$row->wpcc_field.'" style="display: '.$wpcc_if_display.';">
			<div class="wpcc_description'.(($wpcc_cache['title'] == '')?' wpcc_description_empty':'').'">
				'.$wpcc_f_title.'
			</div>
			<div class="wpcc_fields wpcc_jq_slider_'.$wpcc_cache['slider_position'].'">
				<div class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.' wpcc_jq_slider wpcc_jq_slider_box wpcc_jq_slider_box_'.$row->wpcc_field.'" data-fid="'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-default="'.$wpcc_cache['default'].'" data-value="'.$slider_val.'" data-min="'.$wpcc_cache['slider_min'].'" data-max="'.$wpcc_cache['slider_max'].'" data-position="'.$wpcc_cache['slider_position'].'" data-step="'.$wpcc_cache['slider_step'].'" data-data="'.$wpcc_cache['data'].'"></div>
				<input type="text" name="wpcc_structure['.$row->wpcc_field.']" value="'.$slider_val.'" class="wpcc_jq_slider_text wpcc_jq_validation" data-validation="only_numbers_one_dot">
			</div>
			<div class="wpcc_clear"></div>
		</div>
			';	
				}
				
				/* Hidden Fields */
				if ($row->wpcc_value == "if")
				{
					$if_show_id = (($wpcc_cache['if_show'] == '')?'0':$wpcc_cache['if_show']);
					$if_hide_id = (($wpcc_cache['if_hide'] == '')?'0':$wpcc_cache['if_hide']);
					$return_hidden .= '
		<div class="wpcc_jq_if" data-if_id="'.$wpcc_cache['if_id'].'" data-val="'.wpcc_esc_attr($wpcc_cache['if_val']).'" data-show="'.$if_show_id.'" data-hide="'.$if_hide_id.'" data-operator="'.(($wpcc_cache['if_operator'] == '')?'==':$wpcc_cache['if_operator']).'" data-logic="'.(($wpcc_cache['if_logic'] == '')?'ifelse':$wpcc_cache['if_logic']).'"></div>				
		<input type="hidden" name="wpcc_structure_if[]" value="'.$row->wpcc_field.'">';
				}
				
				if ($row->wpcc_value == "ifhidden")
				{
					$return_hidden .= '
		<input type="hidden" name="wpcc_structure['.$row->wpcc_field.']" value="'.$wpcc_cache['default'].'" class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.'" data-fid="'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-default="'.$wpcc_cache['default'].'" data-data="'.$wpcc_cache['data'].'">';
				}
				
				if ($row->wpcc_value == "hidden")
				{
					$return_hidden .= '
		<input type="hidden" name="wpcc_structure['.$row->wpcc_field.']" value="'.$wpcc_cache['price'].'" class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.'" data-fid="'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-data="'.$wpcc_cache['data'].'">';	
				}
				
				if ($row->wpcc_value == "session")
				{
					$wpcc_session = $_SESSION['wpcc_'.$wpcc_cache['sess_calc_id']][$wpcc_cache['sess_calc_results']];
					$return_hidden .= '
		<input type="hidden" name="wpcc_structure['.$row->wpcc_field.']" value="'.(($wpcc_session == '')?wpcc_esc_attr($wpcc_cache['default']):wpcc_esc_attr($wpcc_session)).'" class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.'" data-fid="'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-default="'.$wpcc_cache['default'].'" data-data="'.$wpcc_cache['data'].'">';
				}
				
				if ($row->wpcc_value == "jquery")
				{
					$return_hidden .= '
		<input type="hidden" name="wpcc_structure['.$row->wpcc_field.']" class="wpcc_jq_copy wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.'" value="'.$wpcc_cache['default'].'" data-get_id="'.$wpcc_cache['jq_id'].'" data-fid="'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-default="'.$wpcc_cache['default'].'" data-data="'.$wpcc_cache['data'].'">';	
				}
				
				if ($row->wpcc_value == "armtc")
				{
					$return_hidden .= '
		<input type="hidden" name="wpcc_structure['.$row->wpcc_field.']" value="'.$wpcc_cache['armtc_fn'].'">';	
				}
				
				if ($row->wpcc_value == "result_fields")
				{
					$rf_value = (($_POST['wpcc_structure'][$row->wpcc_field] != '')?wpcc_price($_POST['wpcc_structure'][$row->wpcc_field]):wpcc_price($wpcc_cache['default']));
					$return_hidden .= '
		<input type="hidden" name="wpcc_structure['.$row->wpcc_field.']" class="wpcc_jq_action wpcc_jq_action_'.$row->wpcc_field.' wpcc_result_fields" value="'.$rf_value.'" data-action="'.$wpcc_cache['action'].'" data-fileds="'.$wpcc_cache['rf_fields'].'" data-fid="'.$row->wpcc_field.'" data-type="'.$row->wpcc_value.'" data-default="'.$wpcc_cache['default'].'" data-data="'.$wpcc_cache['datarf'].'">';
				}
				
				if ($row->wpcc_value != "textblock" AND $row->wpcc_value != "if")
				{ 
					$wpcc_structure_id []= $row->wpcc_field;
				}
			}
		}
		
		$return .= $return_hidden;
		
		$return .= '
		<div class="wpcc_clear"></div>
		<input type="hidden" name="wpcc_structure_id" class="wpcc_structure_id" value="'.wpcc_esc_attr(implode(',',$wpcc_structure_id)).'">
		<input type="hidden" name="wpcc_id" class="wpcc_id" value="'.$wpcc_id.'" data-autoscroll="'.(($wpcc_autoscroll == '1' )?'y':'n').'">
		<input type="hidden" name="wpcc_action" class="wpcc_action" value="'.intval($wpcc_action).'">
		<input type="hidden" name="wpcc_form_url" class="wpcc_url" value="'.WPCC_PLUGIN_URL.'">
		'. ((is_admin())?'<input type="hidden" name="wpcc_moderator" value="true">':'') .'
		<input type="hidden" name="action" value="wpcc_ajax_result">
		<input type="submit" value="'.$wpcc_submit.'" name="wpcc_calculate" class="wpcc_submit wpcc_submit_'.$wpcc_id.'">
		<div class="wpcc_loading"><div></div></div>
	</form>
		';
	}
	$return .= '
	<div class="wpcc_result_block wpcc_result_block_'.$wpcc_id.'">
	';
	
	if (isset($_POST['wpcc_calculate']))
	{
		$return .= wpcc_result($_POST, $moderator);		
	}
	
	if (isset($_POST['wpcc_calculate']) OR isset($_POST['wpcc_mail_id']))
	{
		$return .= wpcc_mail_form($wpcc_id);
	}
	
	$return .='
	</div>
</div>
	';
	return $return;
}
add_shortcode('wpcc', 'wpcc_shortcode');

class WPCC_Widget extends WP_Widget {
function __construct() {
	parent::__construct(
		'WPCC_Widget', 
		'WPCC', 
		array( 'description' => 'Creator Calculator Widget', ) 
	);
}
public function widget( $args, $instance ) {
	$title = apply_filters( 'widget_title', $instance['title'] );
	echo $args['before_widget'];
	if ( ! empty( $title ) )
	echo $args['before_title'] . $title . $args['after_title'];
	echo do_shortcode('[wpcc id="'.$instance['wpcc_id'].'" container="sidebar"]');
	echo $args['after_widget'];
}
public function form( $instance ) {
	global $wpdb, $wpcc_DB;
	$title = esc_attr($instance['title']);
	$wpcc_id = esc_attr($instance['wpcc_id']);
?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wpcc'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</label>
	</p>
	
	<p>
		<label><?php _e('Calculator', 'wpcc'); ?>:</label>
		<select name="<?php echo $this->get_field_name('wpcc_id'); ?>" style="width:170px;">
			<option value="0"><?php _e('Select the calculator', 'wpcc'); ?></option>
			<?php
			$wpcc_a = $wpdb->get_results("SELECT wpcc_id, wpcc_value FROM $wpcc_DB WHERE wpcc_type = 'cat'");
			if(count($wpcc_a) > '0')
			{
				foreach($wpcc_a as $wpcc_a_r)
				{
					echo '<option value="'.$wpcc_a_r->wpcc_id.'" '.(($wpcc_a_r->wpcc_id == $wpcc_id)?'selected':'').' >[ID-'.$wpcc_a_r->wpcc_id.'] '.$wpcc_a_r->wpcc_value.'</option>';
				}
			}
			?>
		</select>
	</p>
<?php 
}
public function update( $new_instance, $old_instance ) {
	$instance = array();
	$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
	$instance['wpcc_id'] = ( ! empty( $new_instance['wpcc_id'] ) ) ? intval( $new_instance['wpcc_id'] ) : '';
	return $instance;
}
}
function WPCC_load_widget() {
	register_widget( 'WPCC_Widget' );
}
add_action( 'widgets_init', 'WPCC_load_widget' );

function wpcc_json_encode_cyr($str) {
	$arr_utf = array('\u0410', '\u0430','\u0411','\u0431','\u0412','\u0432',
	'\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',
	'\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',
	'\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',
	'\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',
	'\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',
	'\u0448','\u0429','\u0449','\u042a','\u044a','\u042b','\u044b','\u042c','\u044c',
	'\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');
	$arr_cyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е',
	'Ё', 'ё', 'Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о',
	'П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш',
	'Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я');
	$str_json 		= json_encode($str);
	$str_replace	= str_replace($arr_utf, $arr_cyr, $str_json);
	return $str_replace;
}
/* Success :) */