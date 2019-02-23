<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the number-field related functionality in the UI.
 *
 * @since 3.0
 */
class Number extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Number', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields( $existing ) {
		$fields = array(
			Field::create( 'number', 'default_value_number', __( 'Default value', 'ultimate-fields' ) ),
			Field::create( 'checkbox', 'number_slider', __( 'Slider', 'ultimate-fields' ) )
				->set_text( __( 'Enable', 'ultimate-fields' ) )
				->set_description( __( 'Enables the jQuery UI slider for the field.', 'ultimate-fields' ) ),
			Field::create( 'number', 'number_minimum', __( 'Minumum', 'ultimate-fields' ) )
				->set_description( __( 'This is the minimum value that can be selected through the field.', 'ultimate-fields' ) )
				->set_default_value( 1 ),
			Field::create( 'number', 'number_maximum', __( 'Maximum', 'ultimate-fields' ) )
				->set_description( __( 'This is the maximum value that can be selected through the field.', 'ultimate-fields' ) )
				->set_default_value( 100 ),
			Field::create( 'number', 'number_step', __( 'Step', 'ultimate-fields' ) )
				->set_default_value( 1 )
				->add_dependency( 'number_slider' ),
		);

		return array(
			'general' => $fields
		);
 	}

	/**
	 * Imports data about the field from post meta.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data the data about the field.
	 */
	public function import_meta( $meta ) {
		parent::import_meta( $meta );


	}
}
