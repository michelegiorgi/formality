<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the image select field in the UI.
 *
 * @since 3.0
 */
class Image_Select extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Picture Select', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$general_fields = array(
			Field::create( 'repeater', 'image_select_options', __( 'Options', 'ultimate-fields' ) )
				->set_layout( 'table' )
				->set_add_text( __( 'Add option', 'ultimate-fields' ) )
				->add_group( 'image_option', array(
					'title'  => 'Option',
					'fields' => array(
						Field::create( 'text', 'key', __( 'Key', 'ultimate-fields' ) )
							->set_width( 20 ),
						Field::create( 'text', 'label', __( 'Label', 'ultimate-fields' ) )
							->set_width( 20 ),
						Field::create( 'image', 'image', __( 'Image', 'ultimate-fields' ) )
							->set_width( 60 )
							->set_description( __( 'Please upload an image with the correct size, as it will not be cropped.', 'ultimate-fields' ) )
					)
				))
		);

		$output_fields = array(
			Field::create( 'select', 'image_select_output_format', __( 'Output Format', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_default_value( 'id' )
				->add_options(array(
					'value' => __( 'Output the key of the selected option', 'ultimate-fields' ),
					'text'  => __( 'Output the label of the selected option', 'ultimate-fields' ),
					'url'   => __( 'Output the URL of the image for the current option', 'ultimate-fields' ),
					'image' => __( 'Output a full image tag', 'ultimate-fields' ),
				))
		);

		return array(
			'general' => $general_fields,
			'output'  => $output_fields
		);
	}

	/**
	 * Enqueues the scripts for the UI.
	 *
	 * @since 3.0
	 */
	public static function enqueue() {
		Template::add( 'image-select', 'field/image-select' );
		wp_enqueue_script( 'uf-field-image-select' );
	}

	/**
	 * Imports some meta into the class.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data The settings of the field.
	 */
	public function import( $field_data ) {
		$options = array();

		if( isset( $field_data[ 'image_select_options' ] ) && $field_data[ 'image_select_options' ] ) {
			foreach( $field_data[ 'image_select_options' ] as $row ) {
				$src = '';

				if( $image = wp_get_attachment_image_src( $row[ 'image' ], 'full' ) ) {
					$src = $image[ 0 ];
				}

				$options[ $row[ 'key' ] ] = array(
					'image' => $src,
					'label' => $row[ 'label' ]
				);
			}
		}

		$field_data[ 'image_select_options' ] = $options;

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
		global $wpdb;

		$options = array();
		$sql = "SELECT ID FROM $wpdb->posts WHERE guid=%s AND post_type='attachment'";

		if( isset( $data[ 'image_select_options' ] ) && is_array( $data[ 'image_select_options' ] ) ) {
			foreach( $data[ 'image_select_options' ] as $key => $row ) {
				if( ! isset( $row[ 'image' ] ) || ! $row[ 'image' ] ) {
					continue;
				}

				# Try to locate the image
				$id = $wpdb->get_var( $wpdb->prepare( $sql, $row[ 'image' ] ) );

				if( ! $id ) {
					continue;
				}

				$options[ $key ] = array(
					'__type' => 'image_option',
					'key'    => $key,
					'image'  => intval( $id ),
					'label'  => $row[ 'label' ]
				);
			}
		}

		$data[ 'image_select_options' ] = $options;

		return $data;
	}
}
