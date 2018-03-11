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
	      'has_archive' => true,
	    )
	  );
	  register_post_type('formality_result',
	    array(
	      'labels' => array(
	        'name' => __( 'Results' ),
	        'singular_name' => __( 'Result' )
	      ),
	      'public' => true,
	      'exclude_from_search' => true,
	      'publicly_queryable' => false,
	      'has_archive' => true,
	    )
	  );
		
	}
	
	public function acf() {
		
	}


}
