<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Location\Customizable;
use Ultimate_Fields\Controller\Customizer as Controller;

/**
 * Works as a location definition for containers within the customizer.
 *
 * @since 3.0
 */
class Customizer extends Options {
	use Customizable;

	/**
	 * Initializes the location.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args An array-based list of arguments for the location.
	 */
	public function __construct( $args = array() ) {
		$this->customizer = true;
		$this->check_args_for_customizer( $args );
		parent::__construct( $args );
	}

	/**
	 * Returns an instance of the controller, which controls the location (menu items).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Customizer
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Returns the settings for the location, which will be exported.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();
		$settings[ 'priority' ] = $this->get_customizer_priority();

		# Export customizable data
		if( ! empty( $this->postmessage_fields ) ) {
			$settings[ 'postmessage_fields' ] = $this->postmessage_fields;
		}

		return $settings;
	}

	/**
	 * Imports the location from PHP/JSON.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The arguments to import.
	 */
	public function import( $args ) {
		if( isset( $args[ 'priority' ] ) ) {
			$this->set_customizer_priority( $args[ 'priority' ] );
		}

		# Check for dynamic fields
		if( isset( $args[ 'postmessage_fields' ] ) )
			$this->set_dynamic_fields( $args[ 'postmessage_fields' ] );
	}

	/**
	 * Indicates if the location is available in the customizer.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_shown_in_customizer() {
		return $this->customizer;
	}
}
