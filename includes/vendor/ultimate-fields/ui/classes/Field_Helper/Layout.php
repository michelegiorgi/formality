<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;
use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\UI\Post_Type;
use Ultimate_Fields\UI\Field_Helper\Repeater;

/**
 * Handles the layout field in the UI.
 *
 * @since 3.0
 */
class Layout extends Repeater {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Layout', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for a normal group.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field[]
	 */
	protected static function get_normal_group_fields() {
		# Prepare other containers as options
		$containers = array();
		$post_type = Post_Type::instance();
		foreach( $post_type->get_existing() as $container ) {
			$containers[ $post_type->get_container_hash( $container ) ] = esc_html( $container->post_title );
		}

		# Start with title and name
		$fields = array(
			Field::create( 'tab', 'general', __( 'General', 'ultimate-fields' ) )
				->set_icon( 'dashicons-admin-generic' ),
			Field::create( 'text', 'title', __( 'Title', 'ultimate-fields' ) )
				->required(),
			Field::create( 'text', 'name', __( 'Name', 'ultimate-fields' ) )
				->required()
		);

		$fields_field = Field::create( 'fields', 'fields', __( 'Fields', 'ultimate-fields' ) )
			->required();

		# Add the needed fields for fields source
		if( empty( $containers ) && ! defined( 'UF_UI_IMPORTING' ) ) {
			$fields[] = $fields_field;
		} else {
			$fields = array_merge( $fields, array(
				Field::create( 'select', 'fields_source', __( 'Field source', 'ultimate-fields' ) )
					->set_input_type( 'radio' )
					->add_options(array(
						'manual'    => __( 'Enter manually', 'ultimate-fields' ),
						'container' => __( 'Load a container', 'ultimate-fields' )
					))
					->set_description( __( 'Select whether you prefer enting your fields manually or load them from another container', 'ultimate-fields' ) ),
				$fields_field
					->add_dependency( 'fields_source', 'manual' ),
				Field::create( 'select', 'container', __( 'Source container', 'ultimate-fields' ) )
					->add_dependency( 'fields_source', 'container' )
					->add_options( $containers )
					->set_input_type( 'radio' )
					->set_description( __( 'All fields from the selected containers will be loaded into this group', 'ultimate-fields' ) )
			));
		}

		# Add the rest of the generic fields
		$fields = array_merge( $fields, array(
			Field::create( 'complex', 'size', __( 'Width', 'ultimate-fields' ) )
				->merge()
				->set_description( __( 'You can adjust the minimum and maximum size of the group as a column. The maximum width cannot be bigger than the amount of columns.', 'ultimate-fields' ) )
				->add_fields(array(
					Field::create( 'number', 'minimum_width', __( 'Minimum width', 'ultimate-fields' ) )
						->enable_slider( 1, 20 )
						->set_default_value( 1 ),
					Field::create( 'number', 'maximum_width', __( 'Maximum width', 'ultimate-fields' ) )
						->enable_slider( 1, 20 )
						->set_default_value( 20 )
				)),
			Field::create( 'number', 'maximum', __( 'Maximum occurences', 'ultimate-fields' ) )
				->set_description( __( 'Control how many times the group can appear within the layout.', 'ultimate-fields' ) )
				->set_default_value( 0 ),
			Field::create( 'tab', 'appearance', __( 'Appearance', 'ultimate-fields' ) )
				->set_icon( 'dashicons-admin-media' ),
			Field::create( 'textarea', 'description', __( 'Description', 'ultimate-fields' ) )
				->set_rows( 4 ),
			Field::create( 'color', 'border_color', __( 'Border Color', 'ultimate-fields' ) )
				->set_default_value( '#dddddd' )
				->set_description( __( 'Surround the whole group.', 'ultimate-fields' ) ),
			Field::create( 'complex', 'title_style', __( 'Title-bar style', 'ultimate-fields' ) )
				->set_description( __( 'Visible even when closed', 'ultimate-fields' ) )
				->merge()
				->add_fields(array(
					Field::create( 'color', 'title_background', __( 'Background', 'ultimate-fields' ) )
						->set_width( 50 )
						->set_default_value( '#ffffff' ),
					Field::create( 'color', 'title_color', __( 'Color', 'ultimate-fields' ) )
						->set_width( 50 )
						->set_default_value( '#000000' ),
				)),
			Field::create( 'icon', 'icon', __( 'Icon', 'ultimate-fields' ) )
				->add_set( 'dashicons' ),
			Field::create( 'textarea', 'title_template', __( 'Title Template', 'ultimate-fields' ) )
				->set_description( __( 'You can use an underscore.js template with the names of the fields as variables. If there is an exception while parsing the template, it will not be used.', 'ultimate-fields' ) )
		));

		return $fields;
	}

