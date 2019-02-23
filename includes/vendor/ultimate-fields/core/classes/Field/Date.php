<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Generates the date input within Ultimate Fields.
 *
 * @since 3.0
 */
class Date extends Field {
	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-date' );

		# Localize
		ultimate_fields()
			->localize( 'datepicker-today', __( 'Today', 'ultimate-fields' ) )
			->localize( 'datepicker-close', __( 'Close', 'ultimate-fields' ) );

		# Add additional template(s)
		Template::add( 'date', 'field/date' );
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

		$settings[ 'format' ] = $this->get_format();

		return $settings;
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

		if( is_string( $value ) && preg_match( '~^\d\d\d\d-\d\d-\d\d$~', $value ) ) {
			$value = strtotime( $value );

			# Format the value as needed
			$value = date_i18n( get_option( 'date_format' ), $value );
		} else {
			$value = '';
		}

		return array(
			$this->name => $value
		);
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
			$value = date_i18n( 'Y-m-d', strtotime( $value ) );
		}

		$this->datastore->set( $this->name, $value );
	}

	/**
	 * Returns the format of the date.
	 *
	 * @since 3.0
	 *
	 * @return string.
	 */
	public function get_format() {
		$format = get_option( 'date_format' );
		$map = $this->get_php_to_javascript_map();
		$format = str_replace( array_keys( $map ), array_values( $map ), $format );

		return $format;
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

		if( preg_match( '~^\d\d\d\d-\d\d-\d\d$~', $value ) ) {
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
			? date_i18n( get_option( 'date_format' ), $value )
			: false;
	}

	/**
	 * Returns a map of PHP formats in JavaScript.
	 *
	 * @since 3 .0
	 * @see http://stackoverflow.com/questions/16702398/convert-a-php-date-format-to-a-jqueryui-datepicker-date-format
	 * @author Tristian Jahier
	 *
	 * @return string[]
	 */
	protected function get_php_to_javascript_map() {
		return array(
			// Day
			'd' => 'dd',
			'D' => 'D',
			'j' => 'd',
			'l' => 'DD',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			// Week
			'W' => '',
			// Month
			'F' => 'MM',
			'm' => 'mm',
			'M' => 'M',
			'n' => 'm',
			't' => '',
			// Year
			'L' => '',
			'o' => '',
			'Y' => 'yy',
			'y' => 'y',

			// Time
			'G' => 'H', // Hour with no leading 0 (24 hour)
			'H' => 'HH', // Hour with leading 0 (24 hour)
			'h' => 'hh', // Hour with leading 0 (12 hour)
			'g' => 'h', // Hour with no leading 0 (12 hour)
			'i' => 'mm', // Minute with leading 0
			's' => 'ss', // Second with leading 0
			'a' => 'tt', // am or pm for AM/PM
			'A' => 'TT', // AM or PM for AM/PM)
		);
	}
}
