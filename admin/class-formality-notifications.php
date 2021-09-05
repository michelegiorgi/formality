<?php

/**
 * Build and send notifications.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/admin
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Notifications {

  private $formality;
  private $version;

  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
  }

  public function email_send($to, $data) {
    $render = new Formality_Results($this->formality, $this->version);
    $fields = $render->result_data($data['result_id'], false);
    $message = $this->email_content($fields, $data);
    $subject = __("New result for", "formality") . ' ' . $data['form_title'] . ' (' . $data['result_id'] . ')';

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

  public function email_content($content="", $data){
    $file_name = 'formality-notification.php';
    $file_path = locate_template($file_name) ? locate_template($file_name) : plugin_dir_path(__DIR__) . "public/templates/" . $file_name;
    $result_link = get_admin_url() . 'post.php?post=' .$data['result_id']. '&action=edit';
    $results_link = get_admin_url() . 'post.php?post=' .$data['form_id']. '&action=edit';
    $title = $data['form_title'];
    ob_start();
    include($file_path);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
  }

}
