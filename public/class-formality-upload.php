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
      'callback' => [$this, 'upload_temp_file'],
      'permission_callback' => function() { return true; }
    ));
  }

  /**
   * Init upload directory
   *
   * @since    1.3.0
   */
  public function get_upload_dir($temp = false, $key = false) {
    $dir = array();
    $subbase = '/formality/uploads';
    $upload = wp_upload_dir();
    $dir['sub'] = $subbase . ($temp ? '/temp' : '');
    $dir['path'] = $upload['basedir'] . $dir['sub'];
    $dir['url'] = $upload['baseurl'] . $dir['sub'];
    return !$key ? $dir : $dir[$key];
  }

  /**
   * Create upload directory
   *
   * @since    1.3.0
   */
  public function create_upload_dir() {
    $upload_dir = $this->get_upload_dir(false, 'path');
    if(!is_dir($upload_dir)) { wp_mkdir_p( $upload_dir ); }

    $htaccess = path_join($upload_dir, '.htaccess');
    if(file_exists($htaccess)) { return; }
    $handle = fopen($htaccess, 'w');
    if($handle) {
      fwrite($handle, "Deny from all\n");
      fclose($handle);
    }
  }

  /**
   * Delete temp file
   *
   * @since    1.3.0
   */
  public function delete_temp_file($filename) {
    $directory = $this->get_upload_dir(true, 'path');
    $filepath = trailingslashit($directory) . $this->decode_filename('decrypt', $filename);
    wp_delete_file_from_directory($filepath, $directory);
  }

  /**
   * Cleanup temp directory
   *
   * @since    1.3.0
   */
  public function cleanup_temp_dir($expire=3600, $limit=99) {
    $directory = trailingslashit($this->get_upload_dir(true, 'path'));
    if(!is_dir($directory) || !is_readable($directory) || !wp_is_writable($directory)) { return; }
    $count = 0;

    if($handle = opendir($directory)) {
      while(false !== ($file = readdir($handle))) {
        $filepath = path_join($directory, $file);
        $mtime = @filemtime($filepath);
        if($mtime && time() < $mtime + $expire) { continue; }
        wp_delete_file($filepath);
        $count++;
        if($limit <= $count) { break; }
      }
      closedir($handle);
    }
  }

  /**
   * Encode/decode filename
   *
   * @since    1.3.0
   */
  public function decode_filename($action, $filename) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $token = get_option('formality_token');
    if(is_array($token) && count($token) == 3) {
      $secret_key = $token[0];
      $secret_iv = $token[1];
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);
      $output = $action=='encrypt' ?
                base64_encode(openssl_encrypt($filename, $encrypt_method, $key, 0, $iv)) :
                openssl_decrypt(base64_decode($filename), $encrypt_method, $key, 0, $iv);
    } else {
      $token = [ uniqid(mt_rand()), uniqid(mt_rand()), rand(999, time()) ];
      add_option( 'formality_token', $token, '', 'no' );
    }
    return $output;
  }

  /**
   * Temporary change upload dir
   *
   * @since    1.3.0
   */
  public function temporary_change_upload_dir($dir) {
    $subbase = '/formality/uploads';
    $dir['subdir'] = $subbase . '/temp';
    $dir['path'] = $dir['basedir'] . $dir['subdir'];
    $dir['url'] = $dir['baseurl'] . $dir['subdir'];
    return $dir;
  }

  /**
   * Upload temp file
   *
   * @since    1.3.0
   */
  public function upload_temp_file() {

    $nonce = isset($_POST['nonce']) ? sanitize_key($_POST['nonce']) : '';
    $postid = isset($_POST['id']) ? absint($_POST['id']) : 0;
    $fieldid = isset($_POST['field']) ? sanitize_key($_POST['field']) : 0;
    $old = isset($_POST['old']) ? sanitize_text_field($_POST['old']) : 0;
    $response = array("status" => 400, "field" => $fieldid);

    if (wp_verify_nonce( $nonce, 'formality_async' ) && !empty($_FILES)) {

      $this->create_upload_dir();
      $this->cleanup_temp_dir();
      if($old) { $this->delete_temp_file($old); }

      //get form and field informations
      $file = "";
      if($postid) {
        $args = array( 'post_type' => 'formality_form', 'posts_per_page' => 1, 'p' => $postid, );
        $the_query = new WP_Query( $args );
        if ($the_query->have_posts()) {
          while ( $the_query->have_posts() ) : $the_query->the_post();
            if(has_blocks()) {
              $blocks = parse_blocks(get_the_content());
              foreach ( $blocks as $block ) {
                $type = str_replace("formality/", "", $block['blockName']);
                if($type=="upload") {
                  $blockid = $block["attrs"]["uid"];
                  $field = "field_" . $blockid;
                  if(isset($_FILES[$field]) && $blockid == $fieldid) {
                    $file = $_FILES[$field];
                    //check extension
                    $valid_extensions = isset($block["attrs"]["formats"]) ? $block["attrs"]["formats"] : array('jpg', 'jpeg', 'gif', 'png', 'pdf');
                    $file_name = explode(".", $file["name"]);
                    $file_extension = strtolower(end($file_name));
                    if(!(in_array($file_extension, $valid_extensions))) { $response['errors'][] = "wrong extension"; }
                    //check size
                    $maxsize = intval(isset($block["attrs"]["maxsize"]) ? $block["attrs"]["maxsize"] : 3) * 1048576;
                    if($file["size"] > $maxsize) { $response['errors'][] = "max file size exceeded"; }
                    break;
                  }
                }
              }
            } else {
              $response['errors'][] = "no fields";
            }
          endwhile;
        } else {
          $response['errors'][] = "wrong form id";
        }
        wp_reset_query();
        wp_reset_postdata();
      } else {
        $response['errors'][] = "no form id provided";
      }

      //upload file
      if($file && !isset($response['errors'])) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        add_filter('upload_dir', [$this, 'temporary_change_upload_dir'], PHP_INT_MAX, 1);

        $file['name'] = substr(md5(microtime()),rand(0,26),6) . '_' . $file['name'];
        $uploaded_file = wp_handle_upload($file, array('test_form' => false));
        if(!isset($uploaded_file['error'])) {
          $response['status'] = 200;
          $response['file'] = $this->decode_filename('encrypt', basename($uploaded_file['url']));
        } else {
          $response['errors'][] = $uploaded_file['error'];
        }

        remove_filter('upload_dir', [$this, 'temporary_change_upload_dir'], PHP_INT_MAX, 1);
      } else {
        $response['errors'][] = "wrong file";
      }

    } else {
      //bad token
      $response["status"] = 300;
    }
    header('Content-type: application/json');
    echo json_encode($response);
    exit;
  }

  /**
   * Check if temp file exist
   *
   * @since    1.3.0
   */
  public function temp_exist($filename) {
    $directory = $this->get_upload_dir(true, 'path');
    $filepath = $directory . '/' . $this->decode_filename('decrypt', $filename);
    return file_exists($filepath) ? $filepath : false;
  }

  /**
   * Move temp file to final path
   *
   * @since    1.3.0
   */
  public function move_temp_file($temppath) {
    $finalpath = str_replace('/temp', '', $temppath);
    $directory = $this->get_upload_dir();
    $finalurl = str_replace($directory['path'], $directory['url'], $finalpath);
    return rename($temppath, $finalpath) ? $finalurl : false;
  }

}
