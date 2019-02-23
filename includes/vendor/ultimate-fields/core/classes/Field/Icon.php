<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the inputs for icons.
 *
 * @since 3.0
 */
class Icon extends Field {
	/**
	 * Holds the sets of icons, which are to be used.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $icon_sets = array();

	/**
	 * Holds the default sets of icons, which can be used if there is nothing set.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $default_sets = array( 'font-awesome', 'dashicons' );

	/**
	 * Holds the output format of the field.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_format = 'class';

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-icon' );

		# Add some templates
		Template::add( 'overlay-wrapper', 'overlay-wrapper' );
		Template::add( 'icon-popup', 'field/icon-popup' );
		Template::add( 'icon-preview', 'field/icon-preview' );

		# Localize
		ultimate_fields()
			->localize( 'change-icon', __( 'Change', 'ultimate-fields' ) )
			->localize( 'select-icon', __( 'Select icon', 'ultimate-fields' ) )
			->localize( 'remove-icon', __( 'Remove icon', 'ultimate-fields' ) )
			->localize( 'cancel',      __( 'Cancel', 'ultimate-fields' ) );

		# Add sets
		foreach( $this->get_sets() as $set )
			$this->localize_set( $set );
	}

	/**
	 * Adds the possible icons to the fields' settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		# Load icons
		$sets = empty( $this->icon_sets )
			? $this->default_sets
			: $this->icon_sets;

		$settings[ 'icon_sets' ] = $this->get_sets();

		return $settings;
	}

	/**
	 * Adds a set of icon as a localizable string in order to avoid loading the same thing twice.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the set.
	 */
	protected function localize_set( $name ) {
		static $localized;

		if( is_null( $localized ) ) {
			$localized = array();
		}

		if( isset( $localized[ $name ] ) ) {
			return;
		}

		$set = $this->load_set( $name );
		$localized[ $name ] = $set;

		ultimate_fields()->localize( 'icon_sets', $localized );
	}

	/**
	 * Loads a set of icon.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the set.
	 * @return mixed[] Parsed information about the set, ready for JS.
	 */
	protected function load_set( $name ) {
		static $loaded;

		if( is_null( $loaded ) ) {
			$loaded = array();
		}

		if( isset( $loaded[ $name ] ) ) {
			return $loaded[ $name ];
		}

		/**
		 * Allows the data of an icon set to be loaded.
		 *
		 * If there is any return of this filter, the field will not look for a JSON file.
		 *
		 * @return mixed
		 */
		$data = apply_filters( "uf.icon.$name", null );

		if( ! is_null( $data ) ) {
			return $data;
		}

		// Continue looking for a file
		$path = ULTIMATE_FIELDS_DIR . 'assets/json/' . $name . '.json';

		/**
		 * Allows the path for the JSON of an icon set to be overloaded.
		 *
		 * @since  3.0
		 *
		 * @param string $path The path to the JSON file, which contains icon data.
		 * @return string
		 */
		$path = apply_filters( "uf.icon.$name.path", $path );

		if( ! file_exists( $path ) ) {
			return false;
		}

		$json = file_get_contents( $path );

		if( ! $json ) {
			return false;
		}

		$data = json_decode( $json, true );
		$loaded[ $name ] = $data;

		return $data;
	}

	/**
	 * Adds a specific set of icons to the field.
	 *
	 * @since 3.0
	 *
	 * @param string $set The name of the set (file).
	 * @return Ultimate_Fields\Field\Icon
	 */
	public function add_set( $set ) {
		$this->icon_sets[] = $set;

		return $this;
	}

	/**
	 * Returns the available sets.
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public function get_sets() {
		return empty( $this->icon_sets )
			? $this->default_sets
			: $this->icon_sets;
	}

	/**
	 * CHanges the format for using the_value().
	 *
	 * @since 3.0
	 *
	 * @param string $type The output type of the field ('class' or 'icon').
	 * @return Ultimate_Fields\Field\Icon
	 */
	public function set_output_format( $format ) {
		$this->output_format = $format;

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

		if( isset( $data[ 'icon_sets' ] ) && is_array( $data[ 'icon_sets' ] ) ) {
			foreach( $data[ 'icon_sets' ] as $set ) {
				$this->add_set( $set );
			}
		}

		$this->proxy_data_to_setters( $data, array(
			'icon_output_format' => 'set_output_format'
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
			'icon_sets'     => array( 'icon_sets', array() ),
			'output_format' => array( 'icon_output_format', 'class' )
		));

		return $settings;
	}

	/**
	 * Handles the value by converting it to a proper icon class.
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value to handle.
	 * @param Ultimate_Fields\Helper\Data_Source $source The source the value is coming from.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		if( ! is_string( $value ) || empty( $value ) ) {
			return false;
		}

		$sets     = array_map( array( $this, 'load_set' ), $this->get_sets() );
		$sets     = array_combine( wp_list_pluck( $sets, 'prefix' ), $sets );
		$prefixes = implode( '|', array_map( 'preg_quote', array_keys( $sets ) ) );

		$set = preg_replace( "~^($prefixes)-.+$~", '$1', $value );

		if( ! isset( $sets[ $set ] ) ) {
			return false;
		}

		foreach( $sets[ $set ]['groups'] as $group ) {
			foreach( $group['icons'] as $icon ) {
				if( $icon != $value ) {
					continue;
				}

				return $sets[ $set ]['prefix'] . ' ' . $value;
			}
		}

		return false;
	}

	/**
	 * Processes a value for display.
	 *
	 * @since 3.0
	 *
	 * @param string $valut The value that will be processed.
	 * @return string
	 */
	public function process( $value ) {
		if( 'icon' == $this->output_format ) {
			return $value
				? '<span class="' . $value . '"></span>'
				: '';
		} else {
			return $value;
		}
	}
}
