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

class Formality_Results {

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
    
  public function auto_publish() {
    global $pagenow;
    if ( 'post.php' === $pagenow && isset($_GET['post']) ) {
      if('formality_result' === get_post_type( $_GET['post'] )) {
        if('unread' === get_post_status($_GET['post'])) {
          $my_post = array( 'ID' => $_GET['post'], 'post_status' => 'publish');
          wp_update_post( $my_post );
        }
      }
    }
  } 
  
  public function unread_bubble($menu) {
    $count = 0;
    $status = "unread";
    $num_posts = wp_count_posts( "formality_result", 'readable' );
    if ( !empty($num_posts->$status) ) { $count = $num_posts->$status; }
    foreach( $menu as $menu_key => $menu_data ) {
      if( "formality_menu" != $menu_data[2] ) { continue; }
      if($count) { $menu[$menu_key][4] .= " unread"; }
      $menu[$menu_key][0] .= " <span class='update-plugins count-$count'><span class='plugin-count'>" . number_format_i18n($count) . '</span></span>';
    }
    return $menu;
  }
  
  public function unread_status(){
    register_post_status( 'unread', array(
      'label'                     => __( 'Unread', 'formality' ),
      'public'                    => true,
      'exclude_from_search'       => false,
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'label_count'               => /* translators: %s: unread count */ _n_noop( 'Unread <span class="count">(%s)</span>', 'Unread <span class="count">(%s)</span>', 'formality' )
    ));
  }
  
  public function metaboxes() {
    add_meta_box('result_data', 'Result data', array( $this, 'result_data' ), 'formality_result', 'normal', 'high');
  }
  
  public function result_data($result_id = 0, $echo = true) {
    $header = '<table class="wp-list-table widefat fixed striped tags">
      <thead>
        <tr>
          <th align="left" class="manage-column column-[name]" id="[name]" scope="col">Field</th>
          <th align="left" class="manage-column column-[value]" id="[value]" scope="col">Value</th>
        </tr>
      </thead><tbody>';
    $footer = '</tbody><tfoot>
    </tfoot>
    </table>';
    $return = '';
    
    if(!$result_id) {
      $result_id = get_the_ID();
    } else if(is_object($result_id)) {
      $result_id = $result_id->ID;
    }
    
    $form_id = get_post_meta( $result_id, "id", true);
    $args = array(
      'post_type' => 'formality_form',
      'p'   => $form_id,
      'posts_per_page' => 1
    );

    $the_query = new WP_Query( $args );
    $index = 0;
    while ( $the_query->have_posts() ) : $the_query->the_post();
      if(has_blocks()) {
        $blocks = parse_blocks(get_the_content());
        foreach ( $blocks as $block ) {
          if($block['blockName']) {
            $index++;
            $type = str_replace("formality/","",$block['blockName']);
            if($index==1) {
              if($type=="step") {
                $return .= '<strong>' . (isset($block["attrs"]["name"]) ? $block["attrs"]["name"] : __("Step", "formality")) . '</strong>';
              }
              $return .= $header;
            } else if($type=="step") {
              $return .= $footer;
              $return .= '<br><strong>' . (isset($block["attrs"]["name"]) ? $block["attrs"]["name"] : __("Step", "formality")) . '</strong>';
              $return .= $header;
            }
            $return .= $this->field($result_id, $block, $index);
          }
        }
      }
    endwhile;
    wp_reset_query();
    wp_reset_postdata();
    $return .= $footer;
    if($echo) {
      echo $return;
    } else {
      return $return;
    }
  }
  
  public function field($result_id, $block, $index) {
    if(!isset($block["attrs"]['exclude'])) {
      $fieldname = "field_" . $block["attrs"]["uid"];
      $fieldvalue = get_post_meta( $result_id, $fieldname, true );
      if(is_array($fieldvalue)) {
        $values = "";
        foreach($fieldvalue as $subvalue){
          $values .= $subvalue . '<br>';
        }
        $fieldvalue = $values;
      } else {
        $fieldvalue = nl2br($fieldvalue);
      }
      $row = '<tr><td>' . (isset($block["attrs"]["name"]) ? $block["attrs"]["name"] : __("Field name", "formality")) . '</td><td>' . $fieldvalue . '</td></tr>';
      return $row;
    }
  }
   
