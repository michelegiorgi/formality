<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Displays a timepicker.
 *
 * @since 3.0
 */
class Datetime extends Date {
	/**
	 * Adds the neccessary JS and templates for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();

		wp_enqueue_script( 'uf-field-datetime' );
	}

	/**
	 * Returns the additional data needed for the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$settings[ 'time_format' ] = $this->get_time_format();

		return $settings;
	}

	/**
	 * Returns the format of the date.
	 *
	 * @since 3.0
	 *
	 * @return string.
	 */
	public function get_time_format() {
		$format = get_option( 'time_format' );
		foreach( $this->get_php_to_javascript_map() as $find => $replace ) {
			$format = str_replace( $find, $replace, $format );
		}
		return $format;
	}



	/**
	 * Saves the value of the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The source to retrieve the value from.
	 */
	public function save( $source ) {
		if( ! isset( $source[ $this->name ] ) ) {
			return;
		}

		$value = $source[ $this->name ];
		if( $value ) {
			$value = date_i18n( 'Y-m-d H:i', strtotime( $value ) );
		}

		$this->datastore->set( $this->name, $value );
	}

	/**
	 * Exports the value of the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$value = $this->datastore->get( $this->name );
		if( ! $value && $this->default_value ) {
			$value = $this->default_value;
		}

		if( is_string( $value ) && preg_match( '~^\d\d\d\d-\d\d-\d\d \d\d:\d\d$~', $value ) ) {
			$value = strtotime( $value );

			# Format the value as needed
			$value   = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $value );
		} else {
			$value = '';
		}

		return array(
			$this->name => $value
		);
	}

	/**
	 * Handles a value by processing it to a timestamp.
	 *
	 * @since 3.0
	 *
	 * @param mixed       $value  The value to handle.
	 * @param Data_Source $source The source that the value is associate dwith.
	 * @return int|bool
	 */
	public function handle( $value, $source = null ) {
		if( ! $value || ! is_string( $value ) ) {
			return $value;
		}

		if( preg_match( '~^\d\d\d\d-\d\d-\d\d\s\d\d:\d\d$~', $value ) ) {
			return strtotime( $value );
		} else {
			return false;
		}
	}

	/**
	 * Processes a value to a normal date for get_value().
	 *
	 * @since 3.0
	 *
	 * @param mixed   $value The value to process.
	 * @return string $date  An actual date.
	 */
	public function process( $value ) {
		return is_int( $value ) && $value
			? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $value )
			: false;
	}
}
