<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the link-field related functionality in the UI.
 *
 * @since 3.0
 */
class Link extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Link', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields( $existing ) {
		$existing[ 'wp_object_types' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Link' );
		$existing[ 'wp_object_text' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Link' );
		$existing[ 'wp_object_post_types' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Link' )
			->add_dependency( 'wp_object_types', 'posts', 'CONTAINS' );
		$existing[ 'wp_object_taxonomies' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Link' )
			->add_dependency( 'wp_object_types', 'terms', 'CONTAINS' );
		$existing[ 'hide_filters' ]
			->add_dependency_group()
			->add_dependency( 'type', 'Link' );

		$fields = array(
			Field::create( 'link', 'default_value_link', __( 'Default value', 'ultimate-fields' ) ),
			Field::create( 'checkbox', 'link_target_control', __( 'Target Control', 'ultimate-fields' ) )
				->fancy()
				->set_default_value( true )
				->set_text( __( 'Enable the "New Tab" checkbox', 'ultimate-fields' ) )
		);

		$output = array(

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


	}
}
