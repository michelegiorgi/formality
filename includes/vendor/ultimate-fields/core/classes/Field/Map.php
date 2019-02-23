<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Works as an input for choosing a location on a map.
 *
 * @since 3.0
 */
class Map extends Field {
	/**
	 * Required: An API key for the JS API.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $api_key;

	/**
	 * Holds the maximum size of the map that will be displayed.
	 *
	 * Unlike the height, the width can be either in pixels or in percent.
	 *
	 * @since 3.0
	 * @var string|int.
	 */
	protected $width  = '100%';

	/**
	 * Holds the height of the displayed map.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $height = 400;

	/**
	 * Holds the output width for when the_value() is used.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $output_width = 800;

	/**
	 * Holds the output height for when the_value() is used.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $output_height = 300;

	/**
	 * Exports the settings the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$settings[ 'height' ]  = $this->get_height();
		$settings[ 'api_key' ] = $this->get_api_key();

		return $settings;
	}

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-map' );

		# Add templates
		Template::add( 'map-base', 'field/map-base' );
		Template::add( 'map-error', 'field/map-error' );
	}

	/**
	 * Parses a number that should either be in pixels on percents.
	 *
	 * @since 3.0
	 *
	 * @param mixed $number.
	 * @return mixed Either number, float, string or false.
	 */
	public function parse_number( $number ) {
		if( is_int( $number ) || is_float( $number ) ) {
			return $number;
		} elseif( is_string( $number ) && preg_match( '~^\d+([.,]\d+)(px|\%)?$~i', $number ) ) {
			$unit   = strpos( $number, '%' ) === false ? '' : '$'; // Retrieve unit
			$number = str_replace( array( '%', 'px' ), '', $number ); // Remove unit
			$number = str_replace( ',', '.', $number ); // Remove commas
			$number = strpos( $number, '.' ) === false ? intval( $number ) : floatval( $number );

			return $number . $unit;
		} elseif( is_string( $number ) && preg_match( '~^\d+$~', $number ) ) {
			return intval( $number );
		} else {
			return false;
		}
	}

	/**
	 * Sets the height of the map.
	 *
	 * @since 3.0
	 *
	 * @param string|int $height The height that should be used for the map.
	 * @return Ultimate_Fields\Field\Map The instance of the field, useful for chaining.
	 */
	public function set_height( $height ) {
		# Convert to the appropriate format
		$height = $this->parse_number( $height );

		if( false !== $height ) {
			$this->height = $height;
		}

		return $this;
	}

	/**
	 * Retrieves the height of the map.
	 *
	 * @since 3.0
	 *
	 * @return mixed.
	 */
	public function get_height() {
		return $this->height;
	}

	/**
	 * Changes the output width for when the_value() is used.
	 *
	 * @since 3.0
	 *
	 * @param int $width The width to use.
	 * @return Ultimate_Fields\Field\Map
	 */
	public function set_output_width( $width ) {
		$this->output_width = $width;
		return $this;
	}

	/**
	 * Changes the output height for when the_value() is used.
	 *
	 * @since 3.0
	 *
	 * @param int $height The height to use.
	 * @return Ultimate_Fields\Field\Map
	 */
	public function set_output_height( $height ) {
		$this->output_height = $height;
		return $this;
	}

	/**
	 * Imports the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data for the field.
	 */
	public function import( $data ) {
		parent::import( $data );

		$this->proxy_data_to_setters( $data, array(
			'map_height'        => 'set_height',
			'map_output_width'  => 'set_output_width',
			'map_output_height' => 'set_output_height'
		));
	}

	/**
	 * Generates the data for file exports.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		$this->export_properties( $settings, array(
			'height'        => array( 'map_height', 400 ),
			'output_width'  => array( 'map_output_width', 800 ),
			'output_height' => array( 'map_output_height', 300 )
		));

		return $settings;
	}

	/**
	 * Handles the value by converting it to a proper format.
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value to handle.
	 * @param Ultimate_Fields\Helper\Data_Source $source The source the value is coming from.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		$value = parent::handle( $value, $source );

		if( ! is_array( $value ) || ! isset( $value['latLng'] ) ) {
			return false;
		}

		$prepared = array(
			'latLng'  => array_map( 'floatval', $value['latLng' ] ),
			'address' => isset( $value['address'] ) ? trim( $value['address'] ) : false,
			'zoom'    => isset( $value['zoom'] ) ? intval( $value['zoom'] ) : 8
		);

		return $prepared;
	}

	/**
	 * Processes the value of the map for display.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $value The current value.
	 * @return string HTML code
	 */
	public function process( $value ) {
		if( ! $value || ! is_array( $value ) ) {
			return '';
		}

		$width  = $this->output_width  ? $this->output_width  : 800;
		$height = $this->output_height ? $this->output_height : 300;

		$value = sprintf(
			'<div class="uf-map" data-lat="%s" data-lng="%s" data-pin-title="%s" data-zoom="%d" style="width:%dpx; max-width: 100%%; height: %dpx"></div>',
			$value[ 'latLng' ][ 'lat' ],
			$value[ 'latLng' ][ 'lng' ],
			$value[ 'address' ],
			$value['zoom'],
			$width,
			$height
		);

		wp_enqueue_script( 'uf-map-start' );

		return $value;
	}

	/**
	 * Changes the API key that will be used for displaying the map.
	 *
	 * @since 3.0
	 * @link https://console.developers.google.com/
	 *
	 * @param string $api_key The API key as retrieved from the Google APIs console.
	 * @return Ultimate_Fields\Field\Map The instance of the field, useful for chaining.
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;

		return $this;
	}

	/**
	 * Retrieves the API key that is being used for connection with Google's API.
	 *
	 * @since 3.0
	 *
	 * @return string.
	 */
	public function get_api_key() {
		/**
		 * Allows the API key that is being used for a Map field to be changed.
		 *
		 * @since 3.0
		 *
		 * @param string $api_key The API key for Google.
		 * @param Ultimate_Fields\Field\Map $field The field whose key is being modified.
		 */
		$filtered = apply_filters( 'uf.field.map.api_key', null, $this );

		if( ! is_null( $filtered ) ) {
			return $filtered;
		}

		return $this->api_key
			? $this->api_key
			: get_option( 'uf_google_map_api_key' );
	}
}
