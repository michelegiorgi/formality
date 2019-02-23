<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Datastore\Post_Meta as Datastore;
use Ultimate_Fields\Helper\Menu_Walker;
use Ultimate_Fields\Location\Menu_Item as Menu_Item_Location;

/**
 * Handles containers in menus.
 *
 * @since 3.0
 */
class Menu_Item extends Controller {
	/**
	 * Holds the menu that is currently being edited.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $menu;

	/**
	 * Holds levels for menu items.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $item_levels = array();

	/**
	 * Adds the neccessary hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'change_admin_walker' ) );
		add_action( 'uf.menu_item_output', array( $this, 'output_item_data' ), 10, 3 );
		add_action( 'admin_footer', array( $this, 'render_settings' ), 10, 3 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'save' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'load-nav-menus.php', array( $this, 'do_ajax' ), 8, 2 );
	}

	/**
	 * Overwrites the menu walker with the one from the plugin in order to allow
	 * hooking up and displaying items within the menu.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Helper\Menu_Walker
	 */
	public function change_admin_walker( $walker_class ) {
		if(
			! ( defined( 'DOING_AJAX' ) && DOING_AJAX  )
			&& ! ( function_exists( 'get_current_screen' ) && 'nav-menus' === get_current_screen()->id )
		) {
			return $walker_class;
		}

		if( isset( $_REQUEST[ 'menu' ] ) ) {
			$menu = intval( $_REQUEST[ 'menu' ] );
		} else if( isset( $GLOBALS[ 'nav_menu_selected_id' ] ) ) {
			$menu = intval( $GLOBALS[ 'nav_menu_selected_id' ] );
		} else {
			return $walker_class;
		}

		foreach( $this->combinations as $combination ) {
			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->works_with_menu( $menu ) ) {
					return Menu_Walker::class;
				}
			}
		}

		return $walker_class;
	}

	/**
	 * Outputs the data for a particular menu item. Settings will be rendered later.
	 *
	 * @since 3.0
	 *
	 * @param string  $output The output string.
	 * @param WP_Post $item   The menu item that is being rendered.
	 * @param mixed[] $args   The args for the walker.
	 */
	public function output_item_data( $output, $item, $args ) {
		$datastore = new Datastore();
		$datastore->set_id( $item->ID );

		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];

			# Use the right datastore
			$container->set_datastore( $datastore );

			# Add the output with the data
			$output .= sprintf(
				'<script type="text/json" class="uf-menu-item-data" data-container="%s" data-item-id="%d">%s</script>',
				$container->get_id(),
				$item->ID,
				json_encode( $container->export_data() )
			);
		}

        return $output;
	}

	/**
	 * Outputs the settings of all containers.
	 *
	 * @since 3.0
	 */
	public function render_settings() {
		foreach( $this->combinations as $combination ) {
			$settings = $combination[ 'container' ]->export_settings();
			$settings[ 'locations' ]     = array();
			$settings[ 'show_in_popup' ] = false;
			$settings[ 'layout' ]        = 'rows';

			foreach( $combination[ 'locations' ] as $location ) {
				$settings[ 'locations' ][] = $location->export_settings();

				if( $location->is_shown_in_popup() ) {
					$settings[ 'show_in_popup' ] = true;
				}
			}

			printf(
	            '<script type="text/json" class="uf-menu-settings" id="uf-menu-settings-%s">%s</script>',
				$combination[ 'container' ]->get_id(),
	            json_encode( $settings )
	        );
		}

		# Enqueue the neccessary templates
		Template::add( 'menu', 'container/menu' );
		Template::add( 'field-menu', 'field/wrap/menu' );
		Template::add( 'menu-error', 'container/menu-error' );
		Template::add( 'overlay-wrapper', 'overlay-wrapper' );

		# We are already in the footer, so we need to output everything
		Template::instance()->output_templates();
	}

	/**
	 * Enqueues menu scripts whenever useful.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( 'nav-menus' != get_current_screen()->id ) {
			return;
		}

		# Ensure unique field names
		$this->ensure_unique_field_names();

		# Enqueue the scripts
		wp_enqueue_script( 'uf-container-menu' );
		foreach( $this->combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Localize
		ultimate_fields()
			->localize( 'menu-issues', __( 'Your data cannot be saved because some menu items contain errors. Please resolve them and try again.', 'ultimate-fields' ) )
			->localize( 'close-menu-item', _x( 'Close', 'menu-item', 'ultimate-fields' ) );

		# Attach translations
		ultimate_fields()->l10n()->enqueue();
	}

	/**
	 * Saves data about menu items.
	 *
	 * @since 3.0
	 *
	 *
	 * @param int   $menu_id         ID of the updated menu.
	 * @param int   $menu_item_db_id ID of the updated menu item.
	 * @param array $args            An array of arguments used to update a menu item.
	 */
	public function save( $menu_id, $menu_item_db_id, $args ) {
		# Ensure unique field names
		$this->ensure_unique_field_names();

		# Create and prepare the datastore
		$datastore = new Datastore();
		$datastore->set_id( $menu_item_db_id );

		# Prepare the level of the menu item
		$level = 1;
		if( $args[ 'menu-item-parent-id' ] ) {
			$pid = $args[ 'menu-item-parent-id' ];

			if( isset( $this->item_levels[ $pid ] ) ) {
				$level = $this->item_levels[ $pid ] + 1;
			}
		}
		$this->item_levels[ $menu_item_db_id ] = $level;

		# Go through each container and perform the saving
		$errors = array();
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];

			$data_key = sprintf(
				'menu-item-uf-%s-%s',
				$menu_item_db_id,
				$container->get_id()
			);

			# Check if there's data
			if( ! isset( $_POST[ $data_key ] ) )
				continue;

			$data = json_decode( stripslashes( $_POST[ $data_key ] ), true );

			# Set the datastore to the container and fields
			$container->set_datastore( $datastore );

			# Save the container into the datastore and keep messages
			$container_errors = $container->save( $data );

			# Check if validation is needed
			$report = false;
			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->works_with_level( $level ) ) {
					$report = true;
					break;
				}
			}

			if( $report ) {
				$errors = array_merge(
					$errors,
					$container_errors
				);
			}
		}

		# Save or die
		if( empty( $errors ) ) {
			$datastore->commit();
		} else {
			$this->error( $errors );
		}
	}

 	/**
 	 * Performs AJAX for menu items.
 	 *
 	 * @since 3.0
 	 */
 	public function do_ajax() {
 		ultimate_fields()->ajax( Menu_Item_Location::WORKS_WITH_KEYWORD );
 	}
}
