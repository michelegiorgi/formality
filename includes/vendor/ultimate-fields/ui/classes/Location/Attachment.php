<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\Location as Core_Location;

/**
 * Handles containers on media items.
 *
 * @since 3.0
 */
class Attachment extends Location {
	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'attachment';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Attachment', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$types = array_values( wp_get_mime_types() );
		$types = array_combine( $types, $types );

		$fields = array(
			Field::create( 'select', 'types', __( 'Types', 'ultimate-fields' ) )
				->add_options(array(
					'all'      => __( 'Show on all files', 'ultimate-fields' ),
					'selected' => __( 'Show on selected MIME types', 'ultimate-fields' )
				))
				->set_input_type( 'radio' ) ,
			Field::create( 'multiselect', 'mime_types', __( 'MIME types', 'ultimate-fields' ) )
				->add_options( $types )
				->add_dependency( 'types', 'selected' )
		);

		return $fields;
	}

	/**
	 * Exports the location as a real, core location.
	 *
	 * @since 3.0
	 *
	 * @return Core_Location
	 */
	public static function export( $data, $helper ) {
		$location = Core_Location::create( 'attachment' );

		if( isset( $data[ 'mime_types' ] ) && ! empty( $data[ 'mime_types' ] ) ) {
			$location->file_types = $data[ 'mime_types' ];
		}

		return $location;
	}

	/**
	 * Returns the data of a core location if it can work with it or false if not.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location  $location  The location to export data from.
	 * @param Ultimate_Fields\Container $container The container the location belogns to.
	 * @return mixed
	 */
	public static function get_settings_for_import( $location, $container ) {
		if( ! is_a( $location, Core_Location::class ) ) {
			return false;
		}

		$data = array(
			'__type'     => self::get_type(),
			'mime_types' => $location->file_types
		);

		$data[ 'types' ] = empty( $data[ 'mime_types' ] ) ? 'all' : 'selected';

		return $data;
	}
}
