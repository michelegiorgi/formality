<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the text-field related functionality in the UI.
 *
 * @since 3.0
 */
class Text extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Text', 'ultimate-fields' );
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
			Field::create( 'text', 'default_value_text', __( 'Default value', 'ultimate-fields' ) ),
			Field::create( 'complex', 'text_attributes', __( 'Attributes', 'ultimate-fields' ) )
				->merge()
				->set_description( __( 'Customize the field to make it feel more distinguishable and intuitive.', 'ultimate-fields' ) )
				->add_fields(array(
					Field::create( 'text', 'text_placeholder', __( 'Placeholder text', 'ultimate-fields' ) )
						->set_width( 33 )
						->set_placeholder( __( 'Placeholder text', 'ultimate-fields' ) ),
					Field::create( 'text', 'prefix', __( 'Prefix', 'ultimate-fields' ) )
						->set_prefix ( __( 'Prefix', 'ultimate-fields' ) )
						->set_width( 33 ),
					Field::create( 'text', 'suffix', __( 'Suffix', 'ultimate-fields' ) )
						->set_suffix ( __( 'Suffix', 'ultimate-fields' ) )
						->set_width( 33 ),
				)),
			Field::create( 'textarea', 'suggestions', __( 'Autocomplete Suggestions', 'ultimate-fields' ) )
				->set_description( __( "You may list predefined values here, one value per row.\n\nOnce the user starts typing into the field, those suggestions will appear if the input matches any of them.", 'ultimate-fields' ) )
 		);

		$output = array(
			Field::create( 'select', 'output_format_value', __( 'Format Value', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options( array(
					'none' => __( 'None', 'ultimate-fields' ),
					'html' => __( 'HTML Entities', 'ultimate-fields' )
				) )
				->set_default_value( 'html' )
		);

 		return array(
 			'general' => $fields,
 			'output'  => $output
 		);
 	}

	/**
	 * Prepares additional arguments for $field->import()
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data the data about the field.
	 */
	public function prepare_setup_data() {
		$data = parent::prepare_setup_data( $this->meta );

		if( isset( $this->meta[ 'output_format_value' ] ) && $this->meta[ 'output_format_value' ] ) {
			$data[ 'output_format' ] = $this->meta[ 'output_format_value' ];
		}

		return $data;
	}

	/**
	 * Imports data about the field from post meta.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data the data about the field.
	 */
	public function import( $meta ) {
		if( isset( $meta[ 'text_placeholder' ] ) ) {
			$meta[ 'placeholder' ] = $meta[ 'text_placeholder' ];
		}

		parent::import( $meta );
	}

	/**
	 * Prepares data for import.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data that has been already generated + source data.
	 * @return mixed[]
	 */
	public static function prepare_field_data( $data ) {
		if( isset( $data[ 'suggestions' ] ) && is_array( $data[ 'suggestions' ] ) ) {
			$data[ 'suggestions' ] = implode( "\n", $data[ 'suggestions' ] );
		}

		if( isset( $data[ 'placeholder' ] ) ) {
			$data[ 'text_placeholder' ] = $data[ 'placeholder' ];
		}

		return $data;
	}
}
