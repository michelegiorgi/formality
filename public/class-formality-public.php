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
		wp_enqueue_style( $this->formality, plugin_dir_url(__DIR__) . 'dist/styles/formality-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->formality, plugin_dir_url(__DIR__) . 'dist/scripts/formality-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->formality, 'formality', array('ajax' => admin_url('admin-ajax.php'), 'api' => esc_url_raw(rest_url()), 'nonce' => wp_create_nonce('formality_async')));
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
			$form = "";
			if($atts['id']) {
				$args = array( 'post_type' => 'formality_form', 'p' => $atts['id'] );
				$query = new WP_Query($args);
				$form = new Formality_Form($this->formality, $this->version);
				while ( $query->have_posts() ) : $query->the_post();
					global $post;
					$form = $form->print(true);
				endwhile;
				wp_reset_query();
				wp_reset_postdata();
			}
			return $form;
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
