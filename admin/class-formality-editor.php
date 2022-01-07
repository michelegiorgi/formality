<?php

/**
 * The editor-specific functionality of the plugin.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/admin
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Editor {

  private $formality;
  private $version;

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
        'has_copied' => [ 'default' => false, 'type' => 'boolean'],
      ]
    ));
  }

  public function enqueue_scripts() {
    global $pagenow;
    $editor = get_post_type() == 'formality_form' ? 'formality' : str_replace(".php", "", $pagenow);
    $upload = wp_upload_dir();
    $formats = array();
    $mimes = get_allowed_mime_types();
    $maxsize = wp_max_upload_size() / 1048576;
    if(!empty($mimes)) {
      foreach ($mimes as $type => $mime) {
        $multiple = explode("|", $type);
        foreach ($multiple as $single) { $formats[] = $single; }
      }
    }
    $dependecies = array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-dom-ready', 'wp-components');
    $dependecies[] = $editor == 'widgets' ? 'wp-edit-widgets' : 'wp-edit-post';
    wp_enqueue_script( $this->formality . "-editor", plugin_dir_url(__DIR__) . 'dist/scripts/formality-editor.js', $dependecies, $this->version, false );
    wp_localize_script( $this->formality . "-editor", 'formality', array(
      'plugin_url' => str_replace('admin/', '', plugin_dir_url( __FILE__ )),
      'templates_url' => $upload['baseurl'] . '/formality/templates',
      'templates_count' => get_option('formality_templates', 0),
      'admin_url' => get_admin_url(),
      'api' => esc_url_raw(rest_url()),
      'nonce' => wp_create_nonce('wp_rest'),
      'upload_formats' => $formats,
      'upload_max' => $maxsize,
      'editor' => $editor
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

  public function filter_blocks($allowed_block_types, $editorcontext) {
    $post = property_exists($editorcontext, 'post') ? $editorcontext->post : $editorcontext;
    if(!empty($post) && $post->post_type == 'formality_form') { return $this->get_allowed('blocks'); }
    return $allowed_block_types;
  }

  public function register_metas() {
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
        'formality/upload',
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
        '_formality_border_radius' => 'integer',
        '_formality_bg' => 'string',
        '_formality_bg_id' => 'integer',
        '_formality_bg_layout' => 'string',
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

  public function templates_endpoint() {
    register_rest_route( 'formality/v1', '/templates/download/', array(
      'methods'  => 'POST',
      'callback' => [$this, 'download_templates'],
      'permission_callback' => function () { return current_user_can( 'edit_others_posts' ); }
    ));
    register_rest_route( 'formality/v1', '/templates/count/', array(
      'methods'  => 'GET',
      'callback' => [$this, 'count_templates'],
      'permission_callback' => function () { return current_user_can( 'edit_others_posts' ); }
    ));
  }

  public function count_templates() {
    return get_option('formality_templates', 0);
  }

  public function download_templates() {
    update_option('formality_templates', 0, 'yes');
    $disable_ssl = isset($_POST['disableSSL']) && $_POST['disableSSL']=="1";
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $response['status'] = 200;
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'] . '/formality/templates';
    if(! is_dir($upload_dir)) { wp_mkdir_p( $upload_dir ); }

    $templates_path = plugin_dir_path(__DIR__) . "dist/images/templates.json";
    ob_start();
    include($templates_path);
    $templates_file = ob_get_contents();
    ob_end_clean();

    if($templates_file) {
      $templates = json_decode($templates_file, true);
      $images = array();
      if(is_array($templates)) {
        foreach($templates as $template) {
          if(isset($template['bg']) && $template['bg'] !== 'none') {
            $images[] = $template['bg'];
          }
        }
      }
      $count = 1;
      foreach($images as $key => $image) {
        $path = $upload_dir . '/' . $image . '.jpg';
        $count++;
        if(!file_exists($path)) {
          $endpoint = 'https://source.unsplash.com/' . $image . '/1800x1200';
          if($disable_ssl) { add_filter('https_ssl_verify', [$this, 'unsplash_disable_ssl'], 11, 2); }
          $temp = download_url($endpoint);
          if($disable_ssl) { remove_filter('https_ssl_verify', [$this, 'unsplash_disable_ssl'], 11, 2); }
          if(is_wp_error($temp)) {
            error_log('Formality - ' . $temp->get_error_message());
            if(strpos(strtolower($temp->get_error_message()), 'ssl') !== false) {
              $response['status'] = 501;
              $count = 1;
            } else {
              $response['status'] = 500;
            }
            break;
          } else if(wp_get_image_mime($temp) !== "image/jpeg") {
            error_log('Formality - Mime error');
            $response['status'] = 500;
            break;
          }
          $size = function_exists('getimagesize') ? getimagesize($temp) : array(1800);
          if(isset($size[0]) && $size[0]==1800){
            copy($temp, $path);
            $editor = wp_get_image_editor($path);
            if(!is_wp_error($editor) ) {
              $editor->resize(300, 300, true);
              $editor->save(str_replace('.jpg', '_thumb.jpg', $path));
            } else {
              error_log('Formality - ' . $editor->get_error_message());
            }
            update_option('formality_templates', $count, 'yes');
          }
          @unlink($temp);
        }
      }
    }
    $response['count'] = $count;
    update_option('formality_templates', $count, 'yes');
    return $response;
  }

  public function unsplash_disable_ssl($ssl_verify, $url) {
    return substr($url, 0, 27) === 'https://source.unsplash.com' ? false : true;
  }

  public function prevent_classic_editor($can_edit, $post) {
    return 'formality_form' === $post ? true : $can_edit;
  }

  public function remove_3rdparty_styles($screen) {
    if(property_exists($screen, 'post_type') && 'formality_form' == $screen->post_type) {
      remove_editor_styles();
    }
  }
}
