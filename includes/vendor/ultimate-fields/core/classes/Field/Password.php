<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;

/**
 * Handles the input for the password field.
 *
 * @since 3.0
 */
class Password extends Text {
	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-password' );
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
		/** @wp_timezone_override_offset(): Overview this class */
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

		return $settings;
	}
}
