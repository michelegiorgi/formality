<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the password-field related functionality in the UI.
 *
 * @since 3.0
 */
class Password extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Password', 'ultimate-fields' );
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
			Field::create( 'text', 'default_value_password', __( 'Default value', 'ultimate-fields' ) ),
			Field::create( 'complex', 'password_attributes', __( 'Attributes', 'ultimate-fields' ) )
				->merge()
				->set_description( __( 'Customize the field to make it feel more distinguishable and intuitive.', 'ultimate-fields' ) )
				->add_fields(array(
					Field::create( 'text', 'password_placeholder', __( 'Placeholder text', 'ultimate-fields' ) )
						->set_width( 33 )
						->set_placeholder( __( 'Placeholder text', 'ultimate-fields' ) ),
					Field::create( 'text', 'password_prefix', __( 'Prefix', 'ultimate-fields' ) )
						->set_prefix ( __( 'Prefix', 'ultimate-fields' ) )
						->set_width( 33 ),
					Field::create( 'text', 'password_suffix', __( 'Suffix', 'ultimate-fields' ) )
						->set_suffix ( __( 'Suffix', 'ultimate-fields' ) )
						->set_width( 33 ),
				))
 		);

		$output = array();

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

		if( isset( $this->meta[ 'password_prefix' ] ) ) {
			$data[ 'prefix' ] = $this->meta[ 'password_prefix' ];
		}

		if( isset( $this->meta[ 'password_suffix' ] ) ) {
			$data[ 'suffix' ] = $this->meta[ 'password_suffix' ];
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
		if( isset( $meta[ 'password_placeholder' ] ) ) {
			$meta[ 'placeholder' ] = $meta[ 'password_placeholder' ];
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
			$data[ 'password_placeholder' ] = $data[ 'placeholder' ];
		}

		return $data;
	}

	/**
	 * Enqueues the password fields' templates and sripts.
	 *
	 * @since 3.0
	 */
	public static function enqueue() {
		wp_enqueue_script( 'uf-field-password' );
	}
}
