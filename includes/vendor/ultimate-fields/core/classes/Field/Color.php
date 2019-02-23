<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;

/**
 * Works with the input for choosing a color.
 *
 * @since 3.0
 */
class Color extends Field {
	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'uf-field-color' );
	}

	/**
	 * When the data API is being used, this method will "handle" a value.
	 *
	 * @param  mixed       $value  The raw value.
	 * @param  Data_Source $source The data source that the value is associated with.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		$value   = parent::handle( $value, $source );

		if( ! preg_match( '~^\#[0-9a-fA-F]{6,6}$~', $value ) ) {
			$value = false;
		}

		return $value;
	}
}