	/**
	 * Returns the UI editor fields.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Fields_Collection $existing The existing fields within the editor.
	 * @return mixed[] A combination of field arrays for the different tabs/sections.
	 */
	public static function get_fields() {
		$fields = array(
			$groups_field = Field::create( 'repeater', 'layout_groups', __( 'Groups', 'ultimate-fields' ) )
				->set_add_text( __( 'Add group', 'ultimate-fields' ) )
				->add_group( 'group', array(
					'title'                => __( 'Group', 'ultimate-fields' ),
					'layout'               => 'rows',
					'description_position' => 'label',
					'fields'               => self::get_normal_group_fields()
				)),
			Field::create( 'number', 'layout_columns', __( 'Columns', 'ultimate-fields' ) )
				->enable_slider( 1, 20 )
				->set_description( __( 'The amount of columns works as a grid within the field. Each group can have a minimum and maximum amount of columns, which will be based on this number. If you enter 12 here and a column has 6 as a width, you can calculate that as 6/12=50%.', 'ultimate-fields' ) )
				->set_default_value( 12 ),
			Field::create( 'text', 'layout_placeholder_text', __( 'Placeholder Text', 'ultimate-fields' ) )
				->set_width( 50 )
				->set_description( __( 'This text will be displayed in the row, which prompts users to add groups to it.', 'ultimate-fields' ) )
		);

		return array(
			'general'    => $fields
		);
	}

	/**
	 * Imports some meta into the class.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data The settings of the field.
	 */
	public function import( $field_data ) {
		# If there are no groups, don't continue
		if( ! isset( $field_data[ 'layout_groups' ] ) || ! is_array( $field_data[ 'layout_groups' ] ) ) {
			parent::import( $field_data );
			return;
		}

		$groups = array();
		foreach( $field_data[ 'layout_groups' ] as $raw_group ) {
			if( ! isset( $raw_group[ 'fields_source' ] ) || 'container' != $raw_group[ 'fields_source' ] ) {
				$fields = array();

				foreach( $raw_group[ 'fields' ] as $field ) {
					$helper = Field_Helper::import_from_meta( $field );
					$fields[] = $helper->setup_field();
				}

				$raw_group[ 'fields' ] = $fields;
			}

			$groups[] = $raw_group;
		}

		# Replace the groups
		$field_data[ 'layout_groups' ] = $groups;

		parent::import( $field_data );
	}

	/**
	 * Prepares data for import.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data that has been already generated + source data.
	 * @return mixed[]
	 */
	public static function prepare_field_data( $data ) {
		if( isset( $data[ 'layout_groups' ] ) && is_array( $data[ 'layout_groups' ] ) ) {
			$groups = array();

			foreach( $data[ 'layout_groups' ] as $group ) {
				if( isset( $group[ 'fields' ] ) ) {
					$fields = array();

					foreach( $group[ 'fields' ] as $raw_field ) {
						$helper = Field_Helper::import_from_meta( $raw_field );
						$field  = $helper->setup_field();
						$fields[] = Field_Helper::get_field_data( $field );
					}

					$group[ 'fields' ]  = $fields;
				}

				$group[ '__type' ] = 'group';

				$groups[] = $group;
			}

			$data[ 'layout_groups' ] = $groups;
		}

		return $data;
	}

	/**
	 * Enqueues the layout fields' templates and sripts.
	 *
	 * @since 3.0
	 */
	public static function enqueue() {
		wp_enqueue_script( 'uf-layout' );
		wp_enqueue_script( 'uf-field-layout' );

		# Add the necessary templates
		Template::add( 'layout', 'field/layout/base' );
		Template::add( 'layout-placeholder', 'field/layout/placeholder' );
		Template::add( 'layout-element-prototype', 'field/layout/element-prototype' );
	}
}
