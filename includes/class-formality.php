<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/includes
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality {

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0
   * @access   protected
   * @var      Formality_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0
   * @access   protected
   * @var      string    $formality    The string used to uniquely identify this plugin.
   */
  protected $formality;

  /**
   * The current version of the plugin.
   *
   * @since    1.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0
   */
  public function __construct() {

    $this->version = defined( 'FORMALITY_VERSION' ) ? FORMALITY_VERSION : '1.5.5';
    $this->formality = 'formality';

    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();
    $this->setup();

  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Formality_Loader. Orchestrates the hooks of the plugin.
   * - Formality_i18n. Defines internationalization functionality.
   * - Formality_Admin. Defines all hooks for the admin area.
   * - Formality_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0
   * @access   private
   */
  private function load_dependencies() {

    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-formality-loader.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-formality-setup.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-formality-i18n.php';

    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-admin.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-public.php';

    $this->loader = new Formality_Loader();

  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Formality_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0
   * @access   private
   */
  private function set_locale() {

    $plugin_i18n = new Formality_i18n();
    $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0
   * @access   private
   */
  private function define_admin_hooks() {

    $plugin_admin = new Formality_Admin( $this->get_formality(), $this->get_version() );
    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_assets' );
    $this->loader->add_action( 'admin_menu', $plugin_admin, 'formality_menu' );
    $this->loader->add_filter( 'manage_formality_form_posts_columns', $plugin_admin, 'form_columns', 99 );
    $this->loader->add_action( 'manage_formality_form_posts_custom_column', $plugin_admin, 'form_columns_data', 10, 2 );
    $this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_header');
    $this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'link_website', 10, 2 );

    $plugin_tools = new Formality_Tools( $this->get_formality(), $this->get_version() );
    $this->loader->add_action( 'admin_init', $plugin_tools, 'flush_rules');
    $this->loader->add_filter( 'post_row_actions', $plugin_tools, 'duplicate_form_link', 10, 2 );
    $this->loader->add_action( 'admin_action_formality_duplicate_form', $plugin_tools, 'duplicate_form');
    $this->loader->add_action( 'admin_action_formality_generate_sample', $plugin_tools, 'generate_sample');
    $this->loader->add_action( 'admin_action_formality_toggle_panel', $plugin_tools, 'toggle_panel');
    $this->loader->add_action( 'formality_background_download_templates', $plugin_tools, 'background_download_templates');

    $plugin_results = new Formality_Results( $this->get_formality(), $this->get_version() );
    $this->loader->add_action( 'add_meta_boxes', $plugin_results, 'metaboxes' );
    $this->loader->add_action( 'init', $plugin_results, 'unread_status');
    $this->loader->add_action( 'add_menu_classes', $plugin_results, 'unread_bubble', 99);
    $this->loader->add_action( 'admin_init', $plugin_results, 'auto_publish');
    $this->loader->add_filter( 'manage_formality_result_posts_columns', $plugin_results, 'column_id', 99 );
    $this->loader->add_action( 'manage_formality_result_posts_custom_column', $plugin_results, 'column_id_value', 10, 2 );
    $this->loader->add_action( 'admin_action_mark_as_formality_result', $plugin_results, 'mark_as');
    $this->loader->add_filter( 'post_row_actions', $plugin_results, 'mark_as_link', 10, 2 );
    $this->loader->add_action( 'admin_action_mark_all_formality_result', $plugin_results, 'mark_all_as_read' );
    $this->loader->add_action( 'restrict_manage_posts', $plugin_results, 'mark_all_as_read_link', 10, 2 );
    $this->loader->add_action( 'admin_action_export_formality_result', $plugin_results, 'export' );

    $plugin_editor = new Formality_Editor( $this->get_formality(), $this->get_version() );
    $this->loader->add_action( 'enqueue_block_editor_assets', $plugin_editor, 'enqueue_scripts' );
    $this->loader->add_action( 'init', $plugin_editor, 'register_blocks');
    $this->loader->add_filter( 'block_categories_all', $plugin_editor, 'block_categories', 99, 2);
    $this->loader->add_filter( 'allowed_block_types_all', $plugin_editor, 'filter_blocks', 99, 2);
    $this->loader->add_action( 'rest_api_init', $plugin_editor, 'register_metas' );
    $this->loader->add_filter( 'use_block_editor_for_post_type', $plugin_editor, 'prevent_classic_editor', PHP_INT_MAX, 2 );
    $this->loader->add_filter( 'gutenberg_can_edit_post_type', $plugin_editor, 'prevent_classic_editor', PHP_INT_MAX, 2 );
    $this->loader->add_action( 'rest_api_init', $plugin_editor, 'templates_endpoint' );
    $this->loader->add_action( 'current_screen', $plugin_editor, 'remove_3rdparty_styles' );

  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0
   * @access   private
   */
  private function define_public_hooks() {

    $plugin_public = new Formality_Public( $this->get_formality(), $this->get_version() );
    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_assets' );
    $this->loader->add_filter( 'the_content', $plugin_public, 'print_form', PHP_INT_MAX );
    $this->loader->add_filter( 'template_include', $plugin_public, 'page_template', PHP_INT_MAX );
    $this->loader->add_filter( 'body_class', $plugin_public, 'body_classes', 99 );
    $this->loader->add_action( 'wp_print_styles', $plugin_public, 'remove_styles', 99 );
    $this->loader->add_action( 'init', $plugin_public, 'shortcode' );

    $plugin_submit = new Formality_Submit( $this->get_formality(), $this->get_version() );
    $this->loader->add_action( 'rest_api_init', $plugin_submit, 'api_endpoints' );

    $plugin_upload = new Formality_Upload( $this->get_formality(), $this->get_version() );
    $this->loader->add_action( 'rest_api_init', $plugin_upload, 'api_endpoints' );

  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0
   * @access   private
   */
  private function setup() {

    $plugin_setup = new Formality_Setup( $this->get_formality(), $this->get_version() );
    $this->loader->add_action( 'init', $plugin_setup, 'post_types' );

  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_formality() {
    return $this->formality;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    Formality_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader() {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }

}
