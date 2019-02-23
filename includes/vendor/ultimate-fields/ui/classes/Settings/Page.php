<?php
namespace Ultimate_Fields\UI\Settings;

use Ultimate_Fields\Template;
use Ultimate_Fields\UI\Migration;

/**
 * Manages the settings page of the plugin.
 *
 * The page consists of different screens, each of them having it's own functionality.
 *
 * @since 3.0
 */
class Page {
	/**
	 * Contains the screen that is being displayed.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\UI\Settings\Screen
	 */
	protected $screen;

	/**
	 * Holds all available screens.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\UI\Settings\Screen[]
	 */
	protected $screens;

	/**
	 * Creates an instance of the settings page.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\UI\Settings\Page
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Instantiates the class.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'attach' ) );
		add_action( 'uf.enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Attaches the page to the menu.
	 *
	 * @since 3.0
	 */
	public function attach() {
		$page_title = $menu_title = __( 'Settings', 'ultimate-fields' );
		$capability = 'manage_options';
		$menu_slug  = 'settings';
		$function   = array( $this, 'display' );

		$id = add_submenu_page( 'edit.php?post_type=ultimate-fields', $page_title, $menu_title, $capability, $menu_slug, $function );

		# Make sure the page is loaded as soon as it's known that it exists
		add_action( "load-$id", array( $this, 'load' ) );
	}

	/**
	 * Loads the page before displaying it.
	 *
	 * @since 3.0
	 */
	public function load() {
		$screens = array();

		$screens[] = new Screen_General;
		$screens[] = new Screen_Import_Export;
		$screens[] = new Screen_JSON_Sync;
		$screens[] = new Screen_About;

		if( Migration::instance()->get_state() == Migration::STATE_PENDING ) {
			$screens[] = new Screen_Migration;
		}

		/**
		 * Allows the tabs (screens) on the settings page to be modified.
		 *
		 * @since 3.0
		 *
		 * @param Ultimate_Fields\UI\Settings\Screen[] $screens
		 * @return Ultimate_Fields\UI\Settings\Screen[]
		 */
		$screens = apply_filters( 'uf.settings.tabs', $screens );

		// Filter the screens based on availability
		$this->screens = array();
		foreach( $screens as $screen ) {
			if( $screen->is_available() ) {
				$this->screens[] = $screen;
			}
		}

		$base_url = admin_url( 'edit.php?post_type=ultimate-fields&page=settings' );
		$current = isset( $_GET[ 'screen' ] ) ? $_GET[ 'screen' ] : false;

		foreach( $this->screens as $i => $screen ) {
			$screen->url = $base_url;

			if( ( ! $i && ! $current ) || ( $current && $screen->get_id() == $current ) ) {
				$screen->active = true;
				$this->screen   = $screen;
				$current        = $screen->get_id();
			}

			if( $i > 0 ) {
				$screen->url .= '&screen=' . $screen->get_id();
			}
		}

		# Once the active screen is found, load it if possible
		if( method_exists( $this->screen, 'load' ) ) {
			$this->screen->load();
		}
	}

	/**
	 * Displays the page.
	 *
	 * @since 3.0
	 */
	public function display() {
		$engine = Template::instance();

		$engine->include_template( 'settings/page', array(
			'screens' => $this->screens
		));

		# After the normal template, include the screen
		$this->screen->display();
	}

	/**
	 * Enqueues the scripts and styles for the page.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( ! function_exists( 'get_current_screen' ) || 'ultimate-fields_page_settings' != get_current_screen()->id ) {
			return;
		}

		wp_enqueue_style( 'ultimate-fields-css' );
	}
}
