<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the map-field related functionality in the UI.
 *
 * @since 3.0
 */
class Map extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Map', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields( $existing ) {
		$fields = array(
			Field::create( 'map', 'default_value_map', __( 'Default value', 'ultimate-fields' ) ),
			Field::create( 'text', 'map_height', __( 'Height', 'ultimate-fields' ) )
				->set_default_value( 400 )
				->set_suffix( 'px' )
		);

		$output = array(
			Field::create( 'number', 'map_output_width', __( 'Map Width', 'ultimate-fields' ) )
				->enable_slider( 100, 2000 )
				->set_default_value( 800 )
				->set_description( __( 'Pixels. Controls the maximum width of the map.', 'ultimate-fields' ) ),
			Field::create( 'number', 'map_output_height', __( 'Map Height', 'ultimate-fields' ) )
				->enable_slider( 100, 1500 )
				->set_default_value( 300 )
				->set_description( __( 'Pixels. Controls the height of the map.', 'ultimate-fields' ) )
		);

		return array(
			'general' => $fields,
			'output'  => $output
		);
 	}
}
