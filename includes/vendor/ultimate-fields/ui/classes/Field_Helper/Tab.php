<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the tab-related functionality in the UI.
 *
 * @since 3.0
 */
class Tab extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Tab', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields() {
		$fields = array(
			Field::create( 'icon', 'tab_icon', __( 'Icon', 'ultimate-fields' ) )
				->add_set( 'dashicons' )
 		);

 		return array(
 			'general' => $fields
 		);
 	}
}
