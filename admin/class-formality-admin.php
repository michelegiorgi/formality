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
    wp_enqueue_style( $this->formality . "-admin", plugin_dir_url(__DIR__) . 'dist/styles/formality-admin.css', array(), $this->version, 'all' );
  }

  public function enqueue_scripts() {
    wp_enqueue_script( $this->formality . "-admin", plugin_dir_url(__DIR__) . 'dist/scripts/formality-admin.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post' ), $this->version, false );
    
    wp_localize_script( $this->formality . "-admin", 'formality', array(
      'plugin_url' => str_replace('admin/', '', plugin_dir_url( __FILE__ )),
      'admin_url' => get_admin_url()
    ));

    wp_set_script_translations( $this->formality . "-admin", 'formality', plugin_dir_path( __DIR__ ) . 'languages' );
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
    
  public function duplicate_form(){
    global $wpdb;
    if (! ( isset( $_GET['form']) || isset( $_POST['form'])  || ( isset($_REQUEST['action']) && 'duplicate_formality_form' == $_REQUEST['action'] ) ) ) {
      wp_die(__("No form to duplicate has been supplied!", "formality"));
    }
   
    if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) ) return;
   
    $post_id = (isset($_GET['form']) ? absint( $_GET['form'] ) : absint( $_POST['form'] ) );
    $post = get_post( $post_id );
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;
   
    if (isset( $post ) && $post != null) {
      $args = array(
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
        'post_author'    => $new_post_author,
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_name'      => $post->post_name,
        'post_parent'    => $post->post_parent,
        'post_password'  => $post->post_password,
        'post_status'    => 'draft',
        'post_title'     => $post->post_title,
        'post_type'      => $post->post_type,
        'to_ping'        => $post->to_ping,
        'menu_order'     => $post->menu_order
      );
   
      $new_post_id = wp_insert_post( $args );
      $taxonomies = get_object_taxonomies($post->post_type);
      foreach ($taxonomies as $taxonomy) {
        $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
        wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
      }
   
      $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
      if (count($post_meta_infos)!=0) {
        $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
        foreach ($post_meta_infos as $meta_info) {
          $meta_key = $meta_info->meta_key;
          if( $meta_key == '_wp_old_slug' ) continue;
          $meta_value = addslashes($meta_info->meta_value);
          $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
        }
        $sql_query.= implode(" UNION ALL ", $sql_query_sel);
        $wpdb->query($sql_query);
      }
      wp_redirect( admin_url('edit.php?post_type=formality_form') ); 
      exit;
    } else {
      wp_die(__("Form duplication failed, could not find original form: ", "formality") . $post_id);
    }
  }

  public function duplicate_form_link($actions, $post) {
    if (current_user_can('edit_posts') && $post->post_type=='formality_form') {
      $link = wp_nonce_url('admin.php?action=duplicate_formality_form&form=' . $post->ID, basename(__FILE__), 'duplicate_nonce' );
      $actions['duplicate'] = '<a href="'.$link.'" title="'.__("Duplicate this form", "formality").'" rel="permalink">'.__("Duplicate", "formality").'</a>';
    }
    return $actions;
  }

}
