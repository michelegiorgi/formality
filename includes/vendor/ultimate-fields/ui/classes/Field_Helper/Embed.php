<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Adds the neccessary UI functionality for the embed field.
 *
 * @since 3.0
 */
class Embed extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Embed', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$general_fields = array(
			Field::create( 'embed', 'default_value_embed', __( 'Default value', 'ultimate-fields' ) ),
		);

		return array(
			'general' => $general_fields
		);
	}
}
