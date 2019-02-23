<?php
namespace Ultimate_Fields\UI\Settings;

use Ultimate_Fields\Container;
use Ultimate_Fields\Options_Page;

/**
 * Handles the general settings of the plugin.
 *
 * @since 3.0
 */
class Screen_General extends Screen {
	/**
	 * Holds the fields, which will be used for the settings page.
	 *
	 * @since 3.0
	 *
	 * @var Ultimate_Fields\Field[]
	 */
	protected $fields;

	/**
	 * Returns the ID of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_id() {
		return 'general';
	}

	/**
	 * Returns the title of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Settings', 'ultimate-fields' );
	}

	/**
	 * Checks if the screen is available at all.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_available() {
		/**
		 * Allows the fields of the settings page to be modified.
		 *
		 * If no fields are found, the whole screen will be ignored.
		 *
		 * @since 3.0
		 *
		 * @param array $fields An empty array of fields.
		 * @return array
		 */
		$this->fields = apply_filters( 'uf.settings.fields', array() );

		return ! empty( $this->fields );
	}

	/**
	 * Loads the screen when needed.
	 *
	 * For this screen, the method will create an options page and add a container to it.
	 *
	 * @since 3.0
	 */
	public function load() {
		# Create an options page
		$this->page = Options_Page::create( 'uf-plugin-settings', __( 'Settings', 'ultimate-fields' ) );
		$this->page->set_screen_id( get_current_screen()->id );

		# Remove the page from the menu
		remove_action( 'admin_menu', array( $this->page, 'add_to_menu' ) );

		# Remove the screen options
		add_filter( 'screen_options_show_screen', '__return_false' );
		add_filter( 'uf.options_page.redirect_url', array( $this, 'fix_redirect' ), 10, 2 );

		# Create a container for the setting
		Container::create( 'uf-plugin-settings' )
			->set_title( __( 'Settings', 'ultimate-fields' ) )
			->add_location( 'options', $this->page )
			->add_fields( $this->fields );

		$this->page->load();
	}

	/**
	 * Displays the screen.
	 *
	 * @since 3.0
	 */
	public function display() {
		$this->page->display();
	}

	/**
	 * Fixes the redirect URL of the options page.
	 *
	 * @since 3.0
	 *
	 * @param string $url The pre-determined URL.
	 * @param Options_Page $page
	 * @return string
	 */
	public function fix_redirect( $url, $page ) {
		$url = admin_url( 'edit.php?post_type=ultimate-fields&page=settings' );
		return $url;
	}
}
