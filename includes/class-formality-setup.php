<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Formality
 * @subpackage Formality/public
 * @author     Your Name <email@example.com>
 */
class Formality_Setup {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $formality    The ID of this plugin.
	 */
	private $formality;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
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
	 * @since    1.0.0
	 */
	public function post_types() {
		
		register_post_type('formality_form',
	    array(
	      'labels' => array(
	        'name' => __( 'Forms' ),
	        'singular_name' => __( 'Form' )
	      ),
	      'rewrite' => array('slug' => 'form'),
	      'public' => true,
	      'show_in_rest' => true,
	      'has_archive' => true,
	      'show_ui' => true,
	      'supports' => array( 'title', 'author', 'editor', 'custom-fields' ),
				'show_in_menu' => 'formality_menu',
	    )
	  );
	  
	  register_post_type('formality_result',
	    array(
	      'labels' => array(
	        'name' => __( 'Results' ),
	        'singular_name' => __( 'Result' )
	      ),
	      'supports' => array('title'),
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
					'assign_terms' => 'manage_options',
					'edit_terms'   => 'god',
					'manage_terms' => 'god',
				),
				'show_in_rest' => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => false
			)
		);

	  register_taxonomy('formality_meta', 'formality_form',
			array(
				'label' => __( 'Form option' ),
				'hierarchical' => true,
				'capabilities'      => array(
					'assign_terms' => 'manage_options',
					'edit_terms'   => 'god',
					'manage_terms' => 'god',
				),
				'show_in_rest' => true,
				'show_in_nav_menus' => false
			)
		);
		
	}
	
	public function uf_init() {
  	
  	if( function_exists( 'ultimate_fields' ) ) { return; }
  	
  	define( 'ULTIMATE_FIELDS_PLUGIN_FILE', __FILE__ );
  	define( 'ULTIMATE_FIELDS_LANGUAGES_DIR', basename( __DIR__ ) . '/languages/' );

    require_once( plugin_dir_path( __FILE__ ) . 'vendor/ultimate-fields/core/ultimate-fields.php' );
    require_once( plugin_dir_path( __FILE__ ) . 'vendor/ultimate-fields/ui/ultimate-fields-ui.php' );
	
	}
	
	public function uf_admin() {
  	if(!is_plugin_active( 'ultimate-fields/ultimate-fields.php' )) {
      remove_menu_page('edit.php?post_type=ultimate-fields');
    } 
	}

}
