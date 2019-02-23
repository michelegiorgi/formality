<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Adds the neccessary UI functionality for the audio field.
 *
 * @since 3.0
 */
class Audio extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Audio', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$general_fields = array(
			Field::create( 'audio', 'default_value_audio', __( 'Default value', 'ultimate-fields' ) )
		);

		return array(
			'general' => $general_fields
		);
	}
}
