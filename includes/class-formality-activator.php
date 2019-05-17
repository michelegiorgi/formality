<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
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
 * @author     Your Name <email@example.com>
 */
class Formality_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
    $formality_token_key = get_option('formality_token_key');
    add_option( 'formality_flush', 1, '', 'yes' );
    if(!$formality_token_key) {
      add_option( 'formality_token_key', uniqid(mt_rand()), '', 'no' );
      add_option( 'formality_token_iv', uniqid(mt_rand()), '', 'no' );
      add_option( 'formality_token_offset', rand(999, time()), '', 'no' );
    }
	}

}
