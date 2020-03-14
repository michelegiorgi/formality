<?php

/**
 * Fired during plugin activation
 *
 * @link       https://formality.dev
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Formality
 * @subpackage Formality/includes
 * @author     Michele Giorgi <hi@giorgi.io>
 */
class Formality_Activator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
  	//check wp version
    $wp_now = get_bloginfo('version');
    $wp_min = 5.4;
    if($wp_now < $wp_min) {
      $message = '<h1 style="margin-top:-10px">' . __( 'Not so fast...', 'formality' ) . '</h1>';
      $message .= '<p>' . sprintf( __( 'Formality requires WordPress %s or higher!', 'formality' ), $wp_min) . '<br>';
      $message .= sprintf( __( 'Please <a href="%s">update your core</a> and retry the activation.', 'formality' ),  get_admin_url() . 'update-core.php') . '</p>';
      $message .= '<p><a href="'.get_admin_url().'plugins.php">' . __( 'Return to your dashboard', 'formality' ) . '</a></p>';
  	  wp_die($message);
    };
    
    //create token settings if not exists
    $formality_token_key = get_option('formality_token_key');
    add_option( 'formality_flush', 1, '', 'yes' );
    if(!$formality_token_key) {
      add_option( 'formality_token_key', uniqid(mt_rand()), '', 'no' );
      add_option( 'formality_token_iv', uniqid(mt_rand()), '', 'no' );
      add_option( 'formality_token_offset', rand(999, time()), '', 'no' );
    }
	}

}
