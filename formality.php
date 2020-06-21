<?php

/**
 * The Formality plugin bootstrap file
 *
 * @link              https://formality.dev
 * @since             1.0.0
 * @package           Formality
 *
 * @wordpress-plugin
 * Plugin Name:       Formality
 * Plugin URI:        https://formality.dev
 * Description:       Forms made simple (and cute). Designless, multistep, conversational, secure, all-in-one Wordpress forms plugin.
 * Version:           1.0.0
 * Author:            Michele Giorgi
 * Author URI:        https://giorgi.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       formality
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Currently plugin version.
 */
define( 'FORMALITY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 */
function activate_formality() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-formality-activator.php';
  Formality_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_formality() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-formality-deactivator.php';
  Formality_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_formality' );
register_deactivation_hook( __FILE__, 'deactivate_formality' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-formality.php';

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */
function run_formality() {
  $plugin = new Formality();
  $plugin->run();
}

run_formality();
