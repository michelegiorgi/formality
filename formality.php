<?php

/**
 * The Formality plugin bootstrap file
 *
 * @link              https://formality.dev
 * @since             1.0
 * @package           Formality
 * @copyright         Copyright (C) 2018-2020, Michele Giorgi
 *
 * @wordpress-plugin
 * Plugin Name:       Formality
 * Plugin URI:        https://formality.dev
 * Description:       Forms made simple (and cute). Designless, multistep, conversational, secure, all-in-one WordPress forms plugin.
 * Version:           1.0.5
 * Author:            Michele Giorgi
 * Author URI:        https://giorgi.io
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       formality
 * Domain Path:       /languages
 *
 * Formality is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Formality is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/gpl-3.0.txt.
 *
 * This program incorporates work covered by the following copyright:
 *
 * Parsley.js - MIT | Copyright (c) 2013-2020 Guillaume Potier, Marc-André Lafortune and contributors | https://github.com/guillaumepotier/Parsley.js
 * Emergence.js - MIT | (c) 2017 @xtianmiller | https://github.com/xtianmiller/emergence.js
 * React Sortable HOC - MIT | Copyright (c) 2016, Claudéric Demers | https://github.com/clauderic/react-sortable-hoc
 * clone-deep - MIT | Copyright © 2018, Jon Schlinkert | https://github.com/jonschlinkert/clone-deep
 * Hanken Grotesk - SIL | Copyright 2020 The Hanken Grotesk Project Authors, with Font Name "Hanken Grotesk". | https://github.com/marcologous/hanken-grotesk
 * Material design Icons - APACHE 2.0 | Copyright (c) 2014-2020 Google, Inc. | https://github.com/google/material-design-icons
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Currently plugin version.
 */
define( 'FORMALITY_VERSION', '1.0.5' );

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
