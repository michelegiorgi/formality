<?php

/**
 * Results functions.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/admin
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Results {

  private $formality;
  private $version;

  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
  }

  /**
   * Change post status on first view
   *
   * @since    1.0
   */
  public function auto_publish() {
    global $pagenow;
    $postid = isset($_GET['post']) ? absint($_GET['post']) : 0;
    if ( 'post.php' === $pagenow && $postid ) {
      if('formality_result' === get_post_type( $postid )) {
        if('unread' === get_post_status($postid)) {
          $tochange = array( 'ID' => $postid, 'post_status' => 'publish');
          wp_update_post($tochange);
        }
      }
    }
  }

  /**
   * Render unread result counter after Formality menu label
   *
   * @since    1.0
   */
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

  /**
   * Define "unread" post status
   *
   * @since    1.0
   */
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

  /**
   * Define result view meta box
   *
   * @since    1.0
   */
  public function metaboxes() {
    add_meta_box('result_data', 'Result data', array( $this, 'result_data' ), 'formality_result', 'normal', 'high');
  }

  /**
   * Render result data
   *
   * @since    1.0
   */
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

  /**
   * Render result field
   *
   * @since    1.0
   */
  public function field($result_id, $block, $index) {
    if(!isset($block["attrs"]['exclude'])) {
      $fieldname = "field_" . $block["attrs"]["uid"];
      $type = str_replace("formality/","",$block['blockName']);
      $fieldvalue = get_post_meta( $result_id, $fieldname, true );
      if(is_array($fieldvalue)) {
        $values = "";
        foreach($fieldvalue as $subvalue){
          $values .= $subvalue . '<br>';
        }
        $fieldvalue = $values;
      } else if($type=="upload") {
        if($fieldvalue) {
          $ext = pathinfo($fieldvalue, PATHINFO_EXTENSION);
          if(in_array($ext, array('gif', 'png', 'bmp', 'jpg', 'jpeg', 'svg'))) {
            $fieldvalue = '<a target="_blank" href="' . $fieldvalue . '"><img style="max-width:100%; height:auto;" src="' . $fieldvalue . '" alt="" /></a>';
          } else {
            $fieldvalue = '<a target="_blank" href="' . $fieldvalue . '">' . __('Download', 'formality') . '</a>';
          }
        }
      } else {
        $fieldvalue = nl2br($fieldvalue);
      }
      $row = '<tr><td>' . (isset($block["attrs"]["name"]) ? $block["attrs"]["name"] : __("Field name", "formality")) . '</td><td>' . $fieldvalue . '</td></tr>';
      return $row;
    }
  }

  /**
   * Define id column on results archive page
   *
   * @since    1.0
   */
  public function column_id($defaults){
    $new = array();
    foreach($defaults as $key => $title) {
      if ($key=='title')
        $new['formality_uid'] = __('ID', 'formality');
      $new[$key] = $title;
    }
    return $new;
  }

  /**
   * Render result id on results archive page
   *
   * @since    1.0
   */
  public function column_id_value($column_name, $id){
    if($column_name === 'formality_uid'){
      echo '<span>' . $id . '</span>';
    }
  }

  /**
   * Mark as read/unread function
   *
   * @since    1.0
   */
  public function mark_as() {
    if (! ( isset( $_GET['result']) || ( isset($_REQUEST['action']) && 'mark_as_formality_result' == $_REQUEST['action'] ) ) ) {
      wp_die(__("No result to mark as read has been supplied!", "formality"));
    }

    if ( !isset( $_GET['mark_as_nonce'] ) || !wp_verify_nonce( $_GET['mark_as_nonce'], basename( __FILE__ ) ) ) return;

    $post_id = isset($_GET['result']) ? absint( $_GET['result'] ) : 0;
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

  /**
   * Render "mark as read/unread" links on results archive page
   *
   * @since    1.0
   */
  public function mark_as_link($actions, $post) {
    if (current_user_can('edit_posts') && $post->post_type=='formality_result') {
      $status = $post->post_status;
      $link_label = $status == "unread" ? __("Mark as read", "formality") : __("Mark as unread", "formality");
      $link = wp_nonce_url('admin.php?action=mark_as_formality_result&result=' . $post->ID, basename(__FILE__), 'mark_as_nonce' );
      $actions['mark_as'] = '<a href="'.$link.'" title="'.$link_label.'" rel="permalink">'.$link_label.'</a>';
    }
    return $actions;
  }

  /**
   * Mark all as read function
   *
   * @since    1.0
   */
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

  /**
   * Render "mark all as read" link on results archive pages
   *
   * @since    1.0
   */
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

  /**
   * Export results function
   *
   * @since    1.4
   */
  public function export() {
    if (!(isset( $_GET['form_id']) && isset($_REQUEST['action']) && 'export_formality_result' == $_REQUEST['action'])) {
      wp_die(__("No results to export!", "formality"));
    }
    if ( !isset( $_GET['export_nonce'] ) || !wp_verify_nonce( $_GET['export_nonce'], basename( __FILE__ ) ) ) return;
    $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
    if(!$form_id) { wp_die(__("No results to export!", "formality")); }

    //columns
    $columns = array();
    $columns[] = array( 'id' => 'date', 'type' => false, 'name' => 'Date' );
    $columns[] = array( 'id' => 'id', 'type' => false, 'name' => 'ID' );
    $form_query = new WP_Query(array(
      'post_type' => 'formality_form',
      'p' => $form_id,
      'posts_per_page' => 1
    ));
    while ( $form_query->have_posts() ) : $form_query->the_post();
      if(has_blocks()) {
        $blocks = parse_blocks(get_the_content());
        foreach ( $blocks as $block ) {
          if($block['blockName']) {
            $type = str_replace("formality/","",$block['blockName']);
            if($type !== 'step' && $type !== 'message' && $type !== 'media') {
              $columns[] = array(
                'id' => "field_" . $block["attrs"]["uid"],
                'type' => $type,
                'name' => isset($block["attrs"]["name"]) ? $block["attrs"]["name"] : __("Field name", "formality")
              );
            }
          }
        }
      }
    endwhile;
    wp_reset_query();
    wp_reset_postdata();

    //rows
    if(count($columns) < 2) { wp_die(__("No results to export!", "formality")); }

    $csv_filename = 'formality_'.date('Y-m-d').'.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$csv_filename);
    $output = fopen('php://output', 'w');

    $row = array();
    foreach($columns as $column) {
      $row[] = $column['name'];
    }
    fputcsv($output, $row);

    $result_query = new WP_Query(array(
      'post_type' => 'formality_result',
      'posts_per_page' => -1,
      'tax_query' => array(
        array(
          'taxonomy' => 'formality_tax',
          'field'    => 'slug',
          'terms'    => 'form_' . $form_id,
        ),
      ),
    ));
    while ( $result_query->have_posts() ) : $result_query->the_post();
      $row = array();
      $result_id = get_the_ID();
      $fields = get_post_meta( $result_id );
      foreach($columns as $column) {
        $field = $column['id'] ?? false;
        $value = isset($fields[$field]) ? maybe_unserialize($fields[$field][0]) : '';
        if(is_array($value)) { $value = implode(', ', $value); }
        if($field == 'id') {
          $row[] = $result_id;
        } else if($field == 'date') {
          $row[] = get_the_date();
        } else {
          $row[] = $value;
        }
      }
      fputcsv($output, $row);
    endwhile;
    wp_reset_query();
    wp_reset_postdata();
    fclose($output);
  }

  /**
   * Render export link on results archive page
   *
   * @since    1.4
   */
  public function export_link($form_id){
    $link = wp_nonce_url('admin.php?action=export_formality_result&form_id='. $form_id, basename(__FILE__), 'export_nonce' );
    $link_label = __("Export", "formality");
    $total = $GLOBALS['wp_query']->found_posts; ?>
    <a class="page-title-action formality-export-toggle" href="#"><?php echo $link_label; ?></a>
    <div class="welcome-panel export-panel hidden">
      <div class="welcome-panel-content">
        <div class="welcome-panel-column-container">
          <div class="welcome-panel-column">
            <h3><?php _e('Limit', 'formality'); ?></h3>
            <input type="number" step="1" min="1" max="<?php echo $total; ?>" class="screen-per-page" name="" maxlength="3" value="<?php echo $total; ?>"> <?php _e('latest results', 'formality'); ?>
          </div>
          <div class="welcome-panel-column">
            <h3><?php _e('Quick links', 'formality'); ?></h3>
            <?php
              $listable = new WP_List_Table;
              $listable->months_dropdown('formality_result');
            ?>
          </div>
          <div class="welcome-panel-column">
            <a class="button button-primary button-hero" href="<?php echo $link; ?>" title="<?php echo $link_label; ?>" rel="permalink"><?php echo $link_label; ?> now</a>
            <br>
          </div>
        </div>
      </div>
    </div><?php
  }
}
