<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/admin
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Admin {

  private $formality;
  private $version;

  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
    $this->load_dependencies();
  }

  private function load_dependencies() {
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-results.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-notifications.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-tools.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-editor.php';
  }

  public function enqueue_assets() {
    wp_enqueue_style( $this->formality . "-admin", plugin_dir_url(__DIR__) . 'dist/styles/formality-admin.css', array(), $this->version, 'all' );
    wp_enqueue_script( $this->formality . "-admin", plugin_dir_url(__DIR__) . 'dist/scripts/formality-admin.js', array('wp-i18n'), $this->version, false );
    wp_set_script_translations( $this->formality . "-admin", 'formality', plugin_dir_path( __DIR__ ) . 'languages' );
  }

  public function formality_menu() {
    add_menu_page( 'Formality', 'Formality', 'edit_others_posts', 'formality_menu', function() { echo 'Formality'; }, "dashicons-formality", 30 );
  }

  public function form_columns($columns) {
    $new = array();
    foreach($columns as $key=>$value) {
      if($key=='title') {
        $new['formality_preview'] = '';
      } else if($key=='date') {
        $new['formality_type'] = __('Form type', 'formality');
        $new['formality_results'] = __('Results', 'formality');
      }
      $new[$key]=$value;
    }
    return $new;
  }

  public function form_columns_data( $column, $post_id ) {
    if($column == 'formality_results'){
      $term = get_term_by("slug", "form_" . $post_id, 'formality_tax');
      if($term) {
        echo '<a href="' . get_admin_url() . 'edit.php?post_type=formality_result&formality_tax=form_' . $post_id . '">' . $term->count . " " . __("results", "formality") . '</a>';
      } else {
        echo __("No results", "formality");
      }
    } else if($column == 'formality_type'){
      $type = get_post_meta($post_id, "_formality_type");
      if(isset($type[0]) && $type[0]=="conversational") {
        echo __("Conversational", "formality");
      } else {
        echo __("Standard", "formality");
      }
    } else if($column == 'formality_preview') {
      $metas = get_post_meta($post_id);
      $color1 = isset($metas["_formality_color1"][0]) && $metas["_formality_color1"][0] ? $metas["_formality_color1"][0] : '#000000';
      $color2 = isset($metas["_formality_color2"][0]) && $metas["_formality_color2"][0] ? $metas["_formality_color2"][0] : '#FFFFFF';
      $template = isset($metas["_formality_template"][0]) ? $metas["_formality_template"][0] : '';
      $opacity = isset($metas["_formality_overlay_opacity"][0]) ? $metas["_formality_overlay_opacity"][0] : '2';
      $title = get_the_title($post_id);
      $link = get_the_permalink($post_id);
      $bg = '';
      if($template){
        $bg = isset($metas["_formality_bg"][0]) ? str_replace('.jpg', '_thumb.jpg', $metas["_formality_bg"][0]) : '';
      } else if(isset($metas["_formality_bg_id"][0]) && $metas["_formality_bg_id"][0]) {
        $bg_library = wp_get_attachment_image_src($metas["_formality_bg_id"][0], 'thumbnail');
        $bg = $bg_library ? $bg_library[0] : '';
      }
      echo '<a target="_blank" href="'.$link.'" style="--color1:'.$color1.'; --color2:'.$color2.'; --opacity: '.( $opacity == '100' ? '1' : '0.' . $opacity ).';'. ( $bg ? ' background-image:url('.$bg.')' : '' ) .'">'.$title[0].'</a>';
    }
  }

  public function link_website( $links, $file ) {
    if ( strpos( $file, 'formality.php' ) !== false ) {
      $new_links = array('<a href="https://formality.dev" target="_blank">' . __('Visit new plugin site', 'formality') . '<span style="font-size: 1.1em; height: auto;
      vertical-align: middle; margin: 0 0 2px 1px" class="dashicons dashicons-formality"></span></a>');
      $links = array_merge( $links, $new_links );
    }
    return $links;
  }

  public function admin_header() {
    global $pagenow, $typenow;
    if(strpos($typenow, 'formality_') !== false) {
      global $wp_post_types;
      $labels = $wp_post_types[$typenow]->labels;
    ?>
      <div class="wrap wrap--formality">
        <h1 class="wp-heading-inline">
          <i></i>
          <?php
            if($pagenow == 'edit.php'){
              $object = get_queried_object();
              if($typenow == 'formality_result' && property_exists($object, 'term_id')) {
                /* translators: %s: Results for <form name>  */
                echo sprintf(__('Results for %s', 'formality'), $object->name);
              } else {
                echo $labels->name;
              }
            } else {
              echo $labels->singular_name . ' ' . get_the_ID();
            }
          ?>
        </h1>
        <?php
          $updated = true;
          $update_plugins = get_site_transient('update_plugins');
          if(!empty( $update_plugins->response )) {
            $updates = array_keys($update_plugins->response);
            foreach ($updates as $update) { if($update == 'formality/formality.php') { $updated = false; }}
          }?>
          <span class="formality-header-version<?php echo $updated ? ' updated' : '';?>">
            <?php echo $this->version; ?>
            <?php if(!$updated) {?><i><?php _e('There is a new version of Formality available', 'formality'); ?></i><?php } ?>
          </span>
        <?php
          if($pagenow == 'edit.php' && $typenow == 'formality_result' && isset($object)) {
            if(property_exists($object, 'term_id')) {
              $results = new Formality_Results( $this->formality, $this->version );
              $results->export_panel(str_replace("form_","",$object->slug));
            }
          } else if ($pagenow == 'edit.php' && $typenow == 'formality_form') {
            $templates = get_option('formality_templates', 0);
            $plugin_tools = new Formality_Tools( $this->formality, $this->version );
            $total = $GLOBALS['wp_query']->found_posts; ?>
            <a href="<?php echo admin_url('post-new.php?post_type='.$typenow); ?>" class="page-title-action"><?php echo $labels->add_new; ?></a>
            <?php $welcome = get_option('formality_welcome'); ?>
            <a class="formality-welcome-toggle <?php echo $welcome ? 'close' : 'open'; ?>" href="<?php echo $plugin_tools->toggle_panel_link_url(); ?>"><span><?php _e('Hide panel', 'formality'); ?></span><span><?php _e('Show panel', 'formality'); ?></span></a>
            <div class="welcome-old<?php echo $welcome ? '' : ' hidden'; ?>">
              <a class="welcome-panel-close formality-welcome-toggle" href="<?php echo $plugin_tools->toggle_panel_link_url(false); ?>"><?php _e('Hide panel', 'formality'); ?></a>
              <div class="welcome-panel-content">
                <h2><?php _e('Welcome to Formality!', 'formality'); ?></h2>
                <p class="about-description"><?php _e('Everything is ready to start building your forms:', 'formality'); ?></p>
                <div class="welcome-panel-column-container">
                  <div class="welcome-panel-column">
                    <h3><?php _e('Get Started', 'formality'); ?></h3>
                    <?php $samplelink = !$templates ? '#TB_inline?&width=380&height=210&inlineId=formality-sample-thick" class="thickbox' : $plugin_tools->generate_sample_link_url(); ?>
                    <a class="button button-hero" href="<?php echo admin_url('post-new.php?post_type=formality_form'); ?>">
                      <strong><?php echo $total ?  __('Add new form', 'formality') : __('Create your first form', 'formality'); ?></strong>
                    </a>
                    <p><?php /* translators: %s: generate sample forms link */ echo sprintf( __('or <a href="%s">generate a couple of sample forms</a> to practice with', 'formality'), $samplelink); ?></p>
                    <p><?php /* translators: %s: import form link */ echo sprintf( __('or <a href="%s">import your forms</a> with the WordPress import tool', 'formality'), admin_url('admin.php?import=wordpress')); ?></p>
                    <?php if(!$templates) {?>
                      <?php add_thickbox(); ?>
                      <div id="formality-sample-thick" style="display:none;">
                        <p><?php _e('Our sample forms are made with a selection of the best Unsplash photos. To start using them, you first have to download these photos from Unsplash servers. Click the button below to start the sample forms generation, and the images download process in the background.', 'formality');?></p>
                        <a class="button button-primary" href="<?php echo $plugin_tools->generate_sample_link_url(); ?>"><?php _e( 'Generate sample forms and download background images', 'formality' ); ?></a>
                        <p>
                         <a target="_blank" rel="noopener noreferrer" href="https://unsplash.com/terms"><?php _e( 'Terms and conditions', 'formality' ); ?></a>&nbsp;
                         <a target="_blank" rel="noopener noreferrer" href="https://unsplash.com/license"><?php _e( 'License', 'formality' ); ?></a>
                        </p>
                      </div>
                    <?php } ?>
                  </div>
                  <div class="welcome-panel-column">
                    <h3><?php _e('Quick links', 'formality'); ?></h3>
                    <ul>
                      <li><a href="<?php echo admin_url('edit.php?post_type=formality_form'); ?>" class="welcome-icon welcome-edit-page"><?php _e('Manage your forms', 'formality'); ?></a></li>
                      <li><a href="<?php echo admin_url('edit.php?post_type=formality_result'); ?>" class="welcome-icon welcome-view-site"><?php _e('View or export your results', 'formality'); ?></a> <span class="badge"></span></li>
                      <li><a target="_blank" href="https://wordpress.org/support/plugin/formality" class="welcome-icon dashicons-warning"><?php _e('Request support or report a bug', 'formality'); ?></a></li>
                      <li><a target="_blank" href="https://formality.dev" class="welcome-icon welcome-learn-more"><?php _e('Learn more about Formality', 'formality'); ?></a></li>
                    </ul>
                  </div>
                  <div class="welcome-panel-column welcome-panel-last">
                    <h3><?php _e('Support us', 'formality'); ?></h3>
                    <p><?php /* translators: %s: donate link + 5 stars review link  */ echo sprintf(__('If you enjoy using Formality, please consider <a href="%1$s">making a donation</a>, or rate this plugin with a <a href="%2$s">5 stars review</a> on the WordPress plugin directory. You can also subscribe to our newsletter (max once a month).', 'formality'), 'https://www.paypal.me/michelegiorgi/" target="_blank', 'https://wordpress.org/support/plugin/formality/reviews/?filter=5#new-post" target="_blank'); ?></p>
                    <form class="formality-newsletter" novalidate>
                      <input placeholder="<?php _e('Your email address', 'formality'); ?>" type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                      <input type="submit" value="<?php _e('Subscribe', 'formality'); ?>" name="subscribe" id="mc-embedded-subscribe" class="button">
                      <br><label class="checkbox subfield" for="gdpr_33536"><input type="checkbox" id="gdpr_33536" name="gdpr[33536]" value="Y" class="av-checkbox "><small><?php /* translators: %s: privacy policy link */ echo sprintf( __('Accept our <a href="%s">privacy policy</a>.', 'formality'), 'https://www.iubenda.com/privacy-policy/87262067/legal" target="_blank'); ?></small></label>
                      <div class="formality-newsletter-result"></div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

        <?php } ?>

        <?php if ($pagenow == 'edit.php' && (isset( $_GET['formality_task']) || isset( $_POST['formality_task'])) && get_option('formality_notice') ) {
          $notice = get_option('formality_notice');
          echo '<div class="notice notice-'.$notice[0].' is-dismissible"><p>'.$notice[1].'</p></div>';
          delete_option('formality_notice');
        } ?>
      </div>
    <?php }
  }

}
