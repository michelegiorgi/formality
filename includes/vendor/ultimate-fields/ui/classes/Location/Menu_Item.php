<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\Location as Core_Location;

/**
 * Handles containers within menu items.
 *
 * @since 3.0
 */
class Menu_Item extends Location {
	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'menu_item';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Menu item', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$fields = array();

		if( $locations = get_registered_nav_menus() ) {
			$fields[] = Field::create( 'multiselect', 'menu_locations', __( 'Theme Locations', 'ultimate-fields' ) )
				->set_description( __( 'If you want the container to only appear on menus, associated with specific locations, select them.', 'ultimate-fields' ) )
				->set_input_type( 'checkbox' )
				->add_options( $locations );
		}

		# Add specific levels
		$fields[] = Field::create( 'complex' , 'levels', __( 'Levels', 'ultimate-fields' ) )
			->set_description( __( 'Enter as numbers, separated by commas.', 'ultimate-fields' ) )
			->add_fields(array(
				Field::create( 'text', 'visible', 'Show on' )
					->set_default_value( '0' )
					->set_width( 50 ),
				Field::create( 'text', 'hidden', 'Hide on' )
					->set_default_value( '0' )
					->set_width( 50 ),
			));

		# Add a field for a popup
		$fields[] = Field::create( 'checkbox', 'popup_mode', __( 'Popup mode', 'ultimate-fields' ) )
			->set_text( __( 'Show fields in a popup', 'ultimate-fields' ) )
			->fancy()
			->set_description( __( 'If there are too many fields in the container or some of them do not fit, this will show them in a popup.', 'ultimate-fields' ) );

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
		$location = Core_Location::create( 'menu_item' );

		if( isset( $data[ 'menu_locations' ] ) && $data[ 'menu_locations' ] )
			$location->theme_locations = $data[ 'menu_locations' ];

		if( isset( $data[ 'levels' ] ) && $data[ 'levels' ] )
			$location->levels = $data[ 'levels' ];

		if( isset( $data[ 'popup_mode' ] ) && $data[ 'popup_mode' ] )
			$location->show_in_popup();

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
			'__type' => self::get_type()
		);

		if( $menu_locations = $location->theme_locations ) {
			$data[ 'menu_locations' ] = $menu_locations[ 'visible' ];
		}
		$data[ 'levels' ]         = $location->levels;
		$data[ 'popup_mode' ]     = $location->is_shown_in_popup();

		return $data;
	}
}
