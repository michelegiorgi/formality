<?php
namespace Ultimate_Fields\Container;

use Ultimate_Fields\Container;
use Ultimate_Fields\Field;

class Repeater_Group extends Group {
	/**
	 * Holds the edit mode for that group.
	 *
	 * By default it's inline (meta box).
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $edit_mode = 'inline';

	/**
	 * Holds the maximum allowed occurences of the group.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $maximum = 0;

	/**
	 * Holds the custom title background when one is set.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $title_background = '';

	/**
	 * Holds the custom title color when one is set.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $title_color = '';

	/**
	 * Groups can have a border of a different color.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $border_color;

	/**
	 * Holds the custom icon when one is set.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $icon = '';

	/**
	 * Holds the backbone template for the data preview in title.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $title_template;

	/**
	 * Holds the ID of a container, whose fields will be loaded in the group, if any.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $source_container;

	/**
	 * Initializes basic class properties.
	 *
	 * @since 3.0
	 *
	 * @param string $id   The ID/type of the group.
	 * @param array  $args Arguments for the group.
	 */
	public function __construct( $title, $args = array() ) {
		parent::__construct( $title, $args );

		if( isset( $args[ 'title_template' ] ) ) {
			$this->title_template = $args[ 'title_template' ];
		}

		# Parse normal arguments
		if( isset( $args[ 'fields' ] ) )           $this->add_fields( $args[ 'fields' ] );
		if( isset( $args[ 'edit_mode' ] ) )        $this->set_edit_mode( $args[ 'edit_mode' ] );
		if( isset( $args[ 'maximum' ] ) )          $this->set_maximum( $args[ 'maximum' ] );
		if( isset( $args[ 'title_background' ] ) ) $this->set_title_background( $args[ 'title_background' ] );
		if( isset( $args[ 'title_color' ] ) )      $this->set_title_color( $args[ 'title_color' ] );
		if( isset( $args[ 'border_color' ] ) )     $this->set_border_color( $args[ 'border_color' ] );
		if( isset( $args[ 'icon' ] ) )             $this->set_icon( $args[ 'icon' ] );
	}

    public function export_settings() {
    	$settings = parent::export_settings();

    	# Locate the title
		$title = $this->title_template;
		if( is_null( $title ) ) {
			$title = '';

			foreach( $this->get_fields() as $field ) {
				if( ! is_a( $field, Field\Text::class ) ) {
					continue;
				}

				$title = '<%= ' . $field->get_name() . '.replace( /(<([^>]+)>)/ig,"" ) %>';
				break;
			}
		}
		$settings[ 'title_template' ] = $title;

		# Add basic settings
		if( is_admin() && isset( $GLOBALS[ 'wp_customize' ] ) ) {
			$settings[ 'edit_mode' ]  = 'popup';
		} else {
			$settings[ 'edit_mode' ]  = $this->edit_mode;
		}

		if( $this->maximum )          $settings[ 'maximum' ]          = $this->maximum;
		if( $this->title_background ) $settings[ 'title_background' ] = $this->title_background;
		if( $this->title_color )      $settings[ 'title_color' ]      = $this->title_color;
		if( $this->border_color )     $settings[ 'border_color' ]     = $this->border_color;
		if( $this->icon )             $settings[ 'icon' ]             = $this->icon;

    	return $settings;
    }

	/**
	 * Controls the way the values of the group can be edited.
	 * Allowes options: 'inline', 'popup', 'both'.
	 *
	 * @since 3.0
	 *
	 * @param string $edit_mode The new edit mode. Please check the documentation of the repeater field.
	 * @return Ultimate_Fields\Container\Repeater_Group The inance of the class, useful for chaining.
	 */
	public function set_edit_mode( $edit_mode ) {
		$this->edit_mode = $edit_mode;

		return $this;
	}

	/**
	 * Returns the edit_mode that is required for that options page.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_edit_mode() {
		return $this->edit_mode;
	}

	/**
	 * Controls how many times the group can be added to the repeater.
	 *
	 * @since 3.0
	 *
	 * @param int $maximum
	 * @return Ultimate_Fields\Container\Repeater_Group The inance of the class, useful for chaining.
	 */
	public function set_maximum( $maximum ) {
		$this->maximum = $maximum;

		return $this;
	}

	/**
	 * Returns the maximum that is required for that options page.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_maximum() {
		return $this->maximum;
	}

	/**
	 * Sets the title background for the group.
	 *
	 * This color will be ....
	 *
	 * @since 3.0
	 *
	 * @param string $title_background
	 * @return Ultimate_Fields\Container\Repeater_Group The group, in order to allow chaining.
	 */
	public function set_title_background( $title_background ) {
		$this->title_background = $title_background;

		return $this;
	}

