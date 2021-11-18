<?php

/**
 * Form submit functions
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/public
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Submit {

  private $formality;
  private $version;

  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
  }

  /**
   * Add routes to send message via WP REST API
   *
   * @since    1.0
   */
  public function api_endpoints() {
    register_rest_route( 'formality/v1', '/token/', array(
      'methods'  => 'POST',
      'callback' => [$this, 'token'],
      'permission_callback' => function () { return true; }
    ));
    register_rest_route( 'formality/v1', '/send/', array(
      'methods'  => 'POST',
      'callback' => [$this, 'send'],
      'permission_callback' => function () { return true; }
    ));
  }

  /**
   * Encode/decode token function
   *
   * @since    1.0
   */
  public function decode_token($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $token = get_option('formality_token');
    if(is_array($token) && count($token) == 3) {
      $secret_key = $token[0];
      $secret_iv = $token[1];
      $secret_offset = $token[2];

      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      if($action == 'encrypt') {
        $string = intval($string) + $secret_offset;
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
      } else {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        $output = intval($output) - $secret_offset;
      }
    } else {
      $token = [ uniqid(mt_rand()), uniqid(mt_rand()), rand(999, time()) ];
      add_option( 'formality_token', $token, '', 'no' );
    }
    return $output;
  }

  /**
   * Token generation function
   *
   * @since    1.0
   */
  public function token($request) {
    $params = $request->get_json_params();
    $nonce = isset($params['nonce']) ? sanitize_key($params['nonce']) : '';
    if (wp_verify_nonce( $nonce, 'formality_async' )) {
      $token = time();
      $response["status"] = 200;
      $response["token"] = $this->decode_token('encrypt', $token);
    } else {
      //bad token
      $response["status"] = 300;
    }
    header('Content-type: application/json');
    echo json_encode($response);
    exit;
  }

  /**
   * Form send function
   *
   * @since    1.0
   */
  public function send() {
    $current_sec  = time();
    $current_sec5 = $current_sec - 5;
    $token = isset($_POST["token"]) ? sanitize_text_field($_POST["token"]) : '';
    if($token) {
      $token_sec = $this->decode_token('decrypt', $token);
      if(($token_sec<=$current_sec)&&($token_sec>$current_sec5)) {
        $data = $this->validate();
        if(!isset($data['errors'])) {
          if(!($errors = $this->save($data))) {
            $response["status"] = 200;
          } else {
            //data saving errors
            $response["status"] = 300;
            $response["errors"] = $errors;
          }
        } else {
          //validation errors
          $response["status"] = 400;
          $response["errors"] = $data['errors'];
        }
      } else {
        //bad token
        $response["status"] = 500;
      }
    } else {
      //no token
      $response["status"] = 666;
    }
    header('Content-type: application/json');
    echo json_encode($response);
    exit;
  }

  /**
   * Data validation
   *
   * @since    1.0
   */
  public function validate() {
    $data = array();
    $postid = isset($_POST['id']) ? absint($_POST['id']) : 0;
    $upload = new Formality_Upload($this->formality, $this->version);
    if($postid) {
      $args = array(
        'post_type' => 'formality_form',
        'posts_per_page' => 1,
        'p' => $postid,
      );
      $the_query = new WP_Query( $args );
      if ($the_query->have_posts()) {
        $data['form']['id'] = $postid;
        $data['form']['title'] = get_the_title($postid);
        while ( $the_query->have_posts() ) : $the_query->the_post();
          $test = 0;
          if(has_blocks()) {
            $blocks = parse_blocks(get_the_content());
            foreach ( $blocks as $block ) {
              if($block['blockName']) {
                $type = str_replace("formality/","",$block['blockName']);
                $options = $block["attrs"];
                $isField = isset($options['uid']) && (!isset($options['exclude']));
                $hasRules = isset($options['rules'][0]['field']);
                $isRequired = isset($options['required']) && $options['required'];
                $test++;
                if($isField) {
                  $fieldname = "field_" . $options["uid"];
                  $fieldvalue = isset($_POST[$fieldname]) && $_POST[$fieldname] ? $_POST[$fieldname] : '';
                  if($fieldvalue) {
                    $sanitized =  "";
                    switch($type) {
                      case 'email':
                        $sanitized = sanitize_email($fieldvalue);
                        if(!is_email($sanitized)) { $data['errors'][] = "wrong email field " . $fieldname; }
                        break;
                      case 'textarea':
                        $sanitized = sanitize_textarea_field($fieldvalue);
                        break;
                      case 'multiple':
                        if(is_array($fieldvalue)) {
                          $sanitized = [];
                          foreach($fieldvalue as $subvalue) { $sanitized[] = sanitize_text_field($subvalue); }
                        } else {
                          $sanitized = sanitize_text_field($fieldvalue);
                        }
                        break;
                      case 'rating':
                      case 'switch':
                        $sanitized = absint($fieldvalue);
                        break;
                      case 'upload':
                        $filename = sanitize_text_field($fieldvalue);
                        $sanitized = $upload->temp_exist($filename);
                        if(!$sanitized) { $data['errors'][] = "temp file does not exist " . $fieldname; }
                        break;
                      default:
                        $sanitized = sanitize_text_field($fieldvalue);
                    }
                    $data['fields'][$fieldname]['value'] = $sanitized;
                    $data['fields'][$fieldname]['type'] = $type;
                  } else if($isRequired && !$hasRules) {
                    $data['errors'][] = "required field " . $fieldname;
                  }
                }
              }
            }
          }
        endwhile;
      } else {
        $data['errors'][] = "wrong form id";
      }
      wp_reset_query();
      wp_reset_postdata();
    } else {
      $data['errors'][] = "no form id";
    }
    do_action('formality_after_validation', $data);
    return apply_filters('formality_sanitized_data', $data);
  }

  /**
   * Save data to WP db
   *
   * @since    1.0
   */
  public function save($data) {
    $errors = false;
    $metas = [];
    $title = "";
    $upload = new Formality_Upload($this->formality, $this->version);

    //get form
    do_action('formality_before_save_data', $data);
    $form_id = $data['form']['id'];
    $form_title = $data['form']['title'];
    $metas["id"] = $form_id;

    //create or edit result form tax
    if(!($taxform = term_exists('form_' . $form_id, 'formality_tax'))) {
      $taxform = wp_insert_term( $form_title, 'formality_tax', array('slug' => 'form_' . $form_id ));
      if( is_wp_error( $taxform ) ) { $errors[] = $taxform->get_error_message(); }
    } else if($form_title !== get_term($taxform["term_id"])->name) {
      wp_update_term($taxform["term_id"], 'formality_tax', array('name' => $form_title));
    }

    //create fields array
    if(isset($data['fields']) && is_array($data['fields'])) {
      foreach ( $data['fields'] as $fieldname => $field ) {
        switch($field['type']) {
          case 'upload':
            $filepath = $upload->move_temp_file($field['value'], $form_id);
            if($filepath) {
              $metas[$fieldname] = $filepath;
            } else {
              $errors[] = 'file move error';
            }
            break;
          default:
            $metas[$fieldname] = $field['value'];
        }
        if(!$title) { $title = $field['value']; }
      }
    } else {
      $title = __('There is no data to save', 'formality');
    }

    //save result
    $result_data = array(
      'post_title' => stripslashes($title),
      'post_type' => 'formality_result',
      'post_status'  => 'unread',
      'meta_input'   => $metas
    );
    $result_id = wp_insert_post($result_data);

    //check record
    if(!is_wp_error($result_id)){
      wp_set_object_terms( $result_id, array(intval($taxform["term_id"])), 'formality_tax' );

      //send notification
      $to = get_post_meta($form_id, '_formality_email', true);
      if(is_email($to)) {
        $notifications = new Formality_Notifications($this->formality, $this->version);
        $notification_data['result_id'] = $result_id;
        $notification_data['form_id'] = $form_id;
        $notification_data['form_title'] = $form_title;
        do_action('formality_before_notification', $data);
        $notifications->email_send($to, $notification_data);
        do_action('formality_after_notification', $data);
      }
    } else {
      $errors[] = 'data save error';
    }

    do_action('formality_after_save_data', $data, $errors);
    return $errors;
  }

}
