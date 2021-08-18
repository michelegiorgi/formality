<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/includes
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Setup {

  /**
   * The ID of this plugin.
   *
   * @since    1.0
   * @access   private
   * @var      string    $formality    The ID of this plugin.
   */
  private $formality;

  /**
   * The version of this plugin.
   *
   * @since    1.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0
   * @param      string    $formality       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0
   */
  public function post_types() {
    $form_labels = array(
      'name'               => _x( 'Forms', 'post type general name', 'formality' ),
      'singular_name'      => _x( 'Form', 'post type singular name', 'formality' ),
      'menu_name'          => _x( 'Forms', 'admin menu', 'formality' ),
      'name_admin_bar'     => _x( 'Form', 'add new on admin bar', 'formality' ),
      'add_new'            => _x( 'Add New', 'form', 'formality' ),
      'add_new_item'       => __( 'Add New Form', 'formality' ),
      'new_item'           => __( 'New Form', 'formality' ),
      'edit_item'          => __( 'Edit Form', 'formality' ),
      'view_item'          => __( 'View Form', 'formality' ),
      'all_items'          => __( 'Forms', 'formality' ),
      'search_items'       => __( 'Search Forms', 'formality' ),
      'parent_item_colon'  => __( 'Parent Forms:', 'formality' ),
      'not_found'          => __( 'No forms found.', 'formality' ),
      'not_found_in_trash' => __( 'No forms found in Trash.', 'formality' )
    );
    register_post_type('formality_form',
      array(
        'labels' => $form_labels,
        'rewrite' => array('slug' => 'form'),
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'show_ui' => true,
        'supports' => array( 'title', 'author', 'editor', 'custom-fields' ),
        'show_in_menu' => 'formality_menu',
        'show_in_nav_menus' => true,
      )
    );
    $result_labels = array(
      'name'               => _x( 'Results', 'post type general name', 'formality' ),
      'singular_name'      => _x( 'Result', 'post type singular name', 'formality' ),
      'menu_name'          => _x( 'Results', 'admin menu', 'formality' ),
      'name_admin_bar'     => _x( 'Result', 'add new on admin bar', 'formality' ),
      'add_new'            => _x( 'Add New', 'result', 'formality' ),
      'add_new_item'       => __( 'Add New Result', 'formality' ),
      'new_item'           => __( 'New Result', 'formality' ),
      'edit_item'          => __( 'Edit Result', 'formality' ),
      'view_item'          => __( 'View Result', 'formality' ),
      'all_items'          => __( 'Results', 'formality' ),
      'search_items'       => __( 'Search Results', 'formality' ),
      'parent_item_colon'  => __( 'Parent Results:', 'formality' ),
      'not_found'          => __( 'No results found.', 'formality' ),
      'not_found_in_trash' => __( 'No results found in Trash.', 'formality' )
    );
    register_post_type('formality_result',
      array(
        'labels' => $result_labels,
        'supports' => array('title', 'author'),
        'public' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'has_archive' => true,
        'capability_type' => 'post',
        'capabilities' => array(
          'create_posts' => 'do_not_allow',
        ),
        'map_meta_cap' => true,
        'show_ui' => true,
        'show_in_menu' => 'formality_menu'
      )
    );
    register_taxonomy('formality_tax', 'formality_result',
      array(
        'label' => __( 'Form' ),
        'hierarchical' => true,
        'capabilities'      => array(
          'assign_terms' => 'god',
          'edit_terms'   => 'god',
          'manage_terms' => 'god',
        ),
        'show_in_rest' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => false
      )
    );
  }
}
