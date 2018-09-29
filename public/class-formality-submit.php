<?php

/**
 * Form rendering functions
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
		
	public function token() { 
    if ( ! wp_verify_nonce( $_POST['nonce'], 'formality_ajax' ) ) die ( 'Busted!');
	}
	
	public function send() {
		$response["test"] = "dsadsa";
		$response["status"] = 1;
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}

}
