<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/includes
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_i18n {


  /**
   * Load the plugin text domain for translation.
   *
   * @since    1.0
   */
  public function load_plugin_textdomain() {

    load_plugin_textdomain(
      'formality',
      false,
      dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
    );

  }



}
