<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the message-related functionality in the UI.
 *
 * @since 3.0
 */
class Message extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Message', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields() {
		$fields = array();

 		return array(
 			'general' => $fields
 		);
 	}

	/**
	 * Enqueues the field manually, as it's not being used separately.
	 *
	 * @since 3.0
	 */
	public static function enqueue() {
		wp_enqueue_script( 'uf-field-message' );
	}

	/**
	 * Imports data about the field from post meta.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data the data about the field.
	 */
	public function import_meta( $meta ) {
		parent::import_meta( $meta );
	}
}
