<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the select field in the UI.
 *
 * @since 3.0
 */
class Select extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return _x( 'Select', 'field', 'ultimate-fields' );
	}

	public static function get_fields( $existing ) {
		$general_fields = array(
			Field::create( 'select', 'select_input_type', __( 'Input type', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_orientation( 'horizontal' )
				->add_options(array(
					'dropdown' => __( 'Dropdown', 'ultimate-fields' ),
					'radio'    => __( 'Radio buttons', 'ultimate-fields' )
				)),
			Field::create( 'select', 'select_options_type', __( 'Source', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_default_value( 'manual' )
				->set_orientation( 'horizontal' )
				->add_options(array(
					'manual' => __( 'Manual', 'ultimate-fields' ),
					'posts'  => __( 'Automatically load pages/posts', 'ultimate-fields' )
				)),
			Field::create( 'textarea', 'select_options', __( 'Options', 'ultimate-fields' ) )
				->add_dependency( 'select_options_type', 'manual' )
				->set_description( __( 'Supports both just values and key-value combinations. Enter one option per row. Separate the key and value by double colons (::).', 'ultimate-fields' ) ),
			Field::create( 'select', 'select_post_type', __( 'Post Type', 'ultimate-fields' ) )
				->add_options( ultimate_fields()->get_available_post_types() )
				->add_dependency( 'select_options_type', 'posts' )
				->set_description( __( 'Select the post type to load options from.', 'ultimate-fields' ) )
		);

		$appearance_fields = array(
			Field::create( 'select', 'select_orientation', __( 'Orientation', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_orientation( 'horizontal' )
				->add_options(array(
					'vertical'   => __( 'Vertical', 'ultimate-fields' ),
					'horizontal' => __( 'Horizontal', 'ultimate-fields' )
				))
				->add_dependency( 'select_input_type', 'radio' ),
			Field::create( 'checkbox', 'use_select2', __( 'Use Select2', 'ultimate-fields' ) )
				->set_description( __( 'select2 is a JavaScript plugin, which enchances the look and feel of the select field.', 'ultimate-fields' ) )
				->fancy()
				->add_dependency( 'select_input_type', 'dropdown' )
		);

		$output_fields = array(
			Field::create( 'select', 'select_output_data_type', __( 'Output Item', 'ultimate-fields' ) )
				->add_options( array(
					'value' => __( 'Output the value of the select, the way it is saved', 'ultimate-fields' ),
					'text'  => __( 'Output the label of the selected value', 'ultimate-fields' )
				))
				->set_input_type( 'radio' )
		);

		return array(
			'general'    => $general_fields,
			'appearance' => $appearance_fields,
			'output'     => $output_fields
		);
	}

	/**
	 * Imports data about the field from post meta.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data the data about the field.
	 */
	public function import( $meta ) {
		$options   = array();
		$type      = isset( $meta[ 'select_options_type' ] ) && 'posts' == $meta[ 'select_options_type' ]
			? 'posts'
			: 'manual';

		$meta[ 'select_options_type' ] = $type;

		if( 'manual' == $type ) {
			$separator = ' :: ';

			if( isset( $meta[ 'select_options' ] ) && $meta[ 'select_options' ] && is_string( $meta[ 'select_options' ] ) ) {
				$raw = explode( "\n", $meta[ 'select_options' ] );
				$raw = array_map( 'trim', $raw );
				$raw = array_filter( $raw );

				foreach( $raw as $row ) {
					if( false !== strpos( $row, $separator ) ) {
						$parts = explode( $separator, $row );
						$options[ $parts[ 0 ] ] = $parts[ 1 ];
					} else {
						$options[ $row ] = $row;
					}
				}
			}

			$meta[ 'select_options' ] = $options;
		} else {
			$meta[ 'select_options_type' ] = 'posts';
			unset( $meta[ 'select_options' ] );
		}

		parent::import( $meta );
	}

	/**
	 * Handles AJAX calls for options.
	 *
	 * @since 3.0
	 */
	public static function generate_ajax_options() {
		$post_type = isset( $_POST[ 'post_type' ] )
			? $_POST[ 'post_type' ]
			: 'post';

		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => 100
		);

		if( is_post_type_hierarchical( $post_type ) ) {
			$args[ 'orderby' ] = array(
				'menu_order' => 'ASC',
				'post_title' => 'ASC'
			);
		}

		$options = array();
		foreach( get_posts( $args ) as $p ) {
			$options[ '' . $p->ID ] = $p->post_title;
		}

		echo json_encode( $options );
		exit;
	}
}
