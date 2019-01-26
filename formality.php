<?php

/**
 * The Formality plugin bootstrap file
 *
 * @link              http://giorgi.io
 * @since             0.1.0
 * @package           Formality
 *
 * @wordpress-plugin
 * Plugin Name:       Formality
 * Plugin URI:        http://giorgi.io
 * Description:       Form manage made simple.
 * Version:           0.1.9
 * Author:            Giorgi
 * Author URI:        http://giorgi.io/
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
define( 'FORMALITY_VERSION', '0.1.9' );

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
