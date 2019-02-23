<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the checkbox-field related functionality in the UI.
 *
 * @since 3.0
 */
class Checkbox extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Checkbox', 'ultimate-fields' );
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
			Field::create( 'text', 'checkbox_text', __( 'Text', 'ultimate-fields' ) )
				->set_description( __( 'This text will be displayed next to the checkbox.', 'ultimate-fields' ) ),
			Field::create( 'checkbox', 'fancy_checkbox', __( 'Fancy', 'ultimate-fields' ) )
				->fancy()
				->set_text( __( 'Enchance the field with CSS', 'ultimate-fields' ) )
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