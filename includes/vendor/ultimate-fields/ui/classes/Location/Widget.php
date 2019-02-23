<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\Location as Core_Location;

/**
 * Handles containers on widgets.
 *
 * @since 3.0
 */
class Widget extends Location {
	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'widget';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Widget', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$fields = array();

		$widgets = array();
		foreach( $GLOBALS[ 'wp_registered_widgets' ] as $item ) {
			$widget      = $item[ 'callback' ][ 0 ];
			$description = '';

			if( isset( $widget->widget_options[ 'description' ] ) ) {
				$description = ' / ' . $widget->widget_options[ 'description' ];
			}

			$widgets[ get_class( $widget ) ] = $widget->name . ' <em>' . $description . '</em>';
		}

		$fields[] = Field::create( 'checkbox', 'show_on_all', __( 'Show on all widgets', 'ultimate-fields' ) )
			->set_description( __( 'Universally shows the fields on all widgets.', 'ultimate-fields' ) )
			->fancy();

		$fields[] = Field::create( 'multiselect', 'widgets', __( 'Widgets', 'ultimate-fields' ) )
			->add_options( $widgets )
			->set_description( __( 'Check the widgets that you want to use the container with.', 'ultimate-fields' ) )
			->set_input_type( 'checkbox' )
			->add_dependency( 'show_on_all', '1', '!=' );

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
		if( isset( $data[ 'show_on_all' ] ) && $data[ 'show_on_all' ] ) {
			return Core_Location::create( 'widget' );
		} else {
			return Core_Location::create( 'widget', $data[ 'widgets' ] );
		}
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

		$data = array(
			'__type'     => self::get_type()
		);

		if( ! empty( $widgets = $location->get_widgets() ) ) {
			$data[ 'widgets' ] = $widgets;
		} else {
			$data[ 'show_on_all' ] = true;
		}

		return $data;
	}
}
