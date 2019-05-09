<?php

/**
 * Form submit functions
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/public
 */

class Formality_Submit {

	private $formality;
	private $version;

	public function __construct( $formality, $version ) {
		$this->formality = $formality;
		$this->version = $version;
	}
	
	public function rest_api() {
    register_rest_route( 'formality/v1', '/token/', array(
      'methods'  => 'POST',
      'callback' => [$this, 'token']
    ));
  	register_rest_route( 'formality/v1', '/send/', array(
      'methods'  => 'POST',
      'callback' => [$this, 'send']
    ));
	}
	
	public function decode_token($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = get_option('formality_token_key');
    $secret_iv = get_option('formality_token_iv');
 
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
 
    if( $action == 'encrypt' ) {
      $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
      $output = base64_encode($output);
    } else if( $action == 'decrypt' ){
      $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
	}

	public function token() {
    if (wp_verify_nonce( $_POST['nonce'], 'formality_async' )) {
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
	
	public function send() {
		$current_sec  = time();
		$current_sec5 = $current_sec - 5;
		if(isset($_POST["token"])) {
  		$token_sec = $this->decode_token('decrypt', $_POST["token"]);
  		if(($token_sec<=$current_sec)&&($token_sec>$current_sec5)) {
  			$postdata = $_POST;
  			$filedata = $_FILES;
  			if(!($errors = $this->validate($postdata, $filedata))) {
  				if(!($errors = $this->save($postdata, $filedata))) {
  					$response["status"] = 200;
  					$response["fields"] = $postdata;
  				} else {
  					//data saving errors
  					$response["status"] = 300;
  					$response["errors"] = $errors;
  				}
  			} else {
  				//validation errors
  				$response["status"] = 400;
  				$response["errors"] = $errors;
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
	
	public function validate($postdata, $filedata) {
		$errors = false;
		if(isset($postdata['id'])) {
			$form_id = $postdata['id'];
			$args = array(
				'post_type' => 'formality_form',
				'posts_per_page' => 1,
				'p' => $form_id,
			);
			$the_query = new WP_Query( $args );
			if ($the_query->have_posts()) {
				while ( $the_query->have_posts() ) : $the_query->the_post();
          $test = 0;
          if(has_blocks()) {
            $blocks = parse_blocks(get_the_content());
            foreach ( $blocks as $block ) {
              if($block['blockName']) {
                $type = str_replace("formality/","",$block['blockName']);
                $options = $block["attrs"];
  				  		$fieldname = "field_" . $options["uid"];
                $test++;
  				  		if( $type == 'file' ) {
  					  		if($options['required']) {
  						  		if(!(isset($filedata[$fieldname]))) {
  							  		$errors[$fieldname] = "no file attached";
  						  		}
  					  		}
  					  		if(isset($filedata[$fieldname])) {
  						  		$size = $options['max_size'];	
  						  		if($size) {
  							  		$size = $size * 1048576;
  							  		if($filedata[$fieldname]["size"] > $size) {
  								  		$errors[$fieldname] = "file size exceeded limit";
  							  		}
  						  		}
  						  		$formats = $options['formats'];	
  						  		if($formats) {
  							  		$validextensions = explode(", ", $formats);
  										$temporary = explode(".", $filedata[$fieldname]["name"]);
  										$file_extension = end($temporary);
  										if(!(in_array($file_extension, $validextensions))) {
  											$errors[$fieldname] = "wrong file format";
  										}
  						  		}
  						  		if(isset($filedata[$fieldname]["type"])) {
  							  	}
  						  		if ($filedata[$fieldname]["error"] > 0) {
  									}
  					  		}
  				  		} else if($options['required']) {
  				  			if(!(isset($postdata[$fieldname]))) {
  				  				$errors[$fieldname] = "required field" . $test;
  				  			} else if(!$postdata[$fieldname]) {
  					  			$errors[$fieldname] = "required field";
  				  			}
  					  		if( $type == 'email' ) {
  						  		if (filter_var($postdata[$fieldname], FILTER_VALIDATE_EMAIL)) {
  									  //error_log( "valid");
  									} else {
  										$errors[$fieldname] = "wrong email";
  									}
  					  		}
  				  		}
  				  	}
  					}
          }
				endwhile;
			} else {
				$errors["formality"] = "wrong form id";
			}
			wp_reset_query();
			wp_reset_postdata();	
		} else {
			$errors["formality"] = "no form id";
		}
		return $errors;
	}
	
	public function save($postdata, $filedata) {
		$errors = false;
		$metas = [];
		$title = "";		
		$args = array(
			'post_type' => 'formality_form',
			'posts_per_page' => 1,
			'p' => $postdata['id'],
		);
		$metas["id"] = $postdata['id'];
		$the_query = new WP_Query( $args );
		while ( $the_query->have_posts() ) : $the_query->the_post();
			if(!($taxform = term_exists('form_' . $postdata['id'], 'formality_tax'))) {
				$taxform = wp_insert_term( get_the_title(), 'formality_tax', array('slug' => 'form_' . $postdata['id'] ));
			}
			if(has_blocks()) {
        $blocks = parse_blocks(get_the_content());
        foreach ( $blocks as $block ) {
          if($block['blockName']) {
            $fieldname = "field_" . $block["attrs"]["uid"];
		  			if(isset($postdata[$fieldname])&&$postdata[$fieldname]) {
						  $metas[$fieldname] = $postdata[$fieldname];
              if(!$title) { $title = $postdata[$fieldname]; }
						}
          }
        }
      }
		endwhile;
		wp_reset_query();
		wp_reset_postdata();
		
		$result_data = array(
			'post_title' => stripslashes($title),
			'post_type' => 'formality_result',
			'post_status'  => 'unread',
			'meta_input'   => $metas
		);
		$result_id = wp_insert_post($result_data);
		wp_set_object_terms( $result_id, array(intval($taxform["term_id"])), 'formality_tax' );
		return $errors;
	}

}
