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

class Formality_Admin {

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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-results.php';
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->formality, plugin_dir_url(__DIR__) . 'dist/styles/formality-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->formality, plugin_dir_url(__DIR__) . 'dist/scripts/formality-admin.js', array( 'jquery' ), $this->version, false );
	}
	
	public function formality_menu() {
		$icon = 'data:image/svg+xml;base64,' . base64_encode('<svg width="28px" height="18px" viewBox="0 0 28 18" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path fill="black" stroke="none" d="M9.0934082,0.630371094 L19.6065918,0.630371094 C24.1593004,0.630371094 27.85,4.32107066 27.85,8.8737793 C27.85,13.4264879 24.1593004,17.1171875 19.6065918,17.1171875 L9.0934082,17.1171875 C4.54069957,17.1171875 0.85,13.4264879 0.85,8.8737793 C0.85,4.32107066 4.54069957,0.630371094 9.0934082,0.630371094 Z M9.07128906,13.9737793 C11.8879413,13.9737793 14.1712891,11.6904315 14.1712891,8.8737793 C14.1712891,6.05712707 11.8879413,3.7737793 9.07128906,3.7737793 C6.25463684,3.7737793 3.97128906,6.05712707 3.97128906,8.8737793 C3.97128906,11.6904315 6.25463684,13.9737793 9.07128906,13.9737793 Z"></path></svg>');
		add_menu_page( 'Formality', 'Formality', 'edit_others_posts', 'formality_menu', function() { echo 'formality 123'; }, "dashicons-formality", 30 );
	}
	
	public function unique_field() {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-acf.php';
	}
	
	public function gutenberg_block() {
		if( function_exists('acf_register_block') ) {
			acf_register_block(array(
				'name'				=> 'text',
				'title'				=> __('Text'),
				'description'		=> __('A custom testimonial block.'),
				'render_callback'	=> 'acf_block_render_callback',
				'category'			=> 'formatting',
				'icon'				=> 'admin-comments',
				'keywords'			=> array( 'testimonial', 'quote' ),
			));
		}
		function acf_block_render_callback( $block ) {
			$slug = str_replace('acf/', '', $block['name']);
			if( file_exists(plugin_dir_path( dirname( __FILE__ ) ) . "public/blocks/{$slug}.php") ) {
				include( plugin_dir_path( dirname( __FILE__ ) ) . "public/blocks/{$slug}.php" );
			}
		}
	}
	
	public function meta_information( $field ) {
	  $field['message'] = '<h4 style="margin:0">Standalone version</h4>This is an independent form, that are not tied to your posts or pages, and you can visit here: ';
	  $field['message'] .= '<a target="_blank" href="' . get_permalink() . '">'.get_permalink().'</a>';
	  $field['message'] .= '<h4 style="margin-bottom:0">Embedded version</h4>But you can also embed it, into your post or pages with Formality Gutenberg block or with this specific shortcode:';
	  $field['message'] .= '<input type="text" readonly value=\'[formality id="'.get_the_ID().'"]\'>';
	  return $field;
	}
	

}
