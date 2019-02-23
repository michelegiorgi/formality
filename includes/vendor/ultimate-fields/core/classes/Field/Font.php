<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Allows users to pick fonts.
 *
 * @since 3.0
 */
class Font extends Field {
	/**
	 * Holds the API key of the field.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $api_key;

	/**
	 * In case there has been a connection error, this will hold
	 * the message that will be shown to the user.
	 *
	 * @since 3.0
	 * @var string.
	 */
	protected $connection_error;

	/**
	 * Holds the index of the field.
	 * This is a runtime-generated index.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $index = 0;

	/**
	 * Holds the static index for multiple fields.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected static $last_index = 0;

	/**
	 * Add some early hooks.
	 *
	 * @since 3.0
	 */
	protected function __constructed() {
		# Generate a new index
		$this->index = ++self::$last_index;
	}

	/**
	 * Enqueues the scripts and templates for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-font' );

		# Add scripts for the filters
		wp_enqueue_script( 'uf-field-textarea' );
		wp_enqueue_script( 'uf-field-text' );
		wp_enqueue_script( 'uf-field-select' );
		wp_enqueue_script( 'uf-field-multiselect' );

		# Add templates
		Template::add( 'font', 'field/font' );
		Template::add( 'font-popup', 'field/font-popup' );
		Template::add( 'font-preview', 'field/font-preview' );
		Template::add( 'font-variants', 'field/font-variants' );
		Template::add( 'overlay-wrapper', 'overlay-wrapper' );
		Template::add( 'pagination', 'pagination' );

		# Localize
		ultimate_fields()
			->localize( 'select',            _x( 'Select', 'font', 'ultimate-fields' ) )
			->localize( 'cancel',            __( 'Cancel', 'ultimate-fields' ) )
			->localize( 'select-font',       __( 'Select font', 'ultimate-fields' ) )
			->localize( 'change-font',       __( 'Change font', 'ultimate-fields' ) )
			->localize( 'font-clear',        __( 'Clear choice', 'ultimate-fields' ) )
			->localize( 'font-preview-text', __( 'Preview Text', 'ultimate-fields' ) )
			->localize( 'font-search',       __( 'Search', 'ultimate-fields' ) )
			->localize( 'font-categories',   __( 'Categories', 'ultimate-fields' ) )
			->localize( 'font-subsets',      __( 'Subsets', 'ultimate-fields' ) )
			->localize( 'font-description',  __( 'Weight: %s, Style: %s', 'ultimate-fields' ) )
			->localize( 'font',              __( 'Font', 'ultimate-fields' ) )
			->localize( 'fonts',             __( 'Fonts', 'ultimate-fields' ) );
	}
	
	/**
	 * Changes the API key that will be used for retrieving fonts.
	 *
	 * @since 3.0
	 * @link https://console.developers.google.com/
	 *
	 * @param string $api_key The API key as retrieved from the Google APIs console.
	 * @return Ultimate_Fields\Field\Font The instance of the field, useful for chaining.
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
		 * Allows the API key that is being used for a Google Font field to be changed.
		 *
		 * @since 3.0
		 *
		 * @param string $api_key The API key for Google.
		 * @param Ultimate_Fields\Field\Font $field The field whose key is being modified.
		 */
		$filtered = apply_filters( 'uf.field.font.api_key', null, $this );

		if( ! is_null( $filtered ) ) {
			return $filtered;
		}

