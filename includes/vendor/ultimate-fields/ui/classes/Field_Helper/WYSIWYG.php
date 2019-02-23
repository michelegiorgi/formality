<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the WYSIWYG field in the UI.
 *
 * @since 3.0
 */
class WYSIWYG extends Textarea {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'WYSIWYG Editor', 'ultimate-fields' );
	}

	public static function get_fields( $existing = null ) {
		$general_fields = array(
			Field::create( 'wysiwyg', 'default_value_wysiwyg', __( 'Default value', 'ultimate-fields' ) )
		);

		$existing[ 'apply_the_content' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WYSIWYG' );

		$existing[ 'apply_shortcodes' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WYSIWYG' )
			->add_dependency( 'apply_the_content', false );

		$existing[ 'apply_wpautop' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WYSIWYG' )
			->add_dependency( 'apply_the_content', false );

		return array(
			'general' => $general_fields
		);
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

	/**
	 * Sets the field up.
	 *
	 * @since 3.0
	 *
	 * @return Field
	 */
	public function setup_field() {
		$field = parent::setup_field();

		return $field;
	}
}
