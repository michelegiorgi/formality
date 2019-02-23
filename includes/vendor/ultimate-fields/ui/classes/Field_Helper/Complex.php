<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\UI\Container_Helper;
use Ultimate_Fields\Field;
use Ultimate_Fields\Template;
use Ultimate_Fields\UI\Post_Type;

/**
 * Handles the controls for the complex field.
 *
 * @since 3.0
 */
class Complex extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Complex', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields() {
		$containers = array();
		$post_type  = Post_Type::instance();

		foreach( $post_type->get_existing() as $container ) {
			$containers[ $post_type->get_container_hash( $container ) ] = esc_html( $container->post_title );
		}

		$fields = array(
			Field::create( 'select', 'complex_fields_source', __( 'Field source', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options(array(
					'manual'    => __( 'Enter manually', 'ultimate-fields' ),
					'container' => __( 'Load a container', 'ultimate-fields' )
				))
				->set_description( __( 'Select whether you prefer enting your fields manually or load them from another container', 'ultimate-fields' ) ),
			Field::create( 'fields', 'complex_fields', __( 'Fields', 'ultimate-fields' ) )
				->add_dependency( 'complex_fields_source', 'manual' ),
			Field::create( 'select', 'complex_container', __( 'Source container', 'ultimate-fields' ) )
				->add_dependency( 'complex_fields_source', 'container' )
				->add_options( $containers )
				->set_input_type( 'radio' ),
			Field::create( 'select', 'complex_save_mode', __( 'Save mode', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options(array(
					'array' => __( 'Save sub-values as an array', 'ultimate-fields' ),
					'merge' => __( 'Merge sub-fields into the parent level', 'ultimate-fields' )
				)),
			Field::create( 'text', 'complex_prefix', __( 'Prefix', 'ultimate-fields' ) )
				->add_dependency( 'complex_save_mode', 'merge' )
				->set_description( __( 'When merging values into the parent level, you can prefix them.', 'ultimate-fields' ) )
		);

		$appearance = array(
			Field::create( 'select', 'complex_layout', __( 'Layout', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_orientation( 'horizontal' )
				->set_default_value( 'grid' )
				->add_options(array(
					'grid' => __( 'Grid', 'ultimate-fields' ),
					'rows' => __( 'Rows', 'ultimate-fields' )
				))
		);

 		return array(
 			'general'    => $fields,
			'appearance' => $appearance
 		);
 	}

	/**
	 * Enqueues the field manually, as it's not being used separately.
	 *
	 * @since 3.0
	 */
	public static function enqueue() {
		Template::add( 'complex-group', 'field/complex-group' );
		wp_enqueue_script( 'uf-field-complex' );
	}

	/**
	 * Imports some meta into the class.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data The settings of the field.
	 */
	public function import( $field_data ) {
		if( ! isset( $field_data[ 'complex_fields_source' ] ) || 'container' != $field_data[ 'complex_fields_source' ] ) {
			$fields = isset( $field_data[ 'complex_fields' ] ) && is_array( $field_data[ 'complex_fields' ] )
				? $field_data[ 'complex_fields' ]
				: array();

			$prepared = array();

			foreach( $fields as $field ) {
				$helper = Field_Helper::import_from_meta( $field );
				$prepared[] = $helper->setup_field();
			}

			# Go through each field and set it up the same way as this one
			$field_data[ 'complex_fields' ]	= $prepared;
		}

		parent::import( $field_data );
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
		if( isset( $data[ 'complex_fields' ] ) && is_array( $data[ 'complex_fields' ] ) ) {
			$fields = array();

			foreach( $data[ 'complex_fields' ] as $raw_field ) {
				$helper = Field_Helper::import_from_meta( $raw_field );
				$field  = $helper->setup_field();
				$fields[] = Field_Helper::get_field_data( $field );
			}

			$data[ 'complex_fields' ] = $fields;
		}

		return $data;
	}

	/**
	 * Prepares data for usage within the UI.
	 * Dynamically loaded containers should be available w/o AJAX.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The previously existing data.
	 * @return mixed[]
	 */
	public static function prepare_editor_data( $data ) {
		if( 'container' != $data[ 'complex_fields_source' ] ) {
			return $data;
		}

		Container_Helper::generate_preview_data( $data[ 'complex_container' ] );

		return $data;
	}
}
