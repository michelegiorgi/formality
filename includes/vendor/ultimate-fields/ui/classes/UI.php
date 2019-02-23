<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\Template;
use Ultimate_Fields\Autoloader;
use Ultimate_Fields\UI\Settings\Page as Settings_Page;
use Ultimate_Fields\UI\Field_Helper\Select as Select_Helper;

/**
 * This class initializes and handles the basics of the admin UI
 * for managing fields with the Ultimate Fields plugin.
 *
 * @since 3.0
 */
class UI {
	/**
	 * Creates and returns an instance of the class.
	 *
	 * @since 3.0
	 *
	 * @param string $file The file of the the UI. Only needed the first time.
	 * @param bool   $autoload  Indicates whether to use the build-in autoloader.
	 * @return UI
	 */
	public static function instance( $file = '', $autoload = false ) {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self( $file, $autoload );
		}

		return $instance;
	}

	/**
	 * Construct the file and perform basic important actions.
	 *
	 * @since 3.0
	 *
	 * @param string $file The file of the plugin (ui).
	 * @param bool   $autoload  Indicates whether to use the build-in autoloader.
	 */
	protected function __construct( $file, $autoload = false ) {
		if( ! $file ) {
			$file = __DIR__;
		}

		// Define path constants
		define( 'ULTIMATE_FIELDS_UI_DIR', dirname( $file ) . '/' );
		define( 'ULTIMATE_FIELDS_UI_URL', ultimate_fields()->get_url( $file ) );

		# Add an autoloader for the UI
		if( $autoload ) {
			new Autoloader( 'Ultimate_Fields\\UI', ULTIMATE_FIELDS_UI_DIR . '/classes' );
		}

		# Initialize everything else when UF is available.
		add_filter( 'uf.register_ui', array( $this, 'register' ) );

		# Let migrations work
		Migration::instance();
	}

	/**
	 * Registers all top-level functions of the UI.
	 *
	 * @since 3.0
	 */
	public function register() {
		if( defined( 'ULTIMATE_FIELDS_DISABLE_UI' ) && ULTIMATE_FIELDS_DISABLE_UI ) {
			return;
		}

		# Add the needed scripts
		add_action( 'uf.register_scripts', array( $this, 'register_scripts' ), 9 );
		add_action( 'uf.ajax.select_ui_options', array( $this, 'generate_select_options' ), 10 );
		add_filter( 'uf.field.class', array( $this, 'generate_field_class' ), 10, 2 );

		# Add paths
		Template::instance()->add_path( ULTIMATE_FIELDS_UI_DIR . 'templates/' );

		# Register the post type
		Post_Type::instance()->hook_in();

		if( is_admin() || isset( $_REQUEST['uf_action'] ) ) {
			Container_Settings::instance();
			Settings_Page::instance();
		}

		// Register the containers
		add_action( 'uf.init', array( $this, 'register_containers' ) );

		// Redirect for the onboarding process
		add_action( 'current_screen', array( $this, 'redirect_to_welcome' ) );
	}

	/**
	 * Registers the scripts, necessary for the UI.
	 *
	 * @since 3.0
	 */
	public function register_scripts() {
		$js      = ULTIMATE_FIELDS_UI_URL . 'js/';
		$ui_deps = array( 'uf-field-repeater', 'uf-field-multiselect', 'uf-container-post-type' );
		$v       = ULTIMATE_FIELDS_VERSION;

		wp_register_script( 'uf-ui-import',                  $js . 'import.js',                  array( 'jquery', 'plupload-all' ), $v );
		wp_register_script( 'uf-ui',                         $js . 'uf-ui.js',                   $ui_deps,                          $v );
		wp_register_script( 'uf-ui-field',                   $js . 'field.js',                   array( 'uf-ui', 'uf-tab' ),        $v );
		wp_register_script( 'uf-ui-field-helpers',           $js . 'field-helpers.js',           array( 'uf-ui-field' ),            $v );
		wp_register_script( 'uf-ui-editor',                  $js . 'editor.js',                  array( 'uf-ui-field' ),            $v );
		wp_register_script( 'uf-ui-conditional-logic-field', $js . 'conditional-logic-field.js', array( 'uf-ui' ),                  $v );

		wp_register_style( 'uf-ui', ULTIMATE_FIELDS_UI_URL . 'assets/css/ultimate-fields-ui.css', array( 'ultimate-fields-css' ), $v );

		if( ! is_admin() || 'ultimate-fields' != get_current_screen()->post_type )
			return;

		// Enqueue assets for the UI
		wp_enqueue_script( 'uf-ui' );
		wp_enqueue_script( 'uf-ui-field' );
		wp_enqueue_script( 'uf-ui-field-helpers' );
		wp_enqueue_script( 'uf-ui-editor' );
		wp_enqueue_style( 'uf-ui' );

		// Force UF to wait for the logic field
		$initializer = $GLOBALS[ 'wp_scripts' ]->query( 'uf-initialize', 'registered' );
		$initializer->deps[] = 'uf-ui-conditional-logic-field';
	}

	/**
	 * Generates the class name for UI fields.
	 *
	 * @since 3.0
	 *
	 * @param mixed  $class_name A null, which will be overwritten if the class exists.
	 * @param string $type       The requested field type.
	 * @return mixed
	 */
	public function generate_field_class( $class_name, $type ) {
		static $fields;

		if( is_null( $fields ) ) {
			$fields = array(
				'fields'            => 'Ultimate_Fields\\UI\\Field\\Fields',
				'conditional_logic' => 'Ultimate_Fields\\UI\\Field\\Conditional_Logic',
				'field_selector'    => 'Ultimate_Fields\\UI\\Field\\Field_Selector',
				'fields_selector'   => 'Ultimate_Fields\\UI\\Field\\Fields_Selector',
			);
		}

		if( is_null( $class_name ) && isset( $fields[ $type ] ) ) {
			return $fields[ $type ];
		} else {
			return $class_name;
		}
	}

	/**
	 * Registers all containers, created in the back end.
	 *
	 * @since 3.0
	 */
	public function register_containers() {
		// In JSON mode containers are only loaded from JSON
		if( ultimate_fields()->is_json_enabled() ) {
			return;
		}

		$containers = get_posts(array(
			'post_type'      => Post_Type::instance()->get_slug(),
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'DESC',
				'ID'         => 'ASC'
			)
		));

		foreach( $containers as $post ) {
			$container = new Container_Helper;
			$container->import_from_post( $post );
			$container->register();
		}
	}

	/**
	 * Performs AJAX calls to load dynamic select options.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item The item that is currently being displayed.
	 */
	public function generate_select_options( $item ) {
		if( ! is_a( $item, 'WP_Post' ) || Post_Type::instance()->get_slug() != $item->post_type ) {
			return;
		}

		Select_Helper::generate_ajax_options();
	}

	/**
	 * Redirects the user to the welcome settings page when they visit the UI for the first time.
	 *
	 * @since 3.0
	 */
	public function redirect_to_welcome() {
		if( get_option( 'uf_boarding_finished' ) ) {
			return;
		}

		$slug = Post_Type::instance()->get_slug();
		if( 'edit-' . $slug != get_current_screen()->id ) {
			return;
		}

		$url = sprintf( 'edit.php?post_type=%s&page=settings&screen=about', $slug );
		wp_redirect( $url );
		add_option( 'uf_boarding_finished', true, null, 'yes' );
		exit;
	}
}
