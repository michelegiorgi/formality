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

    $response = array("status" => 400);
    $nonce = isset($_POST['nonce']) ? sanitize_key($_POST['nonce']) : '';

    if (wp_verify_nonce( $nonce, 'formality_async' ) && !empty($_FILES)) {

      //get form and field informations
      $postid = isset($_POST['id']) ? absint($_POST['id']) : 0;
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
                  $field = "field_" . $block["attrs"]["uid"];
                  if(isset($_FILES[$field])) {
                    $file = $_FILES[$field];
                    //check extension
                    $valid_extensions = isset($block["attrs"]["formats"]) ? $block["attrs"]["formats"] : array('jpg', 'jpeg', 'gif', 'png', 'pdf');
                    $file_name = explode(".", $file["name"]);
                    $file_extension = end($file_name);
                    if(!(in_array($file_extension, $valid_extensions))) { $response['errors'][] = "wrong extension"; }
                    //check size
                    $maxsize = intval(isset($block["attrs"]["maxsize"]) ? $block["attrs"]["maxsize"] : 3) * 1048576;
                    if($file["size"] > $maxsize) { $response['errors'][] = "max file size exceeded"; }
                    break;
                  }
                }
              }
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

        add_filter('upload_dir', 'formality_change_upload_dir');

        function formality_change_upload_dir($dir) {
          $dir['subdir'] = '/formality/uploads/temp';
          $dir['path'] = $dir['basedir'] . '/formality/uploads/temp';
          $dir['url'] = $dir['baseurl'] . '/formality/uploads/temp';
          return $dir;
        }

        $uploaded_file = wp_handle_upload($file, array('test_form' => false));
        if (!isset($uploaded_file['error'])) {
          $response["status"] = 200;
          $response["file"] = basename($uploaded_file['url']);
        } else {
          $response['errors'][] = $uploaded_file['error'];
        }

        remove_filter('upload_dir', 'formality_change_upload_dir');
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

}
