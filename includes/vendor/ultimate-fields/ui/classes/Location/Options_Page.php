<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\Fields_Collection;
use Ultimate_Fields\UI\Post_Type as UI_Post_Type;
use Ultimate_Fields\Options_Page as Page;
use Ultimate_Fields\Location as Core_Location;

/**
 * Handles containers on option pages.
 *
 * @since 3.0
 */
class Options_Page extends Location {
	/**
	 * Indicate that the location can only be used once within a container.
	 *
	 * @since 3.0
	 * @var bool
	 */
	const LIMIT = true;

	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'options_page';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Options Page', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$fields = new Fields_Collection(array(
			Field::create( 'tab', 'basic_settings', __( 'Basic settings', 'ultimate-fields' ) )
				->set_icon( 'dashicons-menu' )
		));
		$pages  = array();

		# Check for existing pages
		$args = array(
			'post_parent'    => 0,
			'posts_per_page' => -1,
			'post_type'      => UI_Post_Type::instance()->get_slug()
		);

		if( isset( $_GET[ 'post' ] ) && intval( $_GET[ 'post' ] ) ) {
			$args[ 'post__not_in' ] = array( intval( $_GET[ 'post' ] ) );
		}

		foreach( get_posts( $args ) as $container ) {
			$locations = get_post_meta( $container->ID, 'container_locations', true );

			if( ! $locations )
				continue;

			foreach( $locations as $location ) {
				if( self::get_type() != $location[ '__type' ] )
					continue;

				$location_page = $location[ 'options_page' ];
				if( '_create_new' != $location_page )
					continue;

				$pages[ $container->ID ] = $container->post_title;
			}
		}

		$page_options = array();
		foreach( $pages as $id => $title ) {
			$page_options[ $id ] = sprintf( __( 'Use the existing &quot;%s&quot; page', 'ultimate-fields' ), $title );
		}

		$page_options[ '_create_new' ] = __( 'Create a new page', 'ultimate-fields' );

		$fields[] = Field::create( 'select', 'options_page', __( 'Options Page', 'ultimate-fields' ) )
			->set_input_type( 'radio' )
			->set_description( __( 'You can either create a new page or use an existing one.', 'ultimate-fields' ) )
			->add_options( $page_options )
			->set_default_value( '_create_new' );

		# Basic location rule for the options page.
		$fields[] = Field::create( 'select', 'menu_location', __( 'Show page', 'ultimate-fields' ) )
			->add_dependency( 'options_page', '_create_new' )
			->set_description( __( 'Select where in the admin menu you want the page to appear.', 'ultimate-fields' ) )
			->set_input_type( 'radio' )
			->add_options( array(
				'menu'          => __( 'In the Main Menu', 'ultimate-fields' ),
				'settings'      => __( 'Under the Settings tab', 'ultimate-fields' ),
				'appearance'    => __( 'Under the Appearance tab', 'ultimate-fields' ),
				'tools'         => __( 'Under the Tools Tab', 'ultimate-fields' ),
				'other_uf_page' => __( 'Under another Ultimate Fields page.', 'ultimate-fields' ),
				'other_page'    => __( 'Under another page, specified by slug', 'ultimate-fields' )
			));

		if( is_multisite() && is_main_site() ) {
			$fields[ 'menu_location' ]->add_option( 'network', __( 'Network admin menu', 'ultimate-fields' ) );
		}

		if( empty( $pages ) ) {
			$message = 	__( 'Right now, there are no top level Ultimate Fields options pages. Until you add a top level page and select it here, this page will be displayed under in main menu.', 'ultimate-fields' );

			$parent_page_field = Field::create( 'message', 'parent_page', __( 'Parent Page', 'ultimate-fields' ) )
					->set_description( $message );
		} else {
			$parent_page_field = Field::create( 'select', 'parent_page', __( 'Parent Page', 'ultimate-fields' ) );
			$parent_page_field->add_options( $pages );
			$parent_page_field->set_input_type( 'radio' );
		}

		$fields[] = $parent_page_field
			->add_dependency( 'menu_location', 'other_uf_page' )
			->add_dependency( 'options_page', '_create_new' );

		# Parent slug when the parent is specified by slug.
		$fields[] = Field::create( 'text', 'parent_slug', __( 'Parent Page Slug', 'ultimate-fields' ) )
			->add_dependency( 'menu_location', 'other_page' );

		# Allows choosing the right icon type.
		$icon_types = array(
			'default'   => __( 'Default Icon', 'ultimate-fields' ),
			'css_class' => __( 'CSS selector', 'ultimate-fields' ),
			'image'     => __( 'Upload an image', 'ultimate-fields' )
		);

		if( class_exists( 'Ultimate_Fields\\Field\\Icon' ) ) {
			$icon_types[ 'icon' ] = __( 'Select a Dashicons icon', 'ultimate-fields' );
		}

		$fields[] = Field::create( 'select', 'icon_type', __( 'Icon Type', 'ultimate-fields' ) )
			->add_dependency( 'menu_location', array( 'menu', 'network' ), 'IN' )
			->add_dependency( 'options_page', '_create_new' )
			->set_input_type( 'radio' )
			->add_options( $icon_types )
			->set_description( __( 'Select what type of icon should appear in the menu.', 'ultimate-fields' ) );

		$fields[] = Field::create( 'file', 'icon_file', __( 'Menu Icon', 'ultimate-fields' ) )
			->add_dependency( 'menu_location', array( 'menu', 'network' ), 'IN' )
			->add_dependency( 'icon_type', 'image')
			->set_file_type( 'image' );

		$fields[] = Field::create( 'text', 'icon_class', __( 'Icon CSS class', 'ultimate-fields' ) )
			->add_dependency( 'menu_location', array( 'menu', 'network' ), 'IN' )
			->add_dependency( 'icon_type', 'css_class' );

		if( class_exists( 'Ultimate_Fields\\Field\\Icon' ) ) {
			$fields[] = Field::create( 'icon', 'icon_icon', __( 'Icon', 'ultimate-fields' ) )
				->add_dependency( 'menu_location', array( 'menu', 'network' ), 'IN' )
				->add_dependency( 'icon_type', 'icon' )
				->add_set( 'dashicons' );
		}

		if( class_exists( Field\Number::class ) ) {
			$position_field = Field::create( 'number', 'menu_position', __( 'Menu Position', 'ultimate-fields' ) )
				->enable_slider( 1, 200 );
		} else {
			$position_field = Field::create( 'text', 'menu_position', __( 'Menu Position', 'ultimate-fields' ) );
		}

		$fields[] = $position_field
			->set_description( __( 'Be careful with this setting, because you might silently overwrite another items as WordPress does not check if the particular position is free.', 'ultimate-fields' ) )
			->add_dependency( 'menu_location', array( 'menu', 'network' ), 'IN' );

		$fields[] = FIeld::create( 'tab', 'advanced_settings', __( 'Advanced settings', 'ultimate-fields' ) )
			->set_icon( 'dashicons-admin-generic' );

		$fields[] = Field::create( 'select', 'context', __( 'Context', 'ultimate-fields' ) )
			->set_description( __( 'Which column should the container be displayed in?', 'ultimate-fields' ) )
			->add_options(array(
				'normal' => __( 'Normal', 'ultimate-fields' ),
				'side'   => __( 'Side', 'ultimate-fields' ),
			))
			->set_input_type( 'radio' );

		$fields[] = Field::create( 'select', 'priority', __( 'Priority', 'ultimate-fields' ) )
			->set_description( __( 'Either normal for default flow, or High to force higher position.', 'ultimate-fields' ) )
			->add_options(array(
				'high' => __( 'Default (High)', 'ultimate-fields' ),
				'low'  => __( 'Low', 'ultimate-fields' )
			))
			->set_input_type( 'radio' );

		$fields->merge_with( self::get_customizer_fields() );
		$fields->merge_with( self::get_rest_fields() );

		return $fields;
	}

	/**
	 * Exports the settings for the current location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Container\Repeater_Group
	 */
	public static function settings() {
		$group = parent::settings();
		$group->set_maximum( 1 );

		return $group;
	}

	/**
	 * Exports the location as a real, core location.
	 *
	 * @since 3.0
	 *
	 * @return Core_Location
	 */
	public static function export( $data, $helper ) {
		if( ! isset( $data[ 'options_page' ] ) || ! $data[ 'options_page' ] ) {
			return false;
		}

		if( '_create_new' != $data[ 'options_page' ] ) {
			$page = sanitize_title( get_post_field( 'post_title', $data[ 'options_page' ] ) );
		} else {
			$page = array(
				'id'    => sanitize_title( $helper->prop( 'title' ) ),
				'title' => $helper->prop( 'title' )
			);

			switch( $data[ 'menu_location' ] ) {
				case 'settings':
				case 'appearance':
				case 'tools':
					$page[ 'type' ] = $data[ 'menu_location' ];
					break;
				case 'other_uf_page':
					$page_id = $data[ 'parent_page' ];
					$slug    = get_post_field( 'post_title', $page_id );

					if( $slug ) {
						$page[ 'parent_slug' ] = sanitize_title( $slug );
					}
					break;
				case 'other_page':
					$page[ 'parent_slug' ] = $data[ 'parent_slug' ];
					break;
				case 'network':
				case 'menu':
					if( 'network' == $data[ 'menu_location' ] )  {
						$page[ 'type' ] = 'network';
					}
					switch( $data[ 'icon_type' ] ) {
						case 'css_class':
							$page[ 'icon' ] = $data[ 'icon_class' ];
							break;
						case 'image':
							if( $data[ 'icon_file' ] ) {
								$page[ 'icon' ] = wp_get_attachment_url( $data[ 'icon_file' ] );
							}
							break;
						case 'icon':
							$page[ 'icon' ] = $data[ 'icon_icon' ];
							break;
					}

					if( isset( $data[ 'menu_position' ] ) && intval( $data[ 'menu_position' ] ) ) {
						$page[ 'position' ] = $data[ 'menu_position' ];
					}
					break;
			}
		}

		$args = array();

		if( $data['context'] )  $args['context']  = $data['context'];
		if( $data['priority'] ) $args['priority'] = $data['priority'];

		$location = Core_Location::create( 'options', $page, $args );

		# Setup the rest API
		self::setup_location_customizer( $location, $data );
		self::setup_location_rest( $location, $data );

		return $location;
	}

	/**
	 * Returns the data of a core location if it can work with it or false if not.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $location The location to export data from.
	 * @return mixed
	 */
	public static function get_settings_for_import( $location ) {
		if( ! is_a( $location, Core_Location::class ) ) {
			return false;
		}

		$data = array(
			'__type' => self::get_type()
		);

		# Get basics
		$data[ 'context' ]  = $location->get_context();
		$data[ 'priority' ] = $location->get_priority();

		$page  = $location->get_page();
		$local = $location->is_created_locally();

		# Check for customizer data
		self::import_customizer( $location, $data );

		# Check for REST data
		self::import_rest( $location, $data );

		# If there's no page, don't proceed
		if( ! $page )
			return $data;

		# Check if another page is used
		if( ! $local ) {
			$data[ 'options_page' ] = $page->get_id();
			return $data;
		}

		# Generate the new page
		$data[ 'options_page' ] = '_create_new';

		# Use a simple sub-page
		if( in_array( $page->get_type(), array( 'settings', 'appearance', 'tools' ) ) ) {
			$data[ 'menu_location' ] = $page->get_type();
			return $data;
		}

		$parent = $page->get_parent();
		if( $parent ) {
			# Determine the parent
			if( $parent_id = self::find_container_by_page_slug( $parent ) ) {
				$data[ 'menu_location' ] = 'other_uf_page';
				$data[ 'parent_page' ] = $parent_id;
			} else {
				$data[ 'menu_location' ] = 'other_page';
				$data[ 'parent_slug' ]   = $parent;
			}

			return $data;
		}

		# Add data about a top-level page
		if( 'netowrk' == $page->get_type() ) {
			$data[ 'menu_location' ] = 'network';
		} else {
			$data[ 'menu_location' ] = 'menu';
		}

		# Check for an icon
		$data[ 'icon_type' ] = 'default';
		$icon = $page->get_icon();
		if( $icon ) {
			if( 0 === strpos( $icon, 'dashicons-' ) ) {
				$data[ 'icon_type' ] = 'icon';
				$data[ 'icon_icon' ] = $icon;
			} elseif( 0 === strpos( $icon, 'http' ) ) {
				global $wpdb;

				$sql = "SELECT ID FROM $wpdb->posts WHERE guid=%s";
				$sql = $wpdb->prepare( $sql, $icon );
				$icon_id = $wpdb->get_var( $sql );

				$data[ 'icon_type' ] = 'image';
				$data[ 'icon_file' ] = $icon_id;
			} else {
				$data[ 'icon_type' ] = 'css_class';
				$data[ 'icon_class' ] = $icon;
			}
		}

		# Check for a position
		if( $position = $page->get_position() ) {
			$data[ 'menu_position' ] = $position;
		}

		return $data;
	}

	/**
	 * Locates the ID of an UI container based on page slug.
	 *
	 * @since 3.0
	 *
	 * @param string $slug The slug of the needed page.
	 * @return int|bool
	 */
	public static function find_container_by_page_slug( $slug ) {
		global $wpdb;

		$sql = "SELECT post_id, meta_value
		FROM $wpdb->posts posts
		INNER JOIN $wpdb->postmeta meta ON meta.post_id=posts.ID AND meta.meta_key='container_locations'
		WHERE meta_value LIKE '%%_create_new%%' AND post_type=%s AND post_status='publish'";
		$sql = $wpdb->prepare( $sql, UI_Post_Type::instance()->get_slug() );

		foreach( $wpdb->get_results( $sql, ARRAY_A ) as $row ) {
			$data = unserialize( $row[ 'meta_value' ] );

			foreach( $data as $location ) {
				if(
					self::get_type() == $location[ '__type' ]
					&& '_create_new' == $location[ 'options_page' ]
				) {
					return $row[ 'post_id' ];
				}
			}
		}

		return false;
	}
}
