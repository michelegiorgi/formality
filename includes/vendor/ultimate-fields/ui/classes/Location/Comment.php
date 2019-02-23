<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\Location as Core_Location;

/**
 * Handles containers on comments.
 *
 * @since 3.0
 */
class Comment extends Location {
	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'comment';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Comment', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$fields = array(
			Field::create( 'tab', 'basic_settings', __( 'Basic Settings' ) )
				->set_icon( 'dashicons-admin-post' ),
			Field::create( 'select', 'status', __( 'Comment Status', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_default_value( 'any' )
				->add_options(array(
					'any'      => __( 'Any', 'ultimate-fields' ),
					'approved' => __( 'Approved', 'ultimate-fields' ),
					'pending'  => __( 'Pending', 'ultimate-fields' ),
					'spam'     => __( 'Spam', 'ultimate-fields' ),
				))
				->set_description( __( 'Select which comment stati the container would work with.', 'ultimate-fields' ) ),
			Field::create( 'select', 'priority', __( 'Priority', 'ultimate-fields' ) )
				->set_description( __( 'Either normal for default flow, or High to force higher position.', 'ultimate-fields' ) )
				->add_options(array(
					'default' => __( 'Default', 'ultimate-fields' ),
					'high'    => __( 'High', 'ultimate-fields' )
				))
				->set_input_type( 'radio' )
		);

		$fields = array_merge( $fields, self::get_rest_fields() );

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
		$location = Core_Location::create( 'comment' );

		$location->set_priority( $data[ 'priority' ] );
		if( 'any' != $data[ 'status' ] ) {
			$location->stati = $data[ 'status' ];
		}

		# Setup the rest API
		self::setup_location_rest( $location, $data );

		return $location;
	}

	/**
	 * Returns the data of a core location if it can work with it or false if not.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $location The location to export data from.
	 * @return mixed
	 */
	public static function get_settings_for_import( $location ) {
		if( ! is_a( $location, Core_Location::class ) ) {
			return false;
		}

		$stati = $location->stati;
		$stati = isset( $stati[ 'visible' ] ) ? $stati[ 'visible' ] : array();

		$data = array(
			'__type'   => self::get_type(),
			'priority' => $location->get_priority(),
			'status'   => empty( $stati ) ? 'any' : array_shift( $stati )
		);

		# Check for REST data
		self::import_rest( $location, $data );

		return $data;
	}
}
