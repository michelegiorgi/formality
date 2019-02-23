<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;
use Ultimate_Fields\Helper\Object\Type;

/**
 * Handles the object-field related functionality in the UI.
 *
 * @since 3.0
 */
class WP_Object extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'WP Object', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	 public static function get_fields() {
		$helper_classes = array(
			'posts' => __( 'Post Types', 'ultimate-fields' ),
			'terms' => __( 'Taxonomies', 'ultimate-fields' ),
			'users' => __( 'Users', 'ultimate-fields' )
		);

 		$fields = array(
			Field::create( 'multiselect', 'wp_object_types', __( 'Object Types', 'ultimate-fields' ) )
				->set_input_type( 'checkbox' )
				->add_options( $helper_classes )
				->required(),
			Field::create( 'multiselect', 'wp_object_post_types', __( 'Post Types', 'ultimate-fields' ) )
				->set_input_type( 'checkbox' )
				->add_dependency( 'wp_object_types', 'posts', 'CONTAINS' )
				->add_options( ultimate_fields()->get_available_post_types() )
				->set_default_value( array( 'page' ) )
				->set_description( __( 'The user will be able to select items from the selected post types.', 'ultimate-fields' ) ),
			Field::create( 'multiselect', 'wp_object_taxonomies', __( 'Taxonomies', 'ultimate-fields' ) )
				->set_input_type( 'checkbox' )
				->add_dependency( 'wp_object_types', 'terms', 'CONTAINS' )
				->add_options( ultimate_fields()->get_available_taxonomies() )
				->set_default_value( array( 'category' ) )
				->set_description( __( 'The user will be able to select terms from the selected taxonomies.', 'ultimate-fields' ) ),
			Field::create( 'wp_object', 'default_value_wp_object', __( 'Default value', 'ultimate-fields' ) ),
			Field::create( 'text', 'wp_object_text', __( 'Button Text', 'ultimate-fields' ) )
				->set_default_value( __( 'Select item', 'ultimate-fields' ) ) // use the string from the main plugin
				->set_description( __( 'This text will be used for the selection button and some titles.', 'ultimate-fields' ) )
 		);

		$output = array(
			Field::create( 'select', 'wp_object_output_type', __( 'Output Type', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_dependency( 'type', array( 'WP_Object', 'WP_Objects' ), 'IN' )
				->add_options(array(
					'id'    => __( 'The ID of the selected object', 'ultimate-fields' ),
					'title' => __( 'The title of the selected object', 'ultimate-fields' ),
					'url'   => __( 'The URL of the selected object', 'ultimate-fields' ),
					'link'  => __( 'A link to the selected object', 'ultimate-fields' )
				)),

			Field::create( 'text', 'wp_object_link_text', __( 'Link Text', 'ultimate-fields' ) )
				->add_dependency( 'type', array( 'WP_Object', 'WP_Objects' ), 'IN' )
				->add_dependency( 'wp_object_output_type', 'link' )
				->set_description( __( 'Leave blank to use the title of the selected object as link text.', 'ultimate-fields' ) )
		);

		$appearance = array(
			Field::create( 'checkbox', 'hide_filters', __( 'Hide filters', 'ultimate-fields' ) )
				->fancy()
				->set_description( __( 'Hides the filters in the chooser.', 'ultimate-fields' ) )
				->set_text( __( 'Hide filters', 'ultimate-fields' ) )
		);

 		return array(
 			'general'    => $fields,
			'appearance' => $appearance,
 			'output'     => $output
 		);
 	}
}
