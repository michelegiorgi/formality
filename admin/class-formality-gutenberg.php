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
  				'title' => __( 'Fields', 'formality'),
  			),
  		),
  		$categories
  	);
  }
  
  public function filter_blocks($allowed_block_types, $post) {
    $formality_blocks = array(
      'formality/text',
      'formality/textarea',
      'formality/email',
      'formality/select',
      'formality/step',
      'formality/message'
      //'core/paragraph'
    );
    if ( $post->post_type !== 'formality_form' ) {
      return $allowed_block_types;
    }
    return $formality_blocks;
  }
  
  public function form_info_metabox() {
    add_meta_box(
      'formality_gutenberg_meta_box',
      __( 'Information', 'formality' ),
      function() { _e( '<h4 style="margin-bottom:0.3em; opacity:0.6">Standalone version</h4>This is an independent form, that are not tied to your posts or pages, and you can visit here: <a class="formality-admin-info-permalink" target="_blank" href=""></a><h4 style="margin-bottom:0.3em; opacity:0.6">Embedded version</h4>But you can also embed it, into your post or pages with Formality Gutenberg block or with this specific shortcode:<input class="formality-admin-info-shortcode" type="text" readonly value=""><br><br>', 'Formality' ); },
      'formality_form',
      'side'//,
      //array( '__back_compat_meta_box' => false )
    );
  }
  
  public function rest_api() {
    $fields = array(
      '_formality_type',
      '_formality_color1',
      '_formality_color2',
      '_formality_fontsize',
      '_formality_logo',
      '_formality_logo_id',
      '_formality_bg',
      '_formality_bg_id',
      '_formality_overlay_opacity'
    );
    foreach($fields as $field) {
      register_meta(
        'post', $field,
        array(
          'object_subtype' => 'formality_form',
          'show_in_rest' => true,
          'single' => true,
          'type' => 'string'
        )
      );
    }
    register_rest_route( 'formality/v1', '/options', array(
      'methods'  => 'POST',
      'callback' => [$this, 'form_meta_update'],
      'args'	 => array(
				'id' => array( 'sanitize_callback' => 'absint', ),
			),
    ));
  }
  
  public function form_meta_update( $data ) {
    //$key = $data['key'];
    $keys = array_unique($data['keys']);
    $return = false;
  	foreach($keys as $key) {
  	  $return = update_post_meta( $data['id'], $key, $data[$key] );
    }
    return $return;
  }
  
}
