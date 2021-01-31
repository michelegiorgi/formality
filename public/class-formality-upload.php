<?php

/**
 * Form file upload functions
 *
 * @link       https://formality.dev
 * @since      1.3.0
 * @package    Formality
 * @subpackage Formality/public
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Upload {

  private $formality;
  private $version;

  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
  }

  /**
   * Add routes to upload files via WP REST API
   *
   * @since    1.3.0
   */
  public function api_endpoints() {
    register_rest_route( 'formality/v1', '/upload/', array(
      'methods'  => 'POST',
      'callback' => [$this, 'temp_upload'],
      'permission_callback' => function () { return true; }
    ));
  }

  /**
   * Upload temp file
   *
   * @since    1.3.0
   */
  public function temp_upload() {
    $response = array();
    $nonce = isset($_POST['nonce']) ? sanitize_key($_POST['nonce']) : '';
    if (wp_verify_nonce( $nonce, 'formality_async' ) && !empty($_FILES)) {

      require_once( ABSPATH . 'wp-admin/includes/file.php' );
      add_filter('upload_dir', 'formality_change_upload_dir');

      function formality_change_upload_dir($dir) {
        $dir['subdir'] = '/formality/uploads/temp';
        $dir['path'] = $dir['basedir'] . '/formality/uploads/temp';
        $dir['url'] = $dir['baseurl'] . '/formality/uploads/temp';
        return $dir;
      }

      $file = $_FILES[array_key_first($_FILES)];
      $uploaded_file = wp_handle_upload($file, array('test_form' => false));
      if (!isset($uploaded_file['error'])) {
        $response["status"] = 200;
        $response["file"] = basename($uploaded_file['url']);
      } else {
        $response["status"] = 400;
        $response['error'] = $uploaded_file['error'];
      }

      remove_filter('upload_dir', 'formality_change_upload_dir');
    } else {
      //bad token
      $response["status"] = 300;
    }
    header('Content-type: application/json');
    echo json_encode($response);
    exit;
  }

}