  public function column_id($defaults){
    $new = array();
    foreach($defaults as $key => $title) {
      if ($key=='title')
        $new['uid'] = __('ID');
      $new[$key] = $title;
    }
    return $new;
  }
  
  public function column_id_value($column_name, $id){
    if($column_name === 'uid'){
      echo '<span>' . $id . '</span>';
    }
  }
  
  public function mark_as() {
    if (! ( isset( $_GET['result']) || isset( $_POST['result'])  || ( isset($_REQUEST['action']) && 'mark_as_formality_result' == $_REQUEST['action'] ) ) ) {
      wp_die(__("No result to mark as read has been supplied!", "formality"));
    }
   
    if ( !isset( $_GET['mark_as_nonce'] ) || !wp_verify_nonce( $_GET['mark_as_nonce'], basename( __FILE__ ) ) ) return;
   
    $post_id = (isset($_GET['result']) ? absint( $_GET['result'] ) : absint( $_POST['result'] ) );
    $post = get_post( $post_id );
  
    if (isset( $post ) && $post != null) {
      if(current_user_can('edit_posts')) {
        $oldstatus = $post->post_status;
        if($oldstatus == "unread") { $newstatus = "publish"; } else { $newstatus = "unread"; }
        $my_post = array( 'ID' => $post_id, 'post_status' => $newstatus);
        wp_update_post( $my_post );
        wp_redirect( admin_url('edit.php?post_type=formality_result') ); 
        exit;
      } else {
        wp_die(__("You don't have permission to edit this result", "formality"));
      }
    } else {
      wp_die(__("Result change failed, could not find original result: ", "formality") . $post_id);
    }
  }

  public function mark_as_link($actions, $post) {
    if (current_user_can('edit_posts') && $post->post_type=='formality_result') {
      $status = $post->post_status;
      if($status == "unread") {
        $link_label = __("Mark as read", "formality");
      } else {
        $link_label = __("Mark as unread", "formality");
      }
      $link = wp_nonce_url('admin.php?action=mark_as_formality_result&result=' . $post->ID, basename(__FILE__), 'mark_as_nonce' );
      $actions['mark_as'] = '<a href="'.$link.'" title="'.$link_label.'" rel="permalink">'.$link_label.'</a>';
    }
    return $actions;
  }

  public function mark_all_as_read() {
    if (! (isset($_REQUEST['action']) && 'mark_all_formality_result' == $_REQUEST['action']) ) {
      wp_die(__("No result to mark as read has been supplied!", "formality"));
    }
    if ( !isset( $_GET['mark_all_nonce'] ) || !wp_verify_nonce( $_GET['mark_all_nonce'], basename( __FILE__ ) ) ) return;
 
    $args = array('post_type'=> 'formality_result', 'post_status' => 'unread', 'posts_per_page'=> -1 );
    $unread_posts = get_posts($args);
    
    if(count($unread_posts)) {
      if(current_user_can('edit_posts')) {
        foreach($unread_posts as $post_to_publish){
          $query = array( 'ID' => $post_to_publish->ID, 'post_status' => 'publish' );
          wp_update_post( $query, true );  
        }
        wp_redirect( admin_url('edit.php?post_type=formality_result') ); 
        exit;
      } else {
        wp_die(__("You don't have permission to edit these results", "formality"));
      }
    } else {
      wp_die(__("No results to mark as read", "formality"));
    }
  }
  
  public function mark_all_as_read_link($post_type, $which) {
    if($post_type == 'formality_result') {
      $num_posts = wp_count_posts( "formality_result", 'readable' );
      if ( $num_posts && $num_posts->unread ) {       
        $link = wp_nonce_url('admin.php?action=mark_all_formality_result', basename(__FILE__), 'mark_all_nonce' );
        $link_label = __("Mark all as read", "formality");
        echo '<a class="button button-primary" href="'.$link.'" title="'.$link_label.'" rel="permalink">'.$link_label.'</a> &nbsp;';
      }
    }
  }

}
