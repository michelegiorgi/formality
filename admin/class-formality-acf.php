<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/admin
 */


class acf_field_uid extends acf_field {

	/*
	*  __construct
	*/
	function __construct() {
		$this->name = 'uid';
		$this->label = 'UID';
		$this->category = 'layout';
		$this->l10n = array();
    parent::__construct();
	}

	/*
	*  render_field()
	*/
	function render_field( $field ) {
		echo '<input type="text" readonly="readonly" name="'. esc_attr($field['name']) .'" value="' .esc_attr($field['value']).'" />';
	}

	/*
	*  update_value()
	*/
	function update_value( $value, $post_id, $field ) {
		if (!$value) { $value = uniqid(); }
		return $value;
	}

	/*
	*  validate_value()
	*/
	function validate_value( $valid, $value, $field, $input ){
		return true;
	}
}


// create field
new acf_field_uid();