<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/admin
 */

class Formality_Gutenberg {

	private $formality;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $formality       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $formality, $version ) {
		$this->formality = $formality;
		$this->version = $version;
	}

	public function register_blocks() {

  	wp_register_script(
  		'formality_blocks-js',
  		plugin_dir_url(__DIR__) . 'dist/gutenberg/blocks.build.js',
  		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post' ),
  		null
  	);
  
  	register_block_type(
  	  'formality/formality-blocks',
  	  array( 'editor_script' => 'formality_blocks-js', )
    );

	}
	
	public function block_categories($categories, $post) {
  	return array_merge(
  	  array(
  			array(
  				'slug' => 'formality',
  				'title' => __( 'Formality blocks', 'formality'),
  			),
  		),
  		$categories
  	);
  }
  
  public function filter_blocks($allowed_block_types, $post) {
    $formality_blocks = array(
      'formality/text',
      //'core/paragraph'
    );
    if ( $post->post_type !== 'formality_form' ) {
      return $allowed_block_types;
    }
    return $formality_blocks;
  }
  


}
