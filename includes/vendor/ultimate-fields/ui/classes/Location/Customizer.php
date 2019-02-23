<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\Location as Core_Location;

/**
 * Handles containers in the customizer.
 *
 * @since 3.0
 */
class Customizer extends Location {
	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'customizer';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Customizer', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$fields = array(
			Field::create( 'number', 'priority', __( 'Priority', 'ultimate-fields' ) )
				->set_default_value( 150 )
				->enable_slider( 1, 200 )
				->set_description( __( 'This controls where the container will be shown in the main customizer menu.', 'ultimate-fields' ) ),
			Field::create( 'fields_selector', 'customizer_fields', __( 'postMessage fields', 'ultimate-fields' ) )
				->set_description( __( 'The selected fields will be sent to the page without refreshing.' ) )
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
		$location = Core_Location::create( 'customizer' );

		if( isset( $data[ 'priority' ] ) && $data[ 'priority' ] ) {
			$location->set_customizer_priority( $data[ 'priority' ] );
		}

		if( ! empty( $data[ 'customizer_fields' ] ) ) {
			$location->set_dynamic_fields( $data[ 'customizer_fields' ] );
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
			'__type' => self::get_type(),
			'priority' => $location->get_customizer_priority()
		);

		$data[ 'customizer_fields' ] = $location->get_dynamic_fields();

		return $data;
	}
}
