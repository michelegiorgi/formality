<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Adds the neccessary UI functionality for the file field.
 *
 * @since 3.0
 */
class File extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'File', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$general_fields = array(
			Field::create( 'select', 'allowed_filetype', __( 'Allowed file types', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options(array(
					'all'    => __( 'Allow selecting any file', 'ultimate-fields' ),
					'image'  => __( 'Images only', 'ultimate-fields' ),
					'video'  => __( 'Video only', 'ultimate-fields' ),
					'audio'  => __( 'Audio only', 'ultimate-fields' ),
					'custom' => __( 'Enter file formats manually', 'ultimate-fields' ),
				)),
			Field::create( 'text', 'custom_filetypes', __( 'Allowed MIME types', 'ultimate-fields' ) )
				->set_description( __( 'Enter file extensions, separated by commas. Leave empty to allow all files.<br /><strong>Important:</strong> List file types as MIME types: "image/jpeg", "document/pdf" and etc.', 'ultimate-fields' ) )
				->add_dependency( 'allowed_filetype', 'custom' ),
			Field::create( 'image', 'default_value_file', __( 'Default value', 'ultimate-fields' ) ),
		);

		$output_fields = array(
			Field::create( 'select', 'file_output_type', __( 'Output Type', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options( array(
					'link' => __( 'A link to the file', 'ultimate-fields' ),
					'url'  => __( 'The URL of the file', 'ultimate-fields' ),
					'id'   => __( 'The ID of the file', 'ultimate-fields' ),
				))
				->add_dependency( 'type', 'File' )
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
	public function import( $meta ) {
		$allowed_types = null;

		if( isset( $meta[ 'allowed_filetype' ] ) && $meta[ 'allowed_filetype' ] ) {
			$allowed_types = $meta[ 'allowed_filetype' ];

			if( 'custom' == $allowed_types ) {
				$allowed_types = $meta[ 'custom_filetypes' ];
			}
		}

		$meta[ 'allowed_filetype' ] = $allowed_types;

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
		if( isset( $data[ 'allowed_filetype' ] ) ) {
			$predefined = array( 'all', 'image', 'video', 'audio' );

			if( ! in_array( $data[ 'allowed_filetype' ], $predefined ) ) {
				$data[ 'custom_filetypes' ] = $data[ 'allowed_filetype' ];
				$data[ 'allowed_filetype' ] = 'custom';
			}
		}

		return $data;
	}
}
