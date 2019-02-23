<?php
namespace Ultimate_Fields\UI\Settings;

use Ultimate_Fields\Template;
use Ultimate_Fields\UI\Migration;

/**
 * Handles the migration screen on the settings page.
 *
 * @since 3.0
 */
class Screen_Migration extends Screen {
	/**
	 * Returns the ID of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_id() {
		return 'migration';
	}

	/**
	 * Returns the title of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Migration', 'ultimate-fields' );
	}

	/**
	 * Displays the page.
	 *
	 * @since 3.0
	 */
	public function display() {
		$engine = Template::instance();

		$args = array(
			'url'        => $this->url,
			'containers' => get_posts( 'post_type=ultimatefields&order=asc&orderby=title&posts_per_page=-1' )
		);

		$engine->include_template( 'settings/migration.php', $args );
	}

	/**
	 * Loads the screen and adds action hooks if needed.
	 *
	 * @since 3.0
	 */
	public function load() {
		if( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] ) {
			$this->migrate();
		}
	}

	/**
	 * Performs the migration.
	 *
	 * @since 3.0
	 */
	public function migrate() {
		if( ! isset( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'uf-migrate-containers' ) ) {
			return;
		}

		if( ! isset( $_POST[ 'containers' ] ) || ! is_array( $_POST[ 'containers' ] ) || empty( $_POST[ 'containers' ] ) ) {
			return;
		}

		define( 'ULTIMATE_FIELDS_MIGRATING', true );
		Migration::instance()->migrate();
		exit;
	}
}