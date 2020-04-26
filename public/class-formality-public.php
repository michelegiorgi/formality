<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://formality.dev
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/public
 */

class Formality_Public {

	private $formality;
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
		
		$this->load_dependencies();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-form.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-submit.php';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->formality . "-public", plugin_dir_url(__DIR__) . 'dist/styles/formality-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->formality . "-public", plugin_dir_url(__DIR__) . 'dist/scripts/formality-public.js', array( 'jquery', 'wp-i18n' ), $this->version, false );
		
		wp_localize_script($this->formality . "-public", 'formality', array(
		  'ajax' => admin_url('admin-ajax.php'),
		  'api' => esc_url_raw(rest_url()),
		  'action_nonce' => wp_create_nonce('formality_async'),
		  'login_nonce' => wp_create_nonce('wp_rest')
		));
		
		wp_set_script_translations( $this->formality . "-public", 'formality', plugin_dir_path( __DIR__ ) . 'languages' );
	}

	/**
	 * Replace standard content with form markup
	 *
	 * @since    1.0.0
	 */	
	public function print_form($content) {
		if (get_post_type()=='formality_form') {
			$form = new Formality_Form($this->formality, $this->version);
      $content = $form->print();
		}
    return $content;    
	}
	
	public function shortcode() {
		add_shortcode( 'formality', function($atts) {
			$content = "";
			if($atts['id']) {
				$args = array( 'post_type' => 'formality_form', 'p' => $atts['id'] );
				$query = new WP_Query($args);
				$attributes = array();
				$attributes['include_bg'] = isset($atts['remove_bg']) ? false : true;
      	$attributes['sidebar'] = isset($atts['is_sidebar']) ? true : false;
      	$attributes['hide_title'] = isset($atts['hide_title']) ? true : false;
      	$attributes['invert_colors'] = isset($atts['invert_colors']) ? true : false;
      	$attributes['cta_label'] = isset($atts['cta_label']) ? $atts['cta_label'] : __("Call to action", "formality");
      	$attributes['align'] = isset($atts['align']) ? $atts['align'] : 'left';
				while ( $query->have_posts() ) : $query->the_post();
					global $post;
  				$form = new Formality_Form($this->formality, $this->version);
					$content = $form->print(true, $attributes);
				endwhile;
				wp_reset_query();
				wp_reset_postdata();
			}
			return $content;
		});
	}
	
	public function page_template( $template ) {	
		if ( is_single() && (get_post_type()=='formality_form') ) {
			$file_name = 'single-formality_form.php';
      if ( locate_template( $file_name ) ) {
        $template = locate_template( $file_name );
      } else {
        $template = dirname( __FILE__ ) . '/templates/single.php';
      }
		}
		return $template;
	}
	
}
