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
	
	public function decode_token($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'bs3au11ydvs26';
    $secret_iv = 'hjsdfk6s5aqg6s';
 
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
    if ( ! wp_verify_nonce( $_POST['nonce'], 'formality_ajax' ) ) die ( 'Busted!');
    $token = time();
		$response["token"] = $this->decode_token('encrypt', $token);
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
	public function send() {
		$current_sec  = time();
		$current_sec5 = $current_sec - 5;
		$token_sec = $this->decode_token('decrypt', $_POST["token"]);
		if(($token_sec<=$current_sec)&&($token_sec>$current_sec5)) {
			$postdata = $_POST;
			$filedata = $_FILES;
			if(!($errors = $this->validate($postdata, $filedata))) {
				if(!($errors = $this->save($postdata, $filedata))) {
					$response["status"] = 200;
					$response["fields"] = $postdata;
				} else {
					//validation errors
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
	
					  if( have_rows('formality_fields') ):
					  	while ( have_rows('formality_fields') ) : the_row();
					  		$fieldname = "field_" . get_sub_field('name');

					  		if( get_row_layout() == 'file' ) {
						  		if(get_sub_field('required')) {
							  		if(!(isset($filedata[$fieldname]))) {
								  		$errors[$fieldname] = "no file attached";
							  		}
						  		}
						  		if(isset($filedata[$fieldname])) {
							  		$size = get_sub_field('max_size');	
							  		if($size) {
								  		$size = $size * 1048576;
								  		if($filedata[$fieldname]["size"] > $size) {
									  		$errors[$fieldname] = "file size exceeded limit";
								  		}
							  		}
							  		$formats = get_sub_field('formats');	
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
					  		} else if(get_sub_field('required')) {
					  			if(!(isset($postdata[$fieldname]))) {
					  				$errors[$fieldname] = "required field";
					  			} else if(!$postdata[$fieldname]) {
						  			$errors[$fieldname] = "required field";
					  			}
						  		if( get_row_layout() == 'email' ) {
							  		if (filter_var($postdata[$fieldname], FILTER_VALIDATE_EMAIL)) {
										  //error_log( "valid");
										} else {
											$errors[$fieldname] = "wrong email";
										}
						  		}
					  		}
							endwhile;
						endif;
						
				endwhile;
			} else {
				$errors["formality"] = "wrong form id";
			}
			wp_reset_query();
			wp_reset_postdata();	
		} else {
			$errors["formality"] = "no form id";
		}
		//check code inject
		foreach($postdata as $key => $value) {
			if($value != strip_tags($value)) {
	    	$errors[$key] = "code injection detected";
			}
		}
		return $errors;
	}
	
	public function save($postdata, $filedata) {
		$errors = false;
		$metas = [];		
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
		  if( have_rows('formality_fields') ):
		  	while ( have_rows('formality_fields') ) : the_row();
		  		$fieldname = "field_" . get_sub_field('name');
					$metas[$fieldname] = $postdata[$fieldname];
				endwhile;
			endif;
		endwhile;
		wp_reset_query();
		wp_reset_postdata();
		
		$result_data = array(
			'post_title' => stripslashes($postdata['field_firstname']),
			'post_type' => 'formality_result',
			'post_status'  => 'unread',
			'meta_input'   => $metas
		);
		$result_id = wp_insert_post($result_data);
		wp_set_object_terms( $result_id, array(intval($taxform["term_id"])), 'formality_tax' );
		foreach($postdata as $key => $value) {
			
		}
		return $errors;
	}

}
