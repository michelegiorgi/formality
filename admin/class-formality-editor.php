<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://formality.dev
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/admin
 */

class Formality_Editor {

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
    
    register_block_type('formality/widget', array(
      //'editor_script' => 'formality_blocks-js',
      'render_callback' => array( $this, 'formality_widget_block_handler'),
      'attributes' => [
        'id' => [ 'default' => 0, 'type' => 'integer' ],
        'align' => [ 'default' => 'left', 'type' => 'string' ], 
        'remove_bg' => [ 'default' => false, 'type' => 'boolean'],
        'is_sidebar' => [ 'default' => false, 'type' => 'boolean'],
        'hide_title' => [ 'default' => false, 'type' => 'boolean'],
        'invert_colors' => [ 'default' => false, 'type' => 'boolean'],
        'disable_button' => [ 'default' => false, 'type' => 'boolean'],
        'preview' => [ 'default' => false, 'type' => 'boolean'],
        'cta_label' => [ 'default' => __('Call to action', 'formality'), 'type' => 'string'],
      ]
    ));

  }

  public function enqueue_scripts() {
    wp_enqueue_script( $this->formality . "-editor", plugin_dir_url(__DIR__) . 'dist/scripts/formality-editor.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post' ), $this->version, false );
    
    wp_localize_script( $this->formality . "-editor", 'formality', array(
      'plugin_url' => str_replace('admin/', '', plugin_dir_url( __FILE__ )),
      'admin_url' => get_admin_url()
    ));

    wp_set_script_translations( $this->formality . "-editor", 'formality', plugin_dir_path( __DIR__ ) . 'languages' );
  }

  public function formality_widget_block_handler($atts) {
    if(isset($atts['id']) && $atts['id']) {
      $shortcode_attr = '';
      foreach ($atts as $key => $value) {
        if($value) { $shortcode_attr .= ' ' . $key . '="' . $value . '"'; }
      }
      return do_shortcode('[formality'.$shortcode_attr.']');
    }
    return '';
  }

  public function block_categories($categories, $post) {
    return array_merge(
      array(
        array(
          'slug' => 'formality',
          'title' => __( 'Input fields', 'formality'),
        ),
      ),
      array(
        array(
          'slug' => 'formality_nav',
          'title' => __( 'Layout elements', 'formality'),
        ),
      ),
      $categories
    );
  }
  
  public function filter_blocks($allowed_block_types, $post) {
    $formality_blocks = $this->get_allowed('blocks');
    if ( $post->post_type !== 'formality_form' ) {
      return $allowed_block_types;
    }
    return $formality_blocks;
  }
    
  public function rest_api() {
    $fields = $this->get_allowed('metas');
    foreach($fields as $field => $type) {
      register_meta(
        'post', $field,
        array(
          'object_subtype' => 'formality_form',
          'show_in_rest' => true,
          'single' => true,
          'type' => $type,
          'sanitize_callback' => 'sanitize_text_field',
          'auth_callback' => function() { 
            return current_user_can('edit_posts');
          }
        )
      );
    }
  }
  
  public function get_allowed( $type = 'blocks' ) {
    if($type=="blocks") {
      $return = array(
        'formality/text',
        'formality/textarea',
        'formality/email',
        'formality/select',
        'formality/number',
        'formality/switch',
        'formality/multiple',
        'formality/rating',
        'formality/step',
        'formality/message',
        'formality/media',
      );
    } else if($type=="metas") {
      $return = array(
        '_formality_type' => 'string',
        '_formality_style' => 'string',
        '_formality_color1' => 'string',
        '_formality_color2' => 'string',
        '_formality_color3' => 'string',
        '_formality_fontsize' => 'integer',
        '_formality_logo' => 'string',
        '_formality_logo_id' => 'integer',
        '_formality_logo_height' => 'integer',
        '_formality_bg' => 'string',
        '_formality_bg_id' => 'integer',
        '_formality_overlay_opacity' => 'integer',
        '_formality_template' => 'string',
        '_formality_position' => 'string',
        '_formality_credits' => 'string',
        '_formality_credits_url' => 'string',
        '_formality_enable_credits' => 'boolean',
        '_formality_custom_credits' => 'string',
        '_formality_thankyou' => 'string',   
        '_formality_thankyou_message' => 'string',   
        '_formality_error' => 'string',   
        '_formality_error_message' => 'string',   
        '_formality_email' => 'string',
        '_formality_send_text' => 'string',
      );
    };
    return $return;
  }
  
  public function gutenberg_version_class($classes) {
    $ver = 0;
    if(defined('GUTENBERG_VERSION')) { 
      if(version_compare( GUTENBERG_VERSION, '8.0', '<' )){
        $ver = 7;
      }
    } else if ( version_compare( $GLOBALS['wp_version'], '5.5', '<' ) ) {
      $ver = 7;
    }
    if($ver) { $classes .= ' formality--gutenberg--' . $ver . ' '; }
    return $classes;
  }
  
  public function prevent_classic_editor($can_edit, $post) {
    if ('formality_form' === $post) return true;
    return $can_edit;
  }
  
}