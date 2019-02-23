<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the section-related functionality in the UI.
 *
 * @since 3.0
 */
class Section extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Section', 'ultimate-fields' );
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
			Field::create( 'icon', 'section_icon', __( 'Icon', 'ultimate-fields' ) )
				->add_set( 'dashicons' ),
			Field::create( 'select', 'section_color', __( 'Color', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_default_value( 'white' )
				->add_options(array(
					'white' => __( 'White', 'ultimate-fields' ),
					'blue'  => __( 'Blue', 'ultimate-fields' ),
					'red'   => __( 'Red', 'ultimate-fields' ),
					'green' => __( 'Green', 'ultimate-fields' )
				)),
 		);

 		return array(
 			'general' => $fields
 		);
 	}
}
