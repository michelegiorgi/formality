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
      if ($key=='title') {
        $new['formality_uid'] = __('ID', 'formality');
        $new[$key] = __('First filled field', 'formality');
      } else {
        $new[$key] = $title;
      }
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
    $response = array();

    if(!(isset( $_GET['form_id']) && isset($_REQUEST['action']) && 'export_formality_result' == $_REQUEST['action'])) {
      $response['error'] = __("No results to export!", "formality");
    }

    if(!isset( $_GET['export_nonce'] ) || !wp_verify_nonce( $_GET['export_nonce'], basename( __FILE__ ))) {
      $response['error'] = __("Wrong nonce", "formality");
    };

    $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
    if(!$form_id) {
      $response['error'] = __("No results to export!", "formality");
    }

    if(!current_user_can('edit_posts')) {
      $response['error'] = __("You don't have permission to export these results", "formality");
    }

    header('Content-type: application/json');

    if(!isset($response['error'])) {

      //create upload folders if needed
      $upload = new Formality_Upload($this->formality, $this->version);
      $upload_dir = $upload->get_upload_dir('exports');
      if(!file_exists($upload_dir['htaccess'])) { $upload->create_upload_dir(); }
      if(!is_dir($upload_dir['path'])) { wp_mkdir_p($upload_dir['path']); }
      $temp_path = path_join($upload_dir['path'], 'progress.csv');

      //exit if is a progress request
      if(isset($_GET['progress']) && $_GET['progress'] && file_exists($temp_path)) {
        $output = fopen($temp_path, 'r');
        $progress = -1;
        while($content = fgetcsv($output)){ $progress++; }
        fclose($output);
        $response['progress'] = $progress;
        echo json_encode($response);
        exit;
      } else if(isset($_GET['cleanup']) && $_GET['cleanup']) {
        $files = array_diff(scandir($upload_dir['path']), array('.', '..'));
        foreach($files as $file) { wp_delete_file(path_join($upload_dir['path'], $file)); }
        $response['cleanup'] = 1;
        echo json_encode($response);
        exit;
      };

      //build columns
      $columns = array();
      $form_title = '';
      if(isset($_GET['field_id']) && $_GET['field_id']) { $columns[] = array( 'id' => 'field_id', 'type' => false, 'name' => 'ID' ); }
      if(isset($_GET['field_date']) && $_GET['field_date']) { $columns[] = array( 'id' => 'field_date', 'type' => false, 'name' => 'Date' ); }
      if(isset($_GET['field_author']) && $_GET['field_author']) { $columns[] = array( 'id' => 'field_author', 'type' => false, 'name' => 'Author' ); }
      $form_query = new WP_Query(array( 'post_type' => 'formality_form', 'p' => $form_id, 'posts_per_page' => 1 ));
      while ( $form_query->have_posts() ) : $form_query->the_post();
        if(has_blocks()) {
          $blocks = parse_blocks(get_the_content());
          $form_title = get_the_title();
          foreach ( $blocks as $block ) {
            if($block['blockName']) {
              $type = str_replace("formality/","",$block['blockName']);
              if($type !== 'step' && $type !== 'message' && $type !== 'media') {
                $field = "field_" . $block["attrs"]["uid"];
                if(isset($_GET[$field]) && $_GET[$field]) {
                  $columns[] = array(
                    'id' => $field,
                    'type' => $type,
                    'name' => isset($block["attrs"]["name"]) ? $block["attrs"]["name"] : __("Field name", "formality")
                  );
                }
              }
            }
          }
        }
      endwhile;
      wp_reset_query();
      wp_reset_postdata();

      //build rows
      if(count($columns) < 2) { $response['error'] = __("No results to export!", "formality"); }
      $limit = isset($_GET['export_limit']) && $_GET['export_limit'] ? intval($_GET['export_limit']) : -1;
      $skip = isset($_GET['export_skip']) && $_GET['export_skip'] ? intval($_GET['export_skip']) : 0;
      $order = isset($_GET['export_order']) && $_GET['export_order'] == 'asc' ? 'ASC' : 'DESC';
      $status = isset($_GET['export_status']) && in_array($_GET['export_status'], array('any', 'publish', 'unread'), true) ? $_GET['export_status'] : 'any';
      $month = isset($_GET['export_month']) && $_GET['export_month'] ? intval($_GET['export_month']) : false;
      $resume = isset($_GET['resume']) && $_GET['resume'] ? true : false;
      $csv_filename = 'formality_' . sanitize_title($form_title) . '_' . date('Ymd_Hi') . '.csv';

      if($resume && file_exists($temp_path)) {
        //count temp export file rows
        $output = fopen($temp_path, 'r');
        $progress = -1;
        while(!feof($output)){
          $line = fgets($output);
          $progress++;
        }
        $skip = $skip - $progress;
        fclose($output);
        //open temp file
        $output = fopen($temp_path, 'a');
      } else {
        //delete old export files
        $files = array_diff(scandir($upload_dir['path']), array('.', '..'));
        foreach($files as $file) { wp_delete_file(path_join($upload_dir['path'], $file)); }
        //create new export file with header row
        $output = fopen($temp_path, 'w');
        fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
        $row = array();
        foreach($columns as $column) { $row[] = $column['name']; }
        fputcsv($output, $row);
      }

      $result_query = new WP_Query(array(
        'post_type' => 'formality_result',
        'posts_per_page' => $limit,
        'offset' => $skip,
        'order' => $order,
        'post_status' => $status,
        'm' => $month,
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
          if($field == 'field_id') {
            $row[] = $result_id;
          } else if($field == 'field_date') {
            $row[] = get_the_date();
          } else if($field == 'field_author') {
            $row[] = get_the_author() ? get_the_author() : __('Guest', 'formality');
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

    $final_name = wp_unique_filename($upload_dir['path'], $csv_filename);
    rename($temp_path, path_join($upload_dir['path'], $final_name));
    if(!isset($response['error'])) {
      $response['url'] = path_join($upload_dir['url'], $final_name);
      $response['file'] = $csv_filename;
    }
    echo json_encode($response);
    exit;
  }

  /**
   * Render export panel on results archive page
   *
   * @since    1.4
   */
  public function export_panel($form_id){
    $total = $GLOBALS['wp_query']->found_posts; ?>
    <a class="page-title-action formality-export-toggle" href="#"><?php _e('Export', 'formality'); ?></a>
    <div class="welcome-panel export-panel hidden">
      <div class="welcome-panel-content">
        <div class="welcome-panel-column-container">
          <form action="<?php echo admin_url('admin.php'); ?>" method="GET">
            <div class="welcome-panel-column">
              <h3><?php _e('Columns', 'formality'); ?></h3>
              <p><?php _e('Select the data to export as columns in the csv file export.', 'formality') ;?></p>
              <fieldset class="metabox-prefs">
                <label><input name="field_id" type="checkbox" value="1" checked="checked"><?php _e('ID', 'formality') ;?></label>
                <label><input name="field_date" type="checkbox" value="1" checked="checked"><?php _e('Date', 'formality') ;?></label>
                <label><input name="field_author" type="checkbox" value="1" checked="checked"><?php _e('Author', 'formality') ;?></label>
                <?php
                  $form_query = new WP_Query(array(
                    'post_type' => 'formality_form',
                    'p' => $form_id,
                    'posts_per_page' => 1
                  ));
                  while ( $form_query->have_posts() ) : $form_query->the_post();
                    if(has_blocks()) {
                      $form_title =  get_the_title();
                      $blocks = parse_blocks(get_the_content());
                      foreach ( $blocks as $block ) {
                        if($block['blockName']) {
                          $type = str_replace("formality/","",$block['blockName']);
                          if($type !== 'step' && $type !== 'message' && $type !== 'media') {
                            $field_id = "field_" . $block["attrs"]["uid"];
                            $field_name = isset($block["attrs"]["name"]) ? $block["attrs"]["name"] : __("Field name", "formality");
                            echo '<label><input name="'.$field_id.'" type="checkbox" id="'.$field_id.'" value="1" checked="checked">'.$field_name.'</label>';
                          }
                        }
                      }
                    }
                  endwhile;
                  wp_reset_query();
                  wp_reset_postdata();
                ?>
              </fieldset>
              <input type="hidden" name="action" value="export_formality_result">
              <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
              <input type="hidden" name="export_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)); ?>">
            </div>
            <div class="welcome-panel-column">
              <h3><?php _e('Filters', 'formality'); ?></h3>
              <fieldset>
                <label><?php _e('Limit', 'formality') ;?></label>
                <input type="number" step="1" min="1" max="<?php echo $total; ?>" name="export_limit" maxlength="3" value="<?php echo $total; ?>" /> <?php _e('results', 'formality'); ?>
              </fieldset>
              <fieldset>
                <label><?php _e('Skip', 'formality') ;?></label>
                <input type="number" step="1" min="0" max="<?php echo $total-1; ?>" name="export_skip" maxlength="3" value="0" /> <?php _e('results', 'formality'); ?>
              </fieldset>
              <fieldset>
                <label><?php _e('Month', 'formality') ;?></label>
                <?php
                  echo '<select name="export_month">';
                  echo '<option data-results="'.$total.'" value="">' . __('Any', 'formality') . '</option>';
                  global $wpdb;
                  $months = $wpdb->get_results(
                    $wpdb->prepare("SELECT YEAR( post_date ) AS year, MONTH( post_date ) AS month, count(DISTINCT ID) AS results
                      FROM {$wpdb->posts} AS wposts
                      LEFT JOIN {$wpdb->postmeta} AS wpostmeta ON (wposts.ID = wpostmeta.post_id)
                      LEFT JOIN {$wpdb->term_relationships} AS tax_rel ON (wposts.ID = tax_rel.object_id)
                      LEFT JOIN {$wpdb->term_taxonomy} AS term_tax ON (tax_rel.term_taxonomy_id = term_tax.term_taxonomy_id)
                      LEFT JOIN {$wpdb->terms} AS terms ON (terms.term_id = term_tax.term_id)
                      WHERE post_type = %s AND term_tax.taxonomy = %s AND terms.slug = %s
                      GROUP BY YEAR( post_date ), MONTH( post_date )
                      ORDER BY post_date DESC",
                    'formality_result', 'formality_tax', 'form_'. intval($form_id))
                  );
                  foreach($months as $month) {
                    echo '<option data-results="'.$month->results.'" value="'. $month->year . sprintf("%02d", $month->month) . '">'. date_i18n('F Y', strtotime($month->year . '-' . $month->month)).' (' .$month->results .')</option>';
                  }
                  echo '</select>';
                ?>
              </fieldset>
              <fieldset>
                <label><?php _e('Status', 'formality') ;?></label>
                <select name="export_status">
                  <option value="any"><?php _e('Any', 'formality') ;?></option>
                  <option value="publish"><?php _e('Read', 'formality') ;?></option>
                  <option value="unread"><?php _e('Unread', 'formality') ;?></option>
                </select>
              </fieldset>
            </div>
            <div class="welcome-panel-column">
              <h3><?php _e('Export', 'formality'); ?></h3>
              <fieldset>
                <label><?php _e('Order', 'formality') ;?></label>
                <label><input type="radio" name="export_order" value="asc"><?php _e('Ascending', 'formality') ;?></label>&nbsp;&nbsp;
                <label><input type="radio" name="export_order" value="desc" checked="checked"><?php _e('Descending', 'formality') ;?></label>
              </fieldset>
              <fieldset>
                <label><?php _e('File', 'formality') ;?></label>
                <input name="export_filename" type="text" value="<?php echo 'formality_' . sanitize_title($form_title) . '_' . date('Ymd_Hi'); ?>"><?php _e('.csv', 'formality') ;?>
              </fieldset>
              <button type="submit" class="button button-primary button-hero"><?php _e('Export now', 'formality'); ?></button>
              <div class="export-stats">
                <strong>
                  <span class="export-count-progress">0</span><span class="export-total-live"><?php echo $total; ?></span>
                </strong> <?php _e('results', 'formality') ;?>
                <small><?php /* translators: %s: remaining seconds */ echo sprintf( __("About %s seconds remaining", "formality"), '<span class="export-time-remaining"></span>');?></small>
              </div>
              <div class="export-progress media-item">
                <div class="progress"><div class="bar"></div></div>
                <span></span>
                <small><?php _e("Exporting may take a while. Please don't close your browser or refresh the page until the process is complete.", 'formality'); ?></small>
              </div>
              <div class="export-result">
                <a href="#"></a>
                <small class="export-cleanup">
                  <?php /* translators: %s: delete export file link */ echo sprintf( __('This file will be automatically deleted from your server at the next export attempt. <a href="%s">Click here</a> to delete this file immediately.', 'formality'), wp_nonce_url('admin.php?action=export_formality_result&cleanup=1&form_id='. $form_id, basename(__FILE__), 'export_nonce' )
); ?></small>
                <p></p>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div><?php
  }
}
