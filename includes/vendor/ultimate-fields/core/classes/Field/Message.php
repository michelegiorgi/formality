<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;

/**
 * Displays a message without no input.
 *
 * @since 3.0
 */
class Message extends Field {
	/**
	 * Enqueues the script for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-message' );
	}

	/**
	 * Ensures that unlike normal fields, no values are saved for messages.
	 *
	 * @since 3.0.2
	 *
	 * @param mixed[] $source The source which the value of the field would be available in.
	 */
	public function save( $source ) {
		// Nothing to do here really...
	}
}
