<?php
namespace Ultimate_Fields\Helper;

use Ultimate_Fields\Container;
use Ultimate_Fields\Field;
use Ultimate_Fields\Options_Page;
use Ultimate_Fields\Container\Repeater_Group;

/**
 * Helps with the migration from V1 to V2.
 *
 * This class will create a new container based on the settings from version 1.
 * That container can later be exported as JSON and/or saved in the database.
 *
 * @since 3.0
 */
class V1_Migrator {
	/**
	 * Holds the original V1 data for a container.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $source;

	/**
	 * The container that will be generated based on old settings.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Container
	 */
	protected $container;

	/**
	 * Starts the migration process up.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data about the container.
	 */
	public function __construct( $data ) {
		$this->source = $data;

		$this->container = Container::create( $data[ 'uf_title' ] );
		if( isset( $data[ 'uf_description' ] ) && $data[ 'uf_description' ] )
			$this->container->set_description( $data[ 'uf_description' ] );

		$this->import_options();
		$this->import_post_meta();
		self::import_fields( $this->source[ 'fields' ], $this->container );
	}

	/**
	 * Imports options locations.
	 *
	 * @since 3.0
	 */
	protected function import_options() {
		if( 'post-meta' == $this->source[ 'uf_type' ] ) {
			return;
		}

		# Create the page
		$s = &$this->source;
		$page = Options_Page::create(
			$s[ 'uf_options_page_slug' ] ? $s[ 'uf_options_page_slug' ] : sanitize_title( $s[ 'uf_title' ] ),
			$s[ 'uf_title' ]
		);
		$this->container->add_location( 'options', $page );

		# Setup the page
		if( isset( $s[ 'uf_options_page_type' ] ) && $s[ 'uf_options_page_type' ] )
			$page->set_type( $s[ 'uf_options_page_type' ] );

		if( isset( $s[ 'uf_options_icon' ] ) && $s[ 'uf_options_icon' ] )
			$page->set_icon( $s[ 'uf_options_icon' ] );

		if( isset( $s[ 'uf_options_menu_position' ] ) && $s[ 'uf_options_menu_position' ] )
			$page->set_position( $s[ 'uf_options_menu_position' ] );
	}

	/**
	 * Imports post meta locations.
	 *
	 * @since 3.0
	 */
	protected function import_post_meta() {
		if( 'post-meta' != $this->source[ 'uf_type' ] ) {
			return;
		}

		# Prepare arguments
		$args = array(
			'templates' => isset( $this->source[ 'uf_postmeta_templates' ] ) ? $this->source[ 'uf_postmeta_templates' ] : array()
		);

		if( isset( $this->source[ 'uf_postmeta_levels' ] ) && '0' !== '' . $this->source[ 'uf_postmeta_levels' ] ) {
			$args[ 'levels' ] = $this->source[ 'uf_postmeta_levels' ];
		}

		# Add the location to the container
		$this->container->add_location( 'post_type', $this->source[ 'uf_postmeta_posttype' ], $args );
	}

	/**
	 * Goes through all fields and imports them.
	 *
	 * @since 3.0
	 */
	public static function import_fields( $source, $container ) {
		foreach( $source as $raw ) {
			switch( $raw[ 'type' ] ) {
				case 'tab_start':   $field = self::add_tab( $raw );         break;
				case 'separator':   $field = self::add_separator( $raw );   break;
				case 'text':        $field = self::add_text( $raw );        break;
				case 'textarea':    $field = self::add_textarea( $raw );    break;
				case 'select':      $field = self::add_select( $raw );      break;
				case 'radio':       $field = self::add_radio( $raw );       break;
				case 'set':         $field = self::add_set( $raw );         break;
				case 'select_term': $field = self::add_select_term( $raw ); break;
				case 'select_page': $field = self::add_select_page( $raw ); break;
				case 'checkbox':    $field = self::add_checkbox( $raw );    break;
				case 'file':        $field = self::add_file( $raw );        break;
				case 'richtext':    $field = self::add_richtext( $raw );    break;
				case 'repeater':    $field = self::add_repeater( $raw );    break;
				default:            continue;
			}

			if( $field ) {
				$container->add_field( $field );
			}
		}
	}

