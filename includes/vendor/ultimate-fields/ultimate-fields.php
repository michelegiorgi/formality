<?php
/**
 * Plugin name: Ultimate Fields
 * Version:     3.1
 * Plugin URI:  https://www.ultimate-fields.com/
 * Author:      Radoslav Georgiev
 * Author URI:  http://rageorgiev.com/
 * Copyright:   Radoslav Georgiev
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain path: /languages
 * Text Domain: ultimate-fields
 * Description: Ultimate Fields is a plugin, that allows you to add custom fields in many places throughout the WordPress administration area, supporting a total of more than 30 field types, including repeaters, layouts and etc.
 * Requires at least: 4.9
 */

/**
 * Loads the files of the plugin.
 *
 * @since 3.0
 */
add_action( 'plugins_loaded', 'load_ultimate_fields', 9 );
function load_ultimate_fields() {
	// Check if Ultimate Fields Pro (priority 8) has already been loaded
	if( function_exists( 'ultimate_fields' ) ) {
		return;
	}

	define( 'ULTIMATE_FIELDS_PLUGIN_FILE', __FILE__ );
	define( 'ULTIMATE_FIELDS_LANGUAGES_DIR', basename( __DIR__ ) . '/languages/' );

	require_once( 'core/ultimate-fields.php' );
	require_once( 'ui/ultimate-fields-ui.php' );
}
