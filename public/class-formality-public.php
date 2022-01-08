<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/public
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Public {

  private $formality;
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0
   */
  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;

    $this->load_dependencies();
  }

  private function load_dependencies() {
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-form.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-fields.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-submit.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-upload.php';
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0
   */
  public function enqueue_assets() {
    $isform = is_singular('formality_form');
    wp_register_style( $this->formality . "-public", plugin_dir_url(__DIR__) . 'dist/styles/formality-public.css', array(), $this->version, 'all' );
    wp_register_script( $this->formality . "-public", plugin_dir_url(__DIR__) . 'dist/scripts/formality-public.js', array( 'wp-i18n' ), $this->version, !$isform );
    wp_localize_script($this->formality . "-public", 'formality', array(
      'ajax' => admin_url('admin-ajax.php'),
      'api' => esc_url_raw(rest_url()),
      'action_nonce' => wp_create_nonce('formality_async'),
      'login_nonce' => wp_create_nonce('wp_rest')
    ));
    wp_set_script_translations( $this->formality . "-public", 'formality', plugin_dir_path( __DIR__ ) . 'languages' );
    if($isform) {
      wp_enqueue_style( $this->formality . "-public");
      wp_enqueue_script( $this->formality . "-public");
    }
  }

  /**
   * Replace standard content with form markup
   *
   * @since    1.0
   */
  public function print_form($content) {
    if( get_post_type() == 'formality_form' ) {
      $form = new Formality_Form($this->formality, $this->version);
      $content = $form->print();
    }
    return $content;
  }

  /**
   * Formality form shortcode
   *
   * @since    1.0
   */
  public function shortcode() {
    add_shortcode( 'formality', function($atts) {
      $content = "";
      if(isset($atts['id']) && $atts['id']) {
        $args = array(
          'post_type' => 'formality_form',
          'posts_per_page' => 1,
          'p' => $atts['id']
        );
        $query = new WP_Query($args);
        if($query->have_posts()) {
          $attributes = array();
          $attributes['include_bg'] = isset($atts['remove_bg']) ? (!filter_var($atts['remove_bg'], FILTER_VALIDATE_BOOLEAN)) : true;
          $attributes['sidebar'] = isset($atts['is_sidebar']) ? filter_var($atts['is_sidebar'], FILTER_VALIDATE_BOOLEAN) : false;
          $attributes['hide_title'] = isset($atts['hide_title']) ? filter_var($atts['hide_title'], FILTER_VALIDATE_BOOLEAN) : false;
          $attributes['invert_colors'] = isset($atts['invert_colors']) ? filter_var($atts['invert_colors'], FILTER_VALIDATE_BOOLEAN) : false;
          $attributes['disable_button'] = isset($atts['disable_button']) ? filter_var($atts['disable_button'], FILTER_VALIDATE_BOOLEAN) : false;
          $attributes['cta_label'] = isset($atts['cta_label']) ? $atts['cta_label'] : __("Call to action", "formality");
          $attributes['align'] = isset($atts['align']) ? $atts['align'] : 'left';
          while ( $query->have_posts() ) : $query->the_post();
            global $post;
            $form = new Formality_Form($this->formality, $this->version);
            $content = $form->print(true, $attributes);
          endwhile;
          wp_reset_query();
          wp_reset_postdata();
          wp_enqueue_style( $this->formality . "-public");
          wp_enqueue_script( $this->formality . "-public");
        }
      }
      return $content;
    });
  }

  /**
   * Link plugin templates to formality_form cpt
   *
   * @since    1.0
   */
  public function page_template( $template ) {
    if(is_singular('formality_form')) {
      $file_name = 'formality-form.php';
      $template = locate_template( $file_name ) ? locate_template( $file_name ) : dirname( __FILE__ ) . '/templates/' . $file_name;
      $template = apply_filters('formality_form_template', $template);
    }
    return $template;
  }

  /**
   * Add formality body classes
   *
   * @since    1.0
   */
  public function body_classes( $body_classes ) {
    if(is_singular('formality_form')) {
      $layout_class = get_post_meta(get_the_ID(), '_formality_bg_layout', true);
      $body_classes[] = 'body-formality';
      $body_classes[] = 'body-formality--' . ($layout_class ? $layout_class : 'standard');
      $body_classes = apply_filters('formality_form_body_classes', $body_classes);
    }
    return $body_classes;
  }

  /**
   * Remove any other styles from single form template
   * You can enqueue a custom stylesheet using a handle that contain the substring "formality"
   *
   * @since    1.0.1
   */
  public function remove_styles() {
    if(is_singular('formality_form')) {
      global $wp_styles;
      $queue = $wp_styles->queue;
      $clean_queue = array();
      if(is_array($queue)) {
        foreach($queue as $key => $style) {
          if(strpos($style, 'formality') !== false) {
            $clean_queue[] = $style;
          }
        }
      }
      $wp_styles->queue = $clean_queue;
    }
  }

}