	/**
	 * Generates a tab field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_tab( $source ) {
		$label = $source[ 'title' ];
		$tab   = Field::create( 'tab', sanitize_title( $label ), $label );

		if( isset( $source[ 'description' ] ) && $source[ 'description' ] ) {
			$tab->set_description( $source[ 'description' ] );
		}

		if( isset( $source[ 'icon_type' ] ) ) {
			if( 'font' == $source[ 'icon_type' ] ) {
				$tab->set_icon( $source[ 'icon_class' ] );
			} else {
				// files are not supported anymore
			}
		}

		return $tab;
	}

	/**
	 * Generates a separator field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_separator( $source ) {
		$label   = $source[ 'title' ];
		$section = Field::create( 'section', sanitize_title( $label ), $label );

		if( isset( $source[ 'description' ] ) && $source[ 'description' ] ) {
			$section->set_description( $source[ 'description' ] );
		}

		return $section;
	}

	/**
	 * Adds generic details to a fields (description, etc.).
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field $field  The field to prepare.
	 * @param mixed[]   $source The source data.
	 */
	protected static function generic( $field, $source ) {
		if( isset( $source[ 'default_value' ] ) && $source[ 'default_value' ] ) {
			$field->set_default_value( $source[ 'default_value' ] );
		}

		if( isset( $source[ 'description' ] ) && $source[ 'description' ] ) {
			$field->set_description( $source[ 'description' ] );
		}
	}

	/**
	 * Generates a text field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_text( $source ) {
		$field = Field::create( 'text', $source[ 'field_id' ], $source[ 'field_title' ] );
		self::generic( $field, $source );

		if( isset( $source[ 'autocomplete_suggestions' ] ) ) {
			$field->add_suggestions( $source[ 'autocomplete_suggestions' ] );
		}

		if( isset( $source[ 'output_format_value' ] ) ) {
			$field->set_output_format( $source[ 'output_format_value' ] );
		}


		return $field;
	}

	/**
	 * Adds the basic settings for a textarea field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw version one field.
	 * @param Ultimate_Fields\Field $field The field to setup.
	 */
	protected static function setup_textarea( $source, $field ) {
		self::generic( $field, $source );

		if( isset( $source[ 'rows' ] ) && $source[ 'rows' ] )
			$field->set_rows( $source[ 'rows' ] );

		if( isset( $source[ 'output_add_paragraphs' ] ) && $source[ 'output_add_paragraphs' ] )
			$field->add_paragraphs();

		if( isset( $source[ 'output_apply_shortcodes' ] ) && $source[ 'output_apply_shortcodes' ] )
			$field->do_shortcodes();
	}

	/**
	 * Generates a textarea field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_textarea( $source ) {
		$field = Field::create( 'textarea', $source[ 'field_id' ], $source[ 'field_title' ] );

		self::setup_textarea( $source, $field );

		return $field;
	}

	/**
	 * Prepares the generic arguments for a select (or radio, or textarea) field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[]   $source The raw V1 field.
	 * @param Ultimate_Fields\Field $field  The field that should be set up.
	 */
	protected static function prepare_select( $source, $field ) {
		self::generic( $field, $source );

		if( isset( $source[ 'values_source' ] ) && 'posttype' == $source[ 'values_source' ] ) {
			$field->add_posts( $source[ 'post_type' ] );
		} else {
			$options = array();

			foreach( $source[ 'options' ] as $row )
				$options[ $row[ 'key' ] ] = $row[ 'value' ];

			$field->add_options( $options );
		}
	}

