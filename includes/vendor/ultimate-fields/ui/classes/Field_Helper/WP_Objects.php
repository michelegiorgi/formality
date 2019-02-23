<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the objects-field related functionality in the UI.
 *
 * @since 3.0
 */
class WP_Objects extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'WP Objects', 'ultimate-fields' );
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
			->add_dependency( 'type', 'WP_Objects' );
		$existing[ 'wp_object_text' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WP_Objects' );
		$existing[ 'wp_object_post_types' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WP_Objects' )
			->add_dependency( 'wp_object_types', 'posts', 'CONTAINS' );
		$existing[ 'wp_object_taxonomies' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WP_Objects' )
			->add_dependency( 'wp_object_types', 'terms', 'CONTAINS' );
		$existing[ 'wp_object_link_text' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WP_Objects' )
			->add_dependency( 'wp_object_output_type', 'link' );
		$existing[ 'wp_object_output_type' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WP_Objects' );
		$existing[ 'hide_filters' ]
			->add_dependency_group()
			->add_dependency( 'type', 'WP_Objects' );

 		$fields = array(
			Field::create( 'wp_objects', 'default_value_wp_objects', __( 'Default value', 'ultimate-fields' ) ),
			Field::create( 'number', 'wp_maximum_objects', __( 'Maximum amount of items', 'ultimate-fields' ) )
				->set_description( __( 'Controls the maximum amount of objects that can be added to the field.', 'ultimate-fields' ) )
 		);

		$output = array(
			Field::create( 'select', 'wp_objects_output_format', __( 'Output Format', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_default_value( 'comma' )
				->add_options(array(
					'comma'      => __( 'Separate the values with commas', 'ultimate-fields' ),
					'ordered'    => __( 'Ordered list', 'ultimate-fields' ),
					'unordered'  => __( 'Unordered list', 'ultimate-fields' ),
					'paragraphs' => __( 'Paragraphs', 'ultimate-fields' ),
				))
		);

 		return array(
 			'general' => $fields,
 			'output'  => $output
 		);
 	}
}
