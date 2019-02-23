<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the textarea-field related functionality in the UI.
 *
 * @since 3.0
 */
class Textarea extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Textarea', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	 public static function get_fields() {
		$link = '<a href="https://codex.wordpress.org/Shortcode_API" target="_blank">WordPress Shorcode API</a>';

 		$fields = array(
			Field::create( 'textarea', 'default_value_textarea', __( 'Default value', 'ultimate-fields' ) ),
			Field::create( 'number', 'rows', __( 'Rows', 'ultimate-fields' ) )
				->set_default_value( 10 )
				->set_minimum( 1 )
				->set_maximum( 100 )
 		);

		$output = array(
			Field::create( 'checkbox', 'apply_the_content', __( 'Apply the "the_content" filter', 'ultimate-fields' ) )
				->fancy()
				->set_description( __( '<strong>Warning!</strong> This filter could have many hooks attached to itself and could lead to slow performance. Only apply this if really needed.', 'ultimate-fields' ) )
				->set_text( __( 'Apply', 'ultimate-fields' ) ),
			Field::create( 'checkbox', 'apply_shortcodes', __ ( 'Apply Shortcodes', 'ultimate-fields' ) )
				->fancy()
				->set_text( __( 'Apply', 'ultimate-fields' ) )
				->add_dependency( 'apply_the_content', true, '!=' )
				->set_description( sprintf( __( 'If checked, the shortcodes within the content will be parsed. For more information, please see the %s.', 'ultimate-fields' ), $link ) ),
			Field::create( 'checkbox', 'apply_wpautop', __( 'Automatically add paragraphs', 'ultimate-fields' ) )
				->fancy()
				->set_text( __( 'Add', 'ultimate-fields' ) )
				->add_dependency( 'apply_the_content', true, '!=' )
				->set_description( __( 'Automatically adds paragraphs and line breaks to the text.', 'ultimate-fields' ) )
				->set_default_value( true ),
		);

 		return array(
 			'general' => $fields,
 			'output'  => $output
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

		if( isset( $meta[ 'suggestions' ] ) ) {
			print_r( $meta[ 'suggestions' ] );
		}
	}
}