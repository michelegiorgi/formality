<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\Location as Core_Location;

/**
 * Handles containers as shortcodes.
 *
 * @since 3.0
 */
class Shortcode extends Location {
	/**
	 * Indicate that the location can only be used once within a container.
	 *
	 * @since 3.0
	 * @var bool
	 */
	const LIMIT = true;

	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'shortcode';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Shortcode', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$fields = array(
			Field::create( 'text', 'tag', __( 'Tag/Slug', 'ultimate-fields' ) )
				->required()
				->set_description( __( 'This is the tag which you should use within add_shortcode()', 'ultimate-fields' ) ),
			Field::create( 'select', 'preview', __( 'Preview', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options(array(
					'default' => __( 'Use the default preview (name of the shortcode)', 'ultimate-fields' ),
					'image' => __( 'Display an image', 'ultimate-fields' ),
					'template' => __( 'Use a backbone.js template', 'ultimate-fields' )
				))
				->set_description( __( 'Select what should be seen by the user within the editor when the shortcode is being used.', 'ultimate-fields' ) ),
			Field::create( 'image', 'preview_image', __( 'Preview image', 'ultimate-fields' ) )
				->add_dependency( 'preview', 'image' ),
			Field::create( 'textarea', 'template', __( 'Preview Template', 'ultimate-fields' ) )
				->add_dependency( 'preview', 'template' )
				->set_description( __( 'Enter a backbone.js template here, use field names as variables.', 'ultimate-fields' ) )
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
		$location = Core_Location::create( 'shortcode' );
		$location->set_tag( $data[ 'tag' ] );

		if( isset( $data[ 'preview' ] ) && $data[ 'preview' ] ) {
			if( 'template' == $data[ 'preview' ] ) {
				$location->set_template( $data[ 'template' ] );
			} elseif( 'image' == $data[ 'preview' ] && $data[ 'preview_image' ] ) {
				$location->use_image_as_preview( $data[ 'preview_image' ] );
			}
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
			'tag'    => $location->get_tag()
		);

		if( $location->get_preview_image() ) {
			$data[ 'preview' ]       = 'image';
			$data[ 'preview_image' ] = $location->get_preview_image();
		} elseif( $location->get_raw_template() ) {
			$data[ 'preview' ]  = 'template';
			$data[ 'template' ] = $location->get_raw_template();
		}

		return $data;
	}
}
