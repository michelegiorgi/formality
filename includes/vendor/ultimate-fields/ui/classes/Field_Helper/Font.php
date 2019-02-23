<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the font-field related functionality in the UI.
 *
 * @since 3.0
 */
class Font extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Font', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields( $existing ) {
		$api_key = get_option( 'uf_google_fonts_api_key' );
		$fields  = array();

		if( $api_key ) {
			$fields[] = Field::create( 'font', 'default_value_font', __( 'Default value', 'ultimate-fields' ) )
				->set_api_key( $api_key );
		} else {
			$fields[] = Field::create( 'message', 'font_api_key', __( 'Google Fonts API Key', 'ultimate-fields' ) )
				->set_description( __( 'You need to generate and enter an API key in order to use the font field. Please go to the settings page of Ultimate Fields and enter the key there. If no value is entered, the Google Fonts field will be ignored. You can generate an API key at the <a href="https://console.developers.google.com/project" target="_blank">Google APIs Console</a>.', 'ultimate-fields' ) );
		}

		return array(
			'general' => $fields
		);
 	}

	/**
	 * Enqueues the font field even if the API key is not available.
	 *
	 * @since 3.0
	 */
	public static function enqueue() {
		wp_enqueue_script( 'uf-field-font' );
		Template::add( 'font', 'field/font' );
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
