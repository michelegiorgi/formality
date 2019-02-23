<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Controller\Menu_Item as Controller;
use Ultimate_Fields\Datastore\Post_Meta as Datastore;
use Ultimate_Fields\Helper\Data_Source;

/**
 * Works as a location definition for containers within menu items.
 *
 * @since 3.0
 */
class Menu_Item extends Location {
	/**
	 * Holds a keyword that would let works_with return true even without an object.
	 *
	 * @since 3.0
	 * @const string
	 */
	const WORKS_WITH_KEYWORD = 'menu_item';

	/**
	 * Holds all locations, which the location works with.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $theme_locations;

	/**
	 * Holds all menu IDs, which the location works with.
	 *
	 * @since 3.0
	 * @var int[]
	 */
	protected $menus;

	/**
	 * Holds all levels, which the container should appear on.
	 *
	 * @since 3.0
	 * @var int[]
	 */
	protected $levels;

	/**
	 * Indicates if the menu should be edited in a popup.
	 */
	protected $popup_mode = false;

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args Additional arguments for the location.
	 */
	public function __construct( $args = array() ) {
		# Send all arguments to the appropriate setter.
		$this->set_and_unset( $args, array(
			'popup_mode' => 'show_in_popup'
		));

		$this->arguments = $args;
	}

	/**
	 * Returns an instance of the controller, which controls the location (menu items).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Menu_Item
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Holds all locations, which the location works with.
	 *
	 * @since 3.0
	 *
	 * @param string[] $theme_locations The theme locations to add to the location.
	 * @return Ultimate_Fields\Location\Menu_Item
	 */
	public function set_theme_locations( $theme_locations ) {
		$this->theme_locations = $this->handle_value( $this->theme_locations, $theme_locations );

		return $this;
	}

	/**
	 * Holds all menu IDs, which the location works with.
	 *
	 * @since 3.0
	 *
	 * @param int[] $menus The menus to add to the location.
	 * @return Ultimate_Fields\Location\Menu_Item
	 */
	public function set_menus( $menus ) {
		$this->menus = $this->handle_value( $this->menus, $menus );

		return $this;
	}

	/**
	 * Holds all levels, which the container should appear on.
	 *
	 * @since 3.0
	 *
	 * @param int[] $levels The levels to add to the location.
	 * @return Ultimate_Fields\Location\Menu_Item
	 */
	public function set_levels( $levels ) {
		$this->levels = $this->handle_value( $this->levels, $levels );

		return $this;
	}

	/**
	 * Returns the settings for the location, sendable to JS.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_settings() {
		$this->parse_arguments( $this->arguments );

		$data = array();

		if( ! empty( $this->levels ) ) {
			$data[ 'levels' ] = $this->levels;
		}

		return $data;
	}

	/**
	 * Checks if the location works with a certain menu.
	 *
	 * @since 3.0
	 *
	 * @param mixed $menu The menu to check.
	 * @return bool
	 */
	public function works_with_menu( $menu ) {
		$this->parse_arguments( $this->arguments );

		if( ! empty( $this->menus ) && ! $this->check_single_value( $menu, $this->menus ) ) {
			return false;
		}

		if( ! empty( $this->theme_locations ) ) {
			$locations = array();

			foreach( get_nav_menu_locations() as $location => $id ) {
				if( $id == $menu ) {
					$locations[] = $location;
				}
			}

			if( ! $this->check_multiple_values( $locations, $this->theme_locations ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if the location works with a specific level.
	 *
	 * @since 3.0
	 *
	 * @param int $level The level of a menu item.
	 * @return bool
	 */
	public function works_with_level( $level ) {
		$this->parse_arguments( $this->arguments );

		if( empty( $this->levels ) ) {
			return true;
		}

		return $this->check_single_value( $level, $this->levels );
	}

	/**
	 * Determines whether the location works with a certain object(type).
	 *
	 * @since 3.0
	 *
	 * @param mixed $object An object or a string to work with.
	 * @return bool
	 */
	public function works_with( $source ) {
		# Check for the attachment type
		if( $source === self::WORKS_WITH_KEYWORD ) {
			return true;
		}

		# If there is no object, then we don't work with it
		if( ! is_object( $source ) ) {
			return false;
		}

		# Convert to a proper post
		if( is_a( $source, 'WP_Post' ) ) {
			$post = $source;
		} elseif( is_a( $source, Data_Source::class ) && 'post_meta' == $source->type && $existing = get_post( $source->item ) ) {
			$post = $existing;
		} else {
			return false;
		}

		if( 'nav_menu_item' != $post->post_type ) {
			return false;
		}

		return $post;
	}

	/**
	 * Generates a datastore based on an object.
	 *
	 * @since 3.0
	 *
	 * @param mixed $object The post to create a datastore for.
	 * @return mixed
	 */
	public function create_datastore( $object ) {
		if( ! is_a( $object, 'WP_Post' ) && ! is_int( $object ) ) {
			return false;
		}

		$post = get_post( $object );
		if( ! $post || 'nav_menu_item' != $post->post_type ) {
			return false;
		}

		$datastore = new Datastore;
		$datastore->set_id( $post->ID );

		return $datastore;
	}

	/**
	 * Switches the location to popup mode.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Location\Menu_Item
	 */
	public function show_in_popup( $flag = true ) {
		$this->popup_mode = (bool) $flag;

		return $this;
	}

	/**
	 * Returns the current state of popup_mode.
	 *
	 * @since 3.0
	 * @return bool
	 */
	public function is_shown_in_popup() {
		return $this->popup_mode;
	}

	/**
	 * Returns the settings for the location, which will be exported.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		$this->export_rule( $settings, 'theme_locations' );
		$this->export_rule( $settings, 'menus' );
		$this->export_rule( $settings, 'levels' );
		if( $this->popup_mode ) {
			$settings[ 'popup_mode' ] = true;
		}

		return $settings;
	}

	/**
	 * Imports the location from PHP/JSON.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The arguments to import.
	 */
	public function import( $args ) {
		if( isset( $args[ 'theme_locations' ] ) ) {
			$this->__set( 'theme_locations', $args[ 'theme_locations' ] );
		}

		if( isset( $args[ 'menus' ] ) ) {
			$this->__set( 'menus', $args[ 'menus' ] );
		}

		if( isset( $args[ 'levels' ] ) ) {
			$this->__set( 'levels', $args[ 'levels' ] );
		}

		if( isset( $args[ 'popup_mode' ] ) && $args[ 'popup_mode' ] ) {
			$this->popup_mode = true;
		}
	}
}