	/**
	 * Returns the title background of the group.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_title_background() {
		return $this->title_background;
	}

	/**
	 * Sets the title color for the group.
	 * This color will be used as the background of the title-bar that is always visible.
	 *
	 * @since 3.0
	 *
	 * @param string $title_color
	 * @return Ultimate_Fields\Container\Repeater_Group The group, in order to allow chaining.
	 */
	public function set_title_color( $title_color ) {
		$this->title_color = $title_color;

		return $this;
	}

	/**
	 * Returns the title color of the group.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_title_color() {
		return $this->title_color;
	}

	/**
	 * Sets the border color for the group.
	 *
	 * @since 3.0
	 *
	 * @param string $border_color
	 * @return Ultimate_Fields\Container\Repeater_Group The group, in order to allow chaining.
	 */
	public function set_border_color( $border_color ) {
		$this->border_color = $border_color;

		return $this;
	}

	/**
	 * Returns the border color of the group.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_border_color() {
		return $this->border_color;
	}

	/**
	 * Sets the icon for the group.
	 *
	 * @since 3.0
	 *
	 * @param string $icon The dashicons icon to use.
	 * @return Ultimate_Fields\Container\Repeater_Group The group, in order to allow chaining.
	 */
	public function set_icon( $icon ) {
		$this->icon = $icon;

		return $this;
	}

	/**
	 * Returns the icon of the group.
	 *
	 * @since 3.0
	 *
	 * @return string The icon for the group.
	 */
	public function get_icon() {
		return $this->icon;
	}

	/**
	 * Sets the template that will be used for the title of the group.
	 *
	 * @since 3.0
	 *
	 * @param string $template A underscore.js template.
	 * @return Ultimate_Fields\Container\Repeater_Group
	 */
	public function set_title_template( $template ) {
		if( ! is_null( $template ) ) {
			$this->title_template = $template;
		}

		return $this;
	}

	/**
	 * Goes through the fields of the group and forces widths to reach 100.
	 */
	public function fix_table_widths() {
		$no_width = 0;
		$left     = 100;

		# Check the amount of fields, which have no width
		foreach( $this->fields as $field ) {
			if( 100 == $field->get_field_width() ) {
				$no_width++;
			} else {
				$left -= $field->get_field_width();
			}
		}

		if( $no_width > 0 ) {
			# Spread the left width amont fields
			$add = $left / $no_width;

			foreach( $this->fields as $field ) {
				if( 100 == $field->get_field_width() ) {
					$field->set_width( $add );
				}
			}
		} elseif( 0 != $left ) {
			# Resize rows
			$add = $left / count( $this->fields );

			foreach( $this->fields as $field ) {
				$field->set_width( $field->get_field_width() + $add );
			}
		}
	}

	/**
	 * Saves the group.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data to save from, similar to the structure of a _POST array.
	 * @return mixed[] An array of errors.
	 */
	public function save( $data ) {
		$errors = parent::save( $data );

		$this->datastore->set( '__hidden', isset( $data[ '__hidden' ] ) && $data[ '__hidden' ] );
		$this->datastore->set( '__type',   $this->get_id() );
	}

	/**
	 * Instructs the group to load fields from another container.
	 *
	 * @since 3.0
	 *
	 * @param string $container_id The container whose fields will be used.
	 */
	public function load_from( $container_id ) {
		$this->source_container = $container_id;
	}

	/**
	 * Returns the fields of the group with the following priority:
	 * 1. External (source) container.
	 * 2. Callback
	 * 3. Internal fields
	 *
	 * @return Ultimate_Fields\Field[]
	 */
	public function get_fields() {
		if( $this->source_container ) {
			foreach( Container::get_registered() as $container ) {
				if( $this->source_container != $container->get_id() ) {
					continue;
				}

				return $container->get_fields();
			}
		}

		return parent::get_fields();
	}

	/**
	 * Generates an array, which can be exported to both PHP and JSON.
	 *
	 * @since 3.0
	 *
	 * @return mixed[] The JSON-ready data.
	 */
	public function export() {
		$settings = parent::export();

		# Remove the locations from the parent container
		unset( $settings[ 'locations' ] );

		if( $this->source_container ) {
			unset( $settings[ 'fields' ] );
			$settings[ 'fields_source' ] = 'container';
			$settings[ 'container' ] = $this->source_container;
		}

		# Change the layout
		if( 'grid' != $this->layout ) {
			$settings[ 'layout' ] = $this->layout;
		} elseif( isset( $settings[ 'layout' ] ) ) {
			unset( $settings[ 'layout' ] );
		}

		# Export the other generic properties
		$this->export_properties( $settings, array(
			'edit_mode'        => array( 'edit_mode', 'inline' ),
			'maximum'          => array( 'maximum', 0 ),
			'title_background' => array( 'title_background', '#ffffff' ),
			'title_color'      => array( 'title_color', '#000000' ),
			'border_color'     => array( 'border_color', null ),
			'icon'             => array( 'icon', '' ),
			'title_template'   => array( 'title_template', null ),
		));

		return $settings;
	}
}
