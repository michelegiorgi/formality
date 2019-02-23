<?php
namespace Ultimate_Fields\Location;

/**
 * A trait that is shared between all locations, which can be shown in
 * the customizer, containing the necessary interface and helper functions.
 *
 * @since 3.0
 */
trait Customizable {
	/**
	 * The priority of the container in the customizer.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $customizer_priority = 150;

	/**
	 * Indicates if the location should be included in the customizer.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $customizer = false;

	/**
	 * Holds names of the fields, which would sent through the postMessage method through the customizer.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $postmessage_fields = array();

	/**
	 * Checks if initialization arguments include the customizer.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $arguments The arguments to check.
	 */
	protected function check_args_for_customizer( & $args ) {
		if( isset( $args[ 'show_in_customizer' ] ) && $args[ 'show_in_customizer' ] ) {
			$this->show_in_customizer();
			unset( $args[ 'show_in_customizer' ] );
		}

		$this->set_and_unset( $args, array(
			'postmessage_fields' => 'set_dynamic_fields'
		));
	}

	/**
	 * A getter for the priority of the container.
	 *
	 * @since 3.0
	 * @return int
	 */
	public function get_customizer_priority() {
		return $this->customizer_priority;
	}

	/**
	 * A setter for the priority of the container in the customizer.
	 *
	 * @since 3.0
	 *
	 * @param int $priority The priority.
	 * @return Ultimate_Fields\Location
	 */
	public function set_customizer_priority( $priority ) {
		$this->customizer_priority = $priority;

		return $this;
	}

	/**
	 * Adds the location to the customizer.
	 *
	 * Please note that this might be ignored based on the particular location class - shortcodes,
	 * for example, cannot and will be not displayed in the customizer.
	 *
	 * @since 3.0
	 *
	 * @return Location The instnace of the location.
	 */
	public function show_in_customizer() {
		$this->customizer = true;

		return $this;
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

	/**
	 * Allows certain fields to be refreshed dynamically on the page.
	 *
	 * @since 3.0
	 *
	 * @param string[] $names The names of the fields to refresh dynamically.
	 * @return Location
	 */
	public function set_dynamic_fields( $names ) {
		foreach( (array) $names as $name ) {
			$this->postmessage_fields[] = $name;
		}

		return $this;
	}

	/**
	 * Returns the names of the fields, which should use the postMessage transport.
	 *
	 * @since 3.0
	 * @return string[]
	 */
	public function get_dynamic_fields() {
		return $this->postmessage_fields;
	}

	/**
	 * Changes an array of exportable (JSON) information to include customizer data.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $settings The settings where to include data.
	 */
	public function export_customizable_data( & $settings ) {
		if( ! $this->customizer ) {
			return;
		}

		$settings[ 'show_in_customizer' ] = true;
		if( ! empty( $this->postmessage_fields ) ) {
			$settings[ 'postmessage_fields' ] = $this->postmessage_fields;
		}
	}

	/**
	 * Imports column data (mainly from JSON).
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The data to check for column.
	 */
	protected function import_customizable_data( $args ) {
		if( ! isset( $args[ 'show_in_customizer' ] ) ) {
			return;
		}

		$this->show_in_customizer();

		if( isset( $args[ 'postmessage_fields' ] ) )
			$this->set_dynamic_fields( $args[ 'postmessage_fields' ] );
	}
}
