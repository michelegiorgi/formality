<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\UI\Post_Type;
use Ultimate_Fields\Container;
use Ultimate_Fields\Field;
use Ultimate_Fields\UI\Location;

/**
 * Handles container settings on the post type edit screen.
 *
 * @since 3.0
 */
class Container_Settings {
	/**
	 * Holds the container with the settings.
	 *
	 * @since 3.0
	 * @var Container
	 */
	protected $container;

	/**
	 * Creates an instance of the class.
	 *
	 * @since 3.0
	 *
	 * @return Container_Settings
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Adds the neccessary hooks.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		$post_type = Post_Type::instance();

		$title = __( 'Locations', 'ultimate-fields' );
		Container::create( 'container_locations' )
			->set_title( $title )
			->add_location( 'post_type', $post_type->get_slug() )
			->set_fields_callback( array( $this, 'generate_location_fields' ) );

		$title = __( 'Container', 'ultimate-fields' );
		Container::create( 'container_settings' )
			->add_location( 'post_type', $post_type->get_slug() )
			->set_title( $title )
			->set_description_position( 'label' )
			->set_fields_callback( array( $this, 'generate_settings_fields' ) );
	}

	/**
	 * Generates the fields for the settings.
	 *
	 * @since 3.0
	 *
	 * @return Field[]
	 */
	public function generate_settings_fields() {
		$roles = array();

		if( isset( $GLOBALS[ 'wp_roles' ] ) ) foreach( $GLOBALS[ 'wp_roles' ]->roles as $slug => $role ) {
			$roles[ $slug ] = translate_user_role( $role[ 'name' ] );
		}

		$fields = array(
			Field::create( 'tab', 'appearance', __( 'Appearance', 'ultimate-fields' ) )
				->set_icon( 'dashicons-admin-media' ),
			Field::create( 'select', 'style', __( 'Style', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options(array(
					'boxed'    => __( 'Boxed', 'ultimate-fields' ),
					'seamless' => __( 'Seamless', 'ultimate-fields' )
				))
				->set_description( __( 'Some locations (e.g. widgets) will ignore this option.', 'ultimate-fields' ) ),
			Field::create( 'select', 'layout', __( 'Layout', 'ultimate-fields' ) )
				->add_options(array(
					'auto'  => __( 'Automatic<em> based on the location</em>', 'ultimate-fields' ),
					'rows'  => __( 'Rows <em>Label in a separate column</em>', 'ultimate-fields' ),
					'grid'  => __( 'Grid <em>Label above the field, variable widths</em>', 'ultimate-fields' )
				))
				->set_input_type( 'radio' )
				->set_description( __( 'Grid elements support field widths, while row elements always occupy an entire row.', 'ultimate-fields' ) ),
			Field::create( 'select', 'description_position', __( 'Display field description', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->add_options(array(
					'label' => __( 'after the label', 'ultimate-fields' ),
					'input' => __( 'after the field', 'ultimate-fields' )
				))
				->set_default_value( 'input' )
				->set_description( __( 'You can use descriptions to explain the purpose of a field to your users.', 'ultimate-fields' ) ),
			Field::create( 'textarea', 'description', __( 'Description', 'ultimate-fields' ) )
				->set_description( __( 'The description will be displayed in the beginning of the container.', 'ultimate-fields' ) ),

			Field::create( 'tab', 'advanced', __( 'Advanced', 'ultimate-fields' ) )
				->set_icon( 'dashicons-admin-generic' ),
			Field::create( 'text', 'container_order', __( 'Order', 'ultimate-fields' ) )
				->set_description( __( 'Controls the order of containers on edit screens. Bigger value brings the container higher on the screen', 'ultimate-fields' ) ),
			Field::create( 'multiselect', 'roles', __( 'Roles', 'ultimate-fields' ) )
				->set_input_type( 'checkbox' )
				->add_options( $roles )
				->set_description( __( 'Select the roles, which should have access to the container&apos;s fields or leave blank for all roles. Do not confuse this with the setting of the User location.', 'ultimate-fields' ) )

		);

		return $fields;
	}

	/**
	 * Returns the default settings for a container.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_defaults() {
		return array(
			'style'                => 'boxed',
			'layout'               => 'auto',
			'description_position' => 'field',
			'description'          => '',
			'container_order'      => array(),
			'roles'                => array(),
		);
	}

	/**
	 * Returns the classes for possible locations.
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public static function get_location_classes() {
		static $locations;

		if( ! is_null( $locations ) ) {
			return $locations;
		}

		return apply_filters( 'uf.ui.location_classes', array(
			Location\Post_Type::class,
			Location\Options_Page::class,
			Location\Customizer::class,
			Location\Comment::class,
			Location\Attachment::class,
			Location\Menu_Item::class,
			Location\Shortcode::class,
			Location\Taxonomy::class,
			Location\User::class,
			Location\Widget::class,
		));
	}

	/**
	 * Generates the fields for location settings.
	 *
	 * @since 3.0
	 * @return Field[]
	 */
	public function generate_location_fields() {
		$placeholder_text = __( 'This container will appear based on the locations, which you add here.', 'ultimate-fields' );

		$locations_field = Field::create( 'repeater', 'container_locations', __( 'Locations', 'ultimate-fields' ) )
			->hide_label()
			// ->set_minimum( 1 )
			->set_chooser_type( 'tags' )
			->set_add_text( __( 'Add location:', 'ultimate-fields' ) )
			// ->set_description( $placeholder_text )
			;

		$default_value = false;

		# Generate all locations
		$location_classes = self::get_location_classes();

		foreach( $location_classes as $class_name ) {
			$group = $class_name::settings();
			$group->set_edit_mode( 'inline' );

			if( constant( $class_name . '::LIMIT' ) ) {
				$group->set_maximum( 1 );
			}

			$locations_field->add_group( $group );
		}

		return array( $locations_field );
	}
}
