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
		$response["status"] = 500;
		if(($token_sec<=$current_sec)&&($token_sec>$current_sec5)) {		
			$response["status"] = 200;
			$response["fields"] = $_POST;
		}
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
	public function validate() {
		$postdata = $_POST;
		$filedata = $_FILES;
		if(isset($postdata['formality-id'])) {
			$form_id = $postdata['formality-id'];
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
					  		$fieldname = get_sub_field('name');
	  						//check required fields
					  		if( get_row_layout() == 'file' ) {
						  		if(get_sub_field('required')) {
							  		if(!(isset($filedata[$fieldname]))) {
								  		$errors = 1;
							  		}
						  		}
						  		if(isset($filedata[$fieldname])) {
							  		$size = get_sub_field('max_size');	
							  		if($size) {
								  		$size = $size * 1048576;
								  		if($filedata[$fieldname]["size"] > $size) {
									  		$errors = 1;
								  		}
							  		}
							  		$formats = get_sub_field('formats');	
							  		if($formats) {
								  		$validextensions = explode(", ", $formats);
											$temporary = explode(".", $filedata[$fieldname]["name"]);
											$file_extension = end($temporary);
											if(!(in_array($file_extension, $validextensions))) {
												$errors = 1;
											}
							  		}
							  		if(isset($filedata[$fieldname]["type"])) {
								  	}
							  		if ($filedata[$fieldname]["error"] > 0) {
										}
						  		}
					  		} else if(get_sub_field('required')) {
					  			if(!(isset($postdata[$fieldname]))) {
					  				error_log( $fieldname . " empty");
					  				$errors = 1;
					  			}
						  		//regexpo if is email
						  		if( get_row_layout() == 'email' ) {
							  		if (filter_var($postdata[$fieldname], FILTER_VALIDATE_EMAIL)) {
										  //error_log( "valid");
										} else {
											error_log( "invalid mail");
											$errors = 1;
										}
						  		}
					  		}
							endwhile;
						endif;
						
				endwhile;
			} else {
				$errors = 1;
			}
			wp_reset_query();
			wp_reset_postdata();	
		} else {
			$errors = 1;
		}		
		foreach($postdata as $key => $value) {
			if($value != strip_tags($value)) {
	    	$value = "";
	    	$errors = 1;
			} else {
				$value = trim($value);
			  $value = htmlspecialchars($value);
			}
			$postdata[$key] = $value;
		}
	}

}
