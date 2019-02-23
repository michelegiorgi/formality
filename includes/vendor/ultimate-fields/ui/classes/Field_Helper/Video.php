<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Adds the neccessary UI functionality for the video field.
 *
 * @since 3.0
 */
class Video extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Video', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$general_fields = array(
			Field::create( 'video', 'default_value_video', __( 'Default value', 'ultimate-fields' ) )
		);

		$output_fields = array(
			Field::create( 'text', 'video_output_width', __( 'Width', 'ultimate-fields' ) )
				->set_default_value( '1280' ),
			Field::create( 'text', 'video_output_height', __( 'Height', 'ultimate-fields' ) )
				->set_default_value( '720' )
		);

		return array(
			'general' => $general_fields,
			'output'  => $output_fields
		);
	}
}
