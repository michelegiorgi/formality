<?php

/**
 * Helper functions and tools of the plugin.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/admin
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Tools {

  private $formality;
  private $version;

  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
  }

  public function flush_rules(){
    global $pagenow, $typenow;
    if ('edit.php' === $pagenow && strpos($typenow, 'formality_') !== false) {
      if(get_option('formality_flush')) {
        flush_rewrite_rules();
        delete_option('formality_flush');
      }
    }
  }

  public function duplicate_form(){
    $notice = "";
    if (! ( isset( $_GET['form']) || ( isset($_REQUEST['action']) && 'formality_duplicate_form' == $_REQUEST['action'] ) ) ) {
      wp_die(__("No form to duplicate has been supplied!", "formality"));
    }

    if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) ) return;

    $post_id = isset($_GET['form']) ? absint( $_GET['form'] ) : 0;
    $post = get_post( $post_id );
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;
    $metas = get_post_meta($post_id, false, true);
    $metas = array_combine(array_keys($metas), array_column($metas, '0'));

    if (isset( $post ) && $post != null) {
      $args = array(
        'post_title'     => $post->post_title,
        'post_author'    => $new_post_author,
        'post_content'   => wp_slash($post->post_content),
        'post_name'      => $post->post_name,
        'post_password'  => $post->post_password,
        'post_status'    => 'draft',
        'post_type'      => 'formality_form',
        'menu_order'     => $post->menu_order,
        'meta_input'     => $metas,
      );
      $new_post_id = wp_insert_post( $args );
      $notice = ['success', __("Your form has been successfully duplicated.", "formality") ];
    } else {
      $notice = ['error', __("Form duplication failed.", "formality") ];
    }
    if($notice) { add_option( 'formality_notice', $notice, '', 'yes' ); }
    wp_redirect( admin_url('edit.php?post_type=formality_form&formality_task') );
    exit;
  }

  public function duplicate_form_link($actions, $post) {
    if (current_user_can('edit_posts') && $post->post_type=='formality_form') {
      $link = wp_nonce_url('admin.php?action=formality_duplicate_form&form=' . $post->ID, basename(__FILE__), 'duplicate_nonce' );
      $actions['duplicate'] = '<a href="'.$link.'" title="'.__("Duplicate this form", "formality").'" rel="permalink">'.__("Duplicate", "formality").'</a>';
    }
    return $actions;
  }

  public function generate_sample() {
    $notice = ['error', __("Sample import failed.", "formality")];

    if (! ( isset( $_GET['sample']) || ( isset($_REQUEST['action']) && 'formality_generate_sample' == $_REQUEST['action'] ) ) ) {
      wp_die(__("No sample to import has been supplied!", "formality"));
    }

    if ( !isset( $_GET['sample_nonce'] ) || !wp_verify_nonce( $_GET['sample_nonce'], basename( __FILE__ ) ) ) return;
    $sample = isset($_GET['sample']) ? sanitize_key($_GET['sample']) : '';

    if(function_exists('fetch_feed')){
      $uri = plugin_dir_url(__DIR__) . "public/samples/data.xml";
      add_filter('wp_feed_cache_transient_lifetime', function() { return 10; });
      $feed = fetch_feed($uri);
      remove_filter('wp_feed_cache_transient_lifetime', function() { return 10; });
      $namespace = 'http://wordpress.org/export/1.2/';
    }

    $plugin_editor = new Formality_Editor( $this->formality, $this->version );
    $allowed_metas = $plugin_editor->get_allowed('metas');
    if($sample=="all") {
      $allowed_samples = [ "purple-link", "falling-softly" ];
    } else if($sample=="default") {
      $allowed_samples = [ "purple-link", "falling-softly" ];
    } else {
      $allowed_samples = [ $sample ];
      $title = $sample;
    }

    $upload = wp_upload_dir();
    $upload_dir = $upload['baseurl'] . '/formality/';

    if($feed && !is_wp_error($feed)) {
      foreach ($feed->get_items() as $item){
        $itemetas = $item->get_item_tags($namespace, 'postmeta');
        $itemname = $item->get_item_tags($namespace, 'post_name');
        $itemname = isset($itemname[0]['data']) ? $itemname[0]['data'] : "";
        if(in_array($itemname, $allowed_samples)) {
          $title = $item->get_title();
          $content = $item->get_content();
          $metas = [];
          foreach($itemetas as $itemeta) {
            $itemeta = isset($itemeta['child'][$namespace]) ? $itemeta['child'][$namespace] : [];
            if(count($itemeta)) {
              $metakey = $itemeta['meta_key'][0]['data'];
              $metavalue = $itemeta['meta_value'][0]['data'];
              $metavalue = str_replace("%%FORMALITY_UPLOADS%%", $upload_dir, $metavalue);
              if($metakey && $metavalue && isset($allowed_metas[$metakey])) {
                $metas[$metakey] = $metavalue;
              }
            }
          }
          $post = array(
            'post_title' => $title,
            'post_content' => wp_slash($content),
            'post_type' => 'formality_form',
            'post_status' => 'draft',
            'meta_input' => $metas
          );
          wp_insert_post( $post );
        }
      }
      $notice = ['success', count($allowed_samples) > 1 ? /* translators: %s: sample name */ __("All samples have been successfully imported.", "formality") : sprintf( __("Sample '%s' has been successfully imported.", "formality"), $title ) ];

      if(!get_option('formality_templates', 0)) {
        wp_schedule_single_event(time(), 'formality_background_download_templates');
        spawn_cron();
        $notice[1] = $notice[1] . ' ' . __("All the template photos will be downloaded in the background in a few seconds.", "formality");
      }
    }

    if($notice) { add_option( 'formality_notice', $notice, '', 'yes' ); }
    wp_redirect( admin_url('edit.php?post_type=formality_form&formality_task') );
    exit;
  }

  public function generate_sample_link_url($sample="default") {
    $link = wp_nonce_url('admin.php?action=formality_generate_sample&sample='.$sample, basename(__FILE__), 'sample_nonce' );
    return $link;
  }

  public function toggle_panel(){
    if ( !isset( $_GET['panel_nonce'] ) || !wp_verify_nonce( $_GET['panel_nonce'], basename( __FILE__ ) ) ) return;
    $status = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
    if($status=="toggle") {
      if(get_option('formality_welcome')) {
        delete_option( 'formality_welcome');
      } else {
        add_option( 'formality_welcome', 1, '', 'yes' );
      }
    } else if($status) {
      add_option( 'formality_welcome', 1, '', 'yes' );
    } else {
      delete_option( 'formality_welcome');
    }
    wp_redirect( admin_url('edit.php?post_type=formality_form') );
    exit;
  }

  public function toggle_panel_link_url($status="toggle") {
    $link = wp_nonce_url('admin.php?action=formality_toggle_panel&status='.$status, basename(__FILE__), 'panel_nonce' );
    return $link;
  }

  public function background_download_templates() {
    $editor = new Formality_Editor($this->formality, $this->version);
    $editor->download_templates();
  }

}
