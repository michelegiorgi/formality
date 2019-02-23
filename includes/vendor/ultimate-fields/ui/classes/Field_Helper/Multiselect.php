<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the multiselect field in the UI.
 *
 * @since 3.0
 */
class Multiselect extends Select {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Multiselect', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$existing[ 'select_options_type' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Multiselect' );
		$existing[ 'select_options' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Multiselect' )
			->add_dependency( 'select_options_type', 'manual' );
		$existing[ 'select_post_type' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Multiselect' )
			->add_dependency( 'select_options_type', 'posts' );
		$existing[ 'select_orientation' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Multiselect' )
			->add_dependency( 'multiselect_input_type', 'checkbox' );
		$existing[ 'select_output_data_type' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Multiselect' );

		$general_fields = array(
			Field::create( 'select', 'multiselect_input_type', __( 'Input type', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_orientation( 'vertical' )
				->add_options(array(
					'dropdown' => __( 'Multiselect', 'ultimate-fields' ),
					'checkbox' => __( 'Checkboxes', 'ultimate-fields' )
				))
		);

		$output_fields = array(
			Field::create( 'select', 'multiselect_output_format', __( 'Output Format', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_default_value( 'comma' )
				->add_options(array(
					'comma'      => __( 'Separate the values with commas', 'ultimate-fields' ),
					'ordered'    => __( 'Ordered list', 'ultimate-fields' ),
					'unordered'  => __( 'Unordered list', 'ultimate-fields' ),
					'paragraphs' => __( 'Paragraphs', 'ultimate-fields' ),
				))
		);

		return array(
			'general' => $general_fields,
			'output'  => $output_fields
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

		$options = array();
		// foreach( $meta[ 'select_options' ] as $option ) {
		// 	$options[ $option[ 'value' ] ] = $option[ 'label' ];
		// }

		$this->options = $options;
	}
}
