<?php
namespace Ultimate_Fields\UI\Settings;

use Ultimate_Fields\Template;

/**
 * Handles the about screen on the settings page.
 *
 * @since 3.0
 */
class Screen_About extends Screen {
	/**
	 * Returns the ID of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_id() {
		return 'about';
	}

	/**
	 * Returns the title of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_title() {
		return __( 'About', 'ultimate-fields' );
	}

	/**
	 * Displays the page.
	 *
	 * @since 3.0
	 */
	public function display() {
		$engine = Template::instance();
		$engine->include_template( 'settings/welcome.php' );
	}
}