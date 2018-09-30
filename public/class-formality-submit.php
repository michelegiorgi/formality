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
		$postdata = $_POST;
		$current_sec  = time();
		$current_sec5 = $current_sec - 5;
		$token_sec = $this->decode_token('decrypt', $postdata["token"]);
		$response["status"] = 500;
		if(($token_sec<=$current_sec)&&($token_sec>$current_sec5)) {		
			$response["status"] = 200;
			$response["fields"] = $postdata;
		}
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}

}
