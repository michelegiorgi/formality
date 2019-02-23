<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the icon-field related functionality in the UI.
 *
 * @since 3.0
 */
class Icon extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Icon', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields( $existing ) {
		$sets = array(
			'font-awesome' => __( 'Font Awesome', 'ultimate-fields' ),
			'dashicons'    => __( 'Dashicons', 'ultimate-fields' )
		);

		/**
		 * Allows the list of usable sets to be modified for the UI.
		 *
		 * @since 3.0
		 *
		 * @param string[] $sets The sets along with their names.
		 * @return string[]
		 */
		$sets = apply_filters( 'uf.ui.icon_sets', $sets );

		$fields = array(
			FIeld::create( 'multiselect', 'icon_sets', __( 'Icon sets', 'ultimate-fields' ) )
				->add_options( $sets )
				->set_default_value( array_keys( $sets ) )
				->set_input_type( 'checkbox' ),
			Field::create( 'icon', 'default_value_icon', __( 'Default value', 'ultimate-fields' ) )
				->set_description( __( 'Please select an icon from one of the sets you selected in the previous field, otherwise it will be ignored.', 'ultimate-fields' ) )
		);

		$output = array(
			Field::create( 'select', 'icon_output_format', __( 'Output Format', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options(array(
					'class' => __( 'CSS Class', 'ultimate-fields' ),
					'icon'  => __( 'The full icon <em>(A span HTML element)</em>', 'ultimate-fields' )
				))
				->set_description( __( 'Please make sure, that the appropriate CSS styles for Dash Icons or Font Awesome are included.', 'ultimate-fields' ) )
		);

		return array(
			'general' => $fields,
			'output'  => $output
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
