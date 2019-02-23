<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\UI\Container_Helper;
use Ultimate_Fields\Field;
use Ultimate_Fields\UI\Post_Type;
use Ultimate_Fields\Template;

/**
 * Handles the repeater field in the UI.
 *
 * @since 3.0
 */
class Repeater extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Repeater', 'ultimate-fields' );
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

		$this->raw_groups = $meta[ 'repeater_groups' ];
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
				->required()
				->set_width( 50 ),
			Field::create( 'text', 'name', __( 'Name', 'ultimate-fields' ) )
				->required()
				->set_width( 50 )
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
					->set_description( __( 'All fields from the selected containers will be loaded into this group.', 'ultimate-fields' ) )
			));
		}

		# Add the rest of the generic fields
		$fields = array_merge( $fields, array(
			Field::create( 'select', 'edit_mode', __( 'Edit Mode', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options(array(
					'inline' => __( 'Inline', 'ultimate-fields' ),
					'popup'  => __( 'Popup', 'ultimate-fields' ),
					'both'   => __( 'Both', 'ultimate-fields' ),
				))
				->set_description( __( 'The fields of the group can appear <strong>inline</strong> within a box, only as a popup or inline with the option for a popup, if you select both.', 'ultimate-fields' ) ),
			Field::create( 'number', 'maximum', __( 'Maximum occurences', 'ultimate-fields' ) )
				->set_description( __( 'Control how many times the group can be added to the repeater.', 'ultimate-fields' ) )
				->set_default_value( 0 ),
			Field::create( 'tab', 'appearance', __( 'Appearance', 'ultimate-fields' ) )
				->set_icon( 'dashicons-admin-media' ),
			Field::create( 'radio', 'layout', __( 'Layout', 'ultimate-fields' ) )
				->set_default_value( 'grid' )
				->add_options(array(
					'grid'  => __( 'Grid <em>Label above the field, variable widths</em>', 'ultimate-fields' ),
					'rows'  => __( 'Rows <em>Label in a separate column</em>', 'ultimate-fields' )
				))
				->set_description( __( 'Grid elements support field widths, while row elements always occupy an entire row.', 'ultimate-fields' ) ),
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
			$groups_field = Field::create( 'repeater', 'repeater_groups', __( 'Groups', 'ultimate-fields' ) )
				->set_add_text( __( 'Add group', 'ultimate-fields' ) )
				->add_group( 'group', array(
					'title'                => __( 'Group', 'ultimate-fields' ),
					'layout'               => 'rows',
					'description_position' => 'label',
					'fields'               => self::get_normal_group_fields()
				)),
			Field::create( 'complex', 'repeater_limits', __( 'Limits', 'ultimate-fields' ) )
				->merge()
				->set_prefix( 'repeater_' )
				->add_fields(array(
					Field::create( 'number', 'minimum', __( 'Minimum', 'ultimate-fields' ) )
						->set_width( 50 )
						->set_description( __( 'This will disable group removal once the amount of groups reaches the limit.', 'ultimate-fields' ) ),
					Field::create( 'number', 'maximum', __( 'Maximum', 'ultimate-fields' ) )
						->set_width( 50 )
						->set_description( __( 'This will prevent users from adding new groups when the meaximum is reached.', 'ultimate-fields' ) ),
				)),
			Field::create( 'complex', 'repeater_labels', __( 'Labels', 'ultimate-fields' ) )
				->merge()
				->add_fields(array(
					Field::create( 'text', 'repeater_add_text', __( '"Add" text', 'ultimate-fields' ) )
						->set_width( 50 )
						->set_description( __( 'This text will be used for the "Add" group button when needed.', 'ultimate-fields' ) ),
					Field::create( 'text', 'repeater_placeholder_text', __( 'Placeholder Text', 'ultimate-fields' ) )
						->set_width( 50 )
						->set_description( __( 'This text will be displayed before any groups are added to the repeater.', 'ultimate-fields' ) )
				))
		);

		$appearance = array(
			Field::create( 'select', 'repeater_layout', __( 'Layout', 'ultimate-fields' ) )
				->add_dependency( 'repeater_groups', 2, '<' )
				->set_input_type( 'radio' )
				->add_options(array(
					'boxes' => __( 'Boxes <em>(normal)</em>', 'ultimate-fields' ),
					'table' => __( 'Table', 'ultimate-fields' )
				)),
			Field::create( 'select', 'repeater_chooser_type', __( 'Chooser Type', 'ultimate-fields' ) )
				->add_dependency( 'repeater_groups', 1, '>' )
				->set_input_type( 'radio' )
				->add_options(array(
					'widgets'  => __( 'Widgets', 'ultimate-fields' ),
					'dropdown' => __( 'Dropdown', 'ultimate-fields' )
				)),
			Field::create( 'color', 'repeater_background_color', __( 'Background Color', 'ultimate-fields' ) )
				->set_default_value( '#fff' )
		);

		return array(
			'general'    => $fields,
			'appearance' => $appearance
		);
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

		foreach( $this->raw_groups as $raw ) {
			$fields = array();

			foreach( $raw[ 'fields' ] as $raw_field ) {
				$sub_field = self::import_from_meta( $raw_field );
				$fields[] = $sub_field->setup_field();
			}

			$field->add_group( $raw[ 'name' ], array(
				'type'   => $raw[ 'name' ],
				'title'  => $raw[ 'title' ],
				'fields' => $fields,
				'layout' => 'rows'
			));
		}

		return $field;
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
		if( ! isset( $field_data[ 'repeater_groups' ] ) || ! is_array( $field_data[ 'repeater_groups' ] ) ) {
			parent::import( $field_data );
			return;
		}

		$groups = array();
		foreach( $field_data[ 'repeater_groups' ] as $raw_group ) {
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
		$field_data[ 'repeater_groups' ] = $groups;

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
		if( isset( $data[ 'repeater_groups' ] ) && is_array( $data[ 'repeater_groups' ] ) ) {
			$groups = array();

			foreach( $data[ 'repeater_groups' ] as $group ) {
				$group[ 'name' ] = $group[ 'type' ];

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

			$data[ 'repeater_groups' ] = $groups;
		}

		return $data;
	}

	/**
	 * Enqueues the scripts and templates for the field.
	 *
	 * @since 3.0
	 */
	public static function enqueue() {
		Template::add( 'field-repeater-table', 'field/repeater/table' );
		Template::add( 'repeater-heading', 'field/repeater/heading' );
		Template::add( 'table-row', 'field/repeater/table-row' );
		Template::add( 'cell-wrap', 'field/wrap/cell' );
		Template::add( 'repeater-dropdown',  'field/repeater/dropdown' );
		Template::add( 'repeater-tags', 'field/repeater/tags' );
		Template::add( 'repeater-prototype', 'field/repeater/prototype' );

		ultimate_fields()->localize( 'repeater-basic-placeholder-multiple', __( 'Drag an item here to create a new entry.', 'ultimate-fields' ) );
		ultimate_fields()->localize( 'repeater-basic-placeholder-single', __( 'Please click the "%s" button to add a new entry.', 'ultimate-fields' ) );
	}

	/**
	 * Prepares data for usage within the UI.
	 * Dynamically loaded containers should be available w/o AJAX.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The previously existing data.
	 * @return mixed[]
	 */
	public static function prepare_editor_data( $data ) {
		$queue = array();

		foreach( $data[ 'repeater_groups' ] as $group ) {
			if( isset( $group[ 'fields_source' ] ) && 'container' == $group[ 'fields_source' ] ) {
				$queue[] = $group[ 'container' ];
			}
		}

		Container_Helper::generate_preview_data( $queue );

		return $data;
	}
}
