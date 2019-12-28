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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-notifications.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-gutenberg.php';
	}
	
	public function flush_rules(){
  	if(get_option('formality_flush')) {
      flush_rewrite_rules();
      delete_option('formality_flush');
    }
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->formality, plugin_dir_url(__DIR__) . 'dist/styles/formality-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->formality, plugin_dir_url(__DIR__) . 'dist/scripts/formality-admin.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post' ), $this->version, false );
		
		wp_localize_script($this->formality, 'formality', array(
		  'plugin_url' => str_replace('admin/', '', plugin_dir_url( __FILE__ )),
		  'admin_url' => get_admin_url()
		));
	}
	
	public function formality_menu() {
		add_menu_page( 'Formality', 'Formality', 'edit_others_posts', 'formality_menu', function() { echo 'Formality'; }, "dashicons-formality", 30 );
	}
	
	public function column_results($columns) {
  	$new = array();
    foreach($columns as $key=>$value) {
      if($key=='date') {
        $new['type'] = __('Form type', 'formality');
        $new['results'] = __('Results', 'formality');
      }    
      $new[$key]=$value;
    }  
    return $new;
	}
	
	public function column_results_row( $column, $post_id ) {
    if ($column == 'results'){ 
      $term = get_term_by("slug", "form_" . $post_id, 'formality_tax');
      if($term) {
        $counter = $term->count;
        echo '<a href="' . get_admin_url() . 'edit.php?post_type=formality_result&formality_tax=form_' . $post_id . '">' . $counter . " " . __("results", "formality") . '</a>';
      } else {
        echo __("No results", "formality");
      }
    } else if ($column == 'type'){
      $type = get_post_meta($post_id, "_formality_type");
      if(isset($type[0]) && $type[0]=="conversational") {
        echo __("Conversational", "formality");
      } else {
        echo __("Standard", "formality");
      }
    }
  }
  

}
