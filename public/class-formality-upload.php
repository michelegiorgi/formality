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
    $subbase = 'formality/storage';
    $upload = wp_upload_dir();
    $dir['sub'] = $temp ? path_join($subbase, $temp === true ? 'temp' : $temp) : $subbase;
    $dir['path'] = path_join($upload['basedir'], $dir['sub']);
    $dir['url'] = path_join($upload['baseurl'], $dir['sub']);
    $dir['htaccess'] = path_join($upload['baseurl'], '.htaccess');
    $dir['downloader'] = path_join($upload['baseurl'], 'download.php');
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

    $downloadfile = path_join($upload_dir, 'download.php');
    if(!file_exists($downloadfile)) {
      copy(FORMALITY_PATH . 'includes/tools/download.php', $downloadfile);
    }

    $htaccess = path_join($upload_dir, '.htaccess');
    if(file_exists($htaccess)) { return; }
    $handle = fopen($htaccess, 'w');
    if($handle) {
      fwrite($handle, '# FORMALITY UPLOADS HTACCESS' . PHP_EOL);
      fwrite($handle, '<IfModule mod_rewrite.c>' . PHP_EOL);
      fwrite($handle, 'RewriteEngine On' . PHP_EOL);
      fwrite($handle, 'RewriteCond %{REQUEST_FILENAME} -s' . PHP_EOL);
      fwrite($handle, 'RewriteRule ^(.*)$ download.php?file=$1&wproot=' . ABSPATH . ' [QSA,L]' . PHP_EOL);
      fwrite($handle, '</IfModule>' . PHP_EOL);
      fclose($handle);
    }
  }

  /**
   * Cleanup temp directory
   *
   * @since    1.3.0
   */
  public function cleanup_temp_dir($oldfile=false) {
    $directory = trailingslashit($this->get_upload_dir(true, 'path'));
    $oldfile = $oldfile ? $this->decode_filename('decrypt', $oldfile) : false;
    $expire = 3600;
    $limit = 99;
    $count = 0;
    if(!is_dir($directory) || !is_readable($directory) || !wp_is_writable($directory)) { return; }

    if($handle = opendir($directory)) {
      while(false !== ($file = readdir($handle))) {
        if(in_array($file, array(".", ".."))) { continue; }
        $filepath = path_join($directory, $file);
        $mtime = @filemtime($filepath);
        if($oldfile == $file || ($mtime && time() > $mtime + $expire)) {
          wp_delete_file($filepath);
          $count++;
          if($limit <= $count) { break; }
        }
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
    $subbase = 'formality/storage';
    $dir['subdir'] = path_join($subbase, 'temp');
    $dir['path'] = path_join($dir['basedir'], $dir['subdir']);
    $dir['url'] = path_join($dir['baseurl'], $dir['subdir']);
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
      $this->cleanup_temp_dir($old);

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
    $file = $this->decode_filename('decrypt', $filename);
    $filepath = path_join($directory, $file);
    return file_exists($filepath) ? $file : false;
  }

  /**
   * Move temp file to final path
   *
   * @since    1.3.0
   */
  public function move_temp_file($tempname, $formid) {
    $directory = $this->get_upload_dir();
    $temppath = path_join($directory['path'], 'temp');
    $finalpath = path_join($directory['path'], 'form-' . strval($formid));
    if(!is_dir($finalpath)) { wp_mkdir_p( $finalpath ); }
    $finalname = wp_unique_filename($directory['path'], $tempname);
    $finalurl = $directory['url'] . '/form-' . strval($formid) . '/' . $finalname;
    return rename(path_join($temppath, $tempname), path_join($finalpath, $finalname)) ? $finalurl : false;
  }

}