		return $this->api_key
			? $this->api_key
			: get_option( 'uf_google_fonts_api_key' );
	}

	/**
	 * Retrieves the fonts from Google.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	protected function get_fonts_from_google() {
		$url  = 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . urlencode( $this->get_api_key() );

		# Get the data from the internet
		$data = wp_remote_get( $url );

		if( is_a( $data, 'WP_Error' ) ) {
			$fallback = @file_get_contents( $url );
			if( $fallback ) {
				$data = array(
					'body' => $fallback
				);
			}
		}

		# Check if the data is retrieved
		if( is_a( $data, 'WP_Error' ) ) {
			# Check for a specific HTTPs error.
			foreach( $data->errors as $err ) {
				if( __( 'There are no HTTP transports available which can complete the requested request.' ) == $err[ 0 ] ) {
					$this->connection_error = __( "The list of fonts could not be loaded. Please verify that your server's PHP settings allow fetching data through the HTTPS protocol.", 'ultimate-fields' );
				}
			}

			# Set a general error message
			if( ! $this->connection_error ) {
				$this->connection_error = __( 'Unfortunately the list of Google Fonts could not be loaded. Please make sure that the server has a working internet connection. The error is: ', 'ultimate-fields' );

				foreach( $data->errors as $err ) {
					$this->connection_error .= $err[ 0 ];
					break;
				}
			}

			return false;
		}

		# Check if the data is actually there
		$items = json_decode( $data[ 'body' ] );
		if( ! $items || ! isset( $items->items ) ) {
			return false;
		}

		return $items->items;
	}

	/**
	 * Retrieves the fonts that will be available to choose from in the field.
	 *
	 * If possible, will retrieve the fonts from a transient. If not, a call to
	 * the API will be executed in order to retrieve all latest fonts.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	protected function get_fonts() {
		# If there is no API key, we're not doing anything else.
		if( ! $ak = $this->get_api_key() ) {
			return false;
		}

		# Try retrieving fonts from the transient
		$transient_key = 'uf_gfonts_' . $this->name;

		if( ! $fonts = get_transient( $transient_key ) ) {
			if( $fonts = $this->get_fonts_from_google() ) {
				set_transient( $transient_key, $fonts, 12 * HOUR_IN_SECONDS );
			}
		}

		# If there are still no fonts, there is nothing to process.
		if( ! $fonts ) {
			return false;
		}

		# Extract names/font families.
		$options = array();
		foreach( $fonts as $font ) {
			$options[ $font->family ] = $font->family;
		}

		return $options;
	}

	/**
	 * Exports the fields' settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		if( ! $this->get_api_key() ) {
			$this->connection_error = __( 'You need to set a Google API Key for this field, otherwise fonts cannot be retreived.', 'ultimate-fields' );
		}

		$settings = array_merge( $settings, array(
			'field_index'      => $this->index,
			'nonce'            => wp_create_nonce( $this->get_nonce_action() ),
			'fonts'            => $this->get_fonts(),
			'connection_error' => $this->connection_error,
			'subsets'          => array(
				'latin'        => __( 'Latin', 'ultimate-fields' ),
				'latin-ext'    => __( 'Latin Extended', 'ultimate-fields' ),
				'greek'        => __( 'Greek', 'ultimate-fields' ),
				'vietnamese'   => __( 'Vietnamese', 'ultimate-fields' ),
				'cyrillic-ext' => __( 'Cyrillic Extended', 'ultimate-fields' ),
				'cyrillic'     => __( 'Cyrillic', 'ultimate-fields' ),
				'khmer'        => __( 'Khmer', 'ultimate-fields' ),
				'greek-ext'    => __( 'Greek Extended', 'ultimate-fields' ),
				'telugu'       => __( 'Telugu', 'ultimate-fields' ),
				'devanagari'   => __( 'Devanagari', 'ultimate-fields' ),
			),
			'categories'       => array(
				'serif'       => __( 'Serif', 'ultimate-fields' ),
				'sans-serif'  => __( 'Sans Serif', 'ultimate-fields' ),
				'display'     => __( 'Display', 'ultimate-fields' ),
				'handwriting' => __( 'Handwriting', 'ultimate-fields' ),
				'monospace'   => __( 'Monospace', 'ultimate-fields' )
			)
		));

		return $settings;
	}

	/**
	 * Returns the action for a nonce field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_nonce_action() {
		return 'uf_load_fonts_' . $this->index;
	}

	 /**
	 * Processes AJAX requests for all available fonts.
 	 *
 	 * @since 3.0
 	 *
 	 * @param string $action The action that is being performed.
 	 * @param mixed  $item   The item that is being edited.
 	 */
 	public function perform_ajax( $action, $item ) {
		if( 'get_fonts_list_' . $this->name != $action ) {
			return;
		}

		if(
			! isset( $_POST[ 'nonce' ] )
		 	|| ! wp_verify_nonce( $_POST[ 'nonce' ], $this->get_nonce_action() )
		) {
			return;
		}

		$fonts = get_transient( 'uf_gfonts_' . $this->name );

		if( ! $fonts ) {
			echo '[]'; exit;
		}

		echo json_encode( $fonts );
		exit;
	}

	/**
	 * Generates the URL of the stylesheet for a font.
	 *
	 * @since 3.0
	 *
	 * @param array $font The value, returned by `get_value`.
	 * @return string
	 */
	public static function get_font_url( $font ) {
		$variants = array(
			'regular' => 400,
			'bold'    => 700,
			'italic'  => 'i'
		);

		return sprintf(
			'https://fonts.googleapis.com/css?family=%s:%s',
			str_replace( ' ', '+', $font['family'] ),
			implode( ',', str_replace( array_keys( $variants ), array_values( $variants ), $font['variants'] ) )
		);
	}
}
