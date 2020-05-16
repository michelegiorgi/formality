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

class Formality_Notifications {

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
    
  public function email_send($to, $data) {
    $render = new Formality_Results($this->formality, $this->version);
    $fields = $render->result_data($data['result_id'], false);
    $message = $this->email_content($fields, $data);
    $subject = __("New result for", "formality") . ' ' . $data['form_title'];    
    
    add_filter( 'wp_mail_content_type', [$this, 'email_content_type']);
    add_filter( 'wp_mail_from_name', [$this, 'sender_name']);    
    wp_mail($to, $subject, $message);
    remove_filter( 'wp_mail_content_type', [$this, 'email_content_type']);
    remove_filter( 'wp_mail_from_name', [$this, 'sender_name']);    
  }

  public function sender_email( $original_email_address ) {
    return get_option('admin_email');
  }
 
  public function sender_name( $original_email_from ) {
    return get_bloginfo('name');
  }
  
  public function email_content_type(){
    return "text/html";
  }
  
  public function email_content($fields, $data){
    $template = file_get_contents(plugin_dir_url(__DIR__) . "public/templates/notification.html");
    $link = '<a href="' . get_admin_url() . 'post.php?post=' .$data['result_id']. '&action=edit">' . __('View this result', 'formality') . '</a> ' . __('in your admin dashboard', 'formality') . '<br>';
    $link .= '<a href="' . get_admin_url() . 'post.php?post=' .$data['form_id']. '&action=edit">' . __('View all results', 'formality') . '</a> ' . __('for', 'formality') . ' ' . $data['form_title'] . '<br><br>';
    $link .= 'Made with <strong>Formality</strong>';
    $fields = __("New result for", "formality") . '<h2 style="margin:0">' . $data['form_title'] . '</h2><br><br>' . $fields . '<br>';
    $content = str_replace('%%DATA%%', $fields, $template);
    $content = str_replace('%%LINK%%', $link, $content);
    return $content;
  }

}
