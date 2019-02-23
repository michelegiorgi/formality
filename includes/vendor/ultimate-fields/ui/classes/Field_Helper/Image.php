<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Adds the neccessary UI functionality for the image field.
 *
 * @since 3.0
 */
class Image extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Image', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$general_fields = array(
			Field::create( 'image', 'default_value_image', __( 'Default value', 'ultimate-fields' ) ),
		);

		$output_fields = array(
			Field::create( 'select', 'image_output_type', __( 'Output Type', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options( array(
					'image' => __( 'Image', 'ultimate-fields' ),
					'link'  => __( 'Link', 'ultimate-fields' ),
					'url'   => __( 'URL', 'ultimate-fields' ),
					'id'    => __( 'ID', 'ultimate-fields' ),
				))
				->add_dependency( 'type', 'Image' ),

			Field::create( 'select', 'image_size', __( 'Image Size', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options( ultimate_fields()->get_image_sizes() )
				->add_dependency( 'type', 'Image' )
				->add_dependency( 'image_output_type', array( 'image', 'link' ), 'IN' )
				->set_description( __( 'Those are the available image sizes. If you want to add additional ones, use the add_image_size() function.', 'ultimate-fields' ) )
		);

		return array(
			'general' => $general_fields,
			'output'  => $output_fields
		);
	}
}
