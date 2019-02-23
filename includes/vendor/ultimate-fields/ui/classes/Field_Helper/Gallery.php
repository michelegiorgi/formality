<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Adds the neccessary UI functionality for the gallery field.
 *
 * @since 3.0
 */
class Gallery extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Gallery', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$general_fields = array(
			Field::create( 'gallery', 'default_value_gallery', __( 'Default value', 'ultimate-fields' ) )
		);

		$output_fields = array();

		return array(
			'general' => $general_fields,
			'output'  => $output_fields
		);
	}
}
