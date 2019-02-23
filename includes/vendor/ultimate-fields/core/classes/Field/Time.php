<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Displays a timepicker.
 *
 * @since 3.0
 */
class Time extends Date {
	/**
	 * Adds the neccessary JS and templates for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-time' );

		Template::add( 'time', 'field/time' );
	}

	/**
	 * Returns the format of the date.
	 *
	 * @since 3.0
	 *
	 * @return string.
	 */
	public function get_format() {
		$format = get_option( 'time_format' );
		$map = $this->get_php_to_javascript_map();
		$format = str_replace( array_keys( $map ), array_values( $map ), $format );

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
			$value = date_i18n( 'H:i', strtotime( $value ) );
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

		if( is_string( $value ) && preg_match( '~^\d\d:\d\d$~', $value ) ) {
			$value = strtotime( $value );

			# Format the value as needed
			$value   = date_i18n( get_option( 'time_format' ), $value );
		} else {
			$value = '';
		}

		return array(
			$this->name => $value
		);
	}

	/**
	 * Handles a value by simply using it.
	 *
	 * @since 3.0
	 *
	 * @param mixed       $value  The value to handle.
	 * @param Data_Source $source The source that the value is associate dwith.
	 * @return int|bool
	 */
	public function handle( $value, $source = null ) {
		return $value;
	}

	/**
	 * Overwrites the date processing to avoid it and just return the time.
	 *
	 * @since 3.0
	 *
	 * @param mixed   $value The value to process.
	 * @return string
	 */
	public function process( $value ) {
		if( $value ) {
			$full_date = '2020-01-01 ' . $value;
			$timestamp = strtotime( $full_date );
			$value = date_i18n( get_option( 'time_format' ), $timestamp );
		}

		return $value;
	}
}