	/**
	 * Generates a select field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_select( $source ) {
		$field = Field::create( 'select', $source[ 'field_id' ], $source[ 'field_title' ] );

		self::prepare_select( $source, $field );

		if( isset( $data[ 'jquery_plugin' ] ) && $data[ 'jquery_plugin' ]  )
			$field->fancy();

		if( isset( $data[ 'output_data_type' ] ) && $data[ 'output_data_type' ] )
			$field->set_output_type( $data[ 'output_data_type' ] );

		return $field;
	}

	/**
	 * Generates a radio field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_radio( $source ) {
		$field = self::add_select( $source );
		$field->set_input_type( 'radio' );
		return $field;
	}

	/**
	 * Generates a set field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_set( $source ) {
		$field = Field::create( 'multiselect', $source[ 'field_id' ], $source[ 'field_title' ] );

		self::prepare_select( $source, $field );
		$field->set_input_type( 'checkbox' );

		if( isset( $source[ 'output_type' ] ) ) {
			if( 'join' == $source[ 'output_type' ] ) {
				$format = 'comma';
			} else {
				$format = 'unordered';
			}

			$field->set_output_format( $format );
		}

		if( isset( $source[ 'output_item' ] ) ) {
			$field->set_output_type( $source[ 'output_item' ] );
		}

		return $field;
	}

	/**
	 * Generates a select_term field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_select_term( $source ) {
		$field = Field::create( 'wp_object', $source[ 'field_id' ], $source[ 'field_title' ] );
		self::generic( $field, $source );
		$field->add( 'terms', 'taxonomy=' . $source[ 'taxonomy' ]  );

		if( isset( $source[ 'output_type' ] ) && $source[ 'output_type' ] ) {
			$field->set_output_type( $source[ 'output_type' ] );
		}

		return $field;
	}

	/**
	 * Generates a select_page field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_select_page( $source ) {
		$field = Field::create( 'wp_object', $source[ 'field_id' ], $source[ 'field_title' ] );
		self::generic( $field, $source );
		$field->add( 'posts', 'post_type=page' );

		if( isset( $source[ 'output_type' ] ) ) {
			$output_type = 'id';

			switch( $source[ 'output_type' ] ) {
				case 'page_id':    $output_type = 'id';    break;
				case 'page_title': $output_type = 'title'; break;
				case 'page_link':  $output_type = 'link';  break;
				case 'page_url':   $output_type = 'url';   break;
			}

			$field->set_output_type( $output_type );
		}

		return $field;
	}

	/**
	 * Generates a checkbox field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_checkbox( $source ) {
		$field = Field::create( 'checkbox', $source[ 'field_id' ], $source[ 'field_title' ] );
		self::generic( $field, $source );

		if( isset( $source[ 'text' ] ) && $source[ 'text' ] )
			$field->set_text( $source[ 'text' ] );

		return $field;
	}

	/**
	 * Generates a file field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_file( $source ) {
		$field = Field::create( 'file', $source[ 'field_id' ], $source[ 'field_title' ] );
		self::generic( $field, $source );

		if( isset( $source[ 'output_type' ] ) && $source[ 'output_type' ] )
			$field->set_output_type( $source[ 'output_type' ] );

		return $field;
	}

	/**
	 * Generates a richtext field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_richtext( $source ) {
		$field = Field::create( 'wysiwyg', $source[ 'field_id' ], $source[ 'field_title' ] );

		self::setup_textarea( $source, $field );

		return $field;
	}

	/**
	 * Generates a repeater field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The raw V1 field.
	 * @return Ultimate_Fields\Field
	 */
	protected static function add_repeater( $source ) {
		$field = Field::create( 'repeater', $source[ 'field_id' ], $source[ 'field_title' ] );
		self::generic( $field, $source );

		foreach( $source[ 'repeater_fields' ] as $source_group ) {
			$group = new Repeater_Group( $source_group[ 'key' ], array(
				'title' => $source_group[ 'title' ]
			));

			self::import_fields( $source_group[ 'group_fields' ], $group );

			$field->add_group( $group );
		}

		return $field;
	}

	/**
	 * Returns the freshly generated container.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Container
	 */
	public function get_container() {
		return $this->container;
	}
}
