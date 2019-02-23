<?php
namespace Ultimate_Fields\Container;

use Ultimate_Fields\Container\Repeater_Group;

/**
 * Handles the groups in the layout field.
 *
 * @since 3.0
 */
class Layout_Group extends Repeater_Group {
	/**
	 * Contains the minimum width of the group.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $min_width = 0;

	/**
	 * Contains the maximum width of the group.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $max_width = 0;

	/**
	 * Initializes basic class properties.
	 *
	 * @since 3.0
	 *
	 * @param string  $id   The ID/type of the group.
	 * @param mixed[] $args Arguments for the group.
	 */
	public function __construct( $id, $args = array() ) {
		parent::__construct( $id, $args );

		# Check for widths
		if( isset( $args[ 'min_width' ] ) ) $this->set_min_width( $args[ 'min_width' ] );
		if( isset( $args[ 'max_width' ] ) ) $this->set_max_width( $args[ 'max_width' ] );
	}

	/**
	 * Exports the settings of the group.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_settings() {
		$settings = parent::export_settings();

		# Force popup editing mode
		$settings[ 'edit_mode' ] = 'popup';

		# Add widths
		$settings[ 'min_width' ] = $this->min_width;
		$settings[ 'max_width' ] = $this->max_width;

		return $settings;
	}

	/**
	 * Exports data about the group.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
        $data = parent::export_data();

        $data[ '__width' ] = $this->datastore->get( '__width' );

        return $data;
	}

	/**
	 * Saves the group.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data to save from, similar to the structure of a _POST array.
	 * @return mixed[] An array of errors.
	 */
	public function save( $data ) {
		$errors = parent::save( $data );

		$this->datastore->set( '__width', intval( $data[ '__width' ] ) );

		return $errors;
	}

	/**
	 * Allows the minimum width of the group to be changed.
	 *
	 * @since 3.0
	 *
	 * @param int $min_width The new width.
	 * @return Ultimate_Fields\Container\Layout_Group The instance of the group.
	 */
	public function set_min_width( $min_width ) {
		$this->min_width = $min_width;

		return $this;
	}

	/**
	 * Returns the miniumum width of the group.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_min_width() {
		return $this->min_width;
	}

	/**
	 * Allows the maximum width of the group to be changed.
	 *
	 * @since 3.0
	 *
	 * @param int $max_width The new width.
	 * @return Ultimate_Fields\Container\Layout_Group The instance of the group.
	 */
	public function set_max_width( $max_width ) {
		$this->max_width = $max_width;

		return $this;
	}

	/**
	 * Returns the maxiumum width of the group.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_max_width() {
		return $this->max_width;
	}

	/**
	 * Generates an array, which can be exported to both PHP and JSON.
	 *
	 * @since 3.0
	 *
	 * @return mixed[] The JSON-ready data.
	 */
	public function export() {
		$settings = parent::export();

		# Export the other generic properties
		$this->export_properties( $settings, array(
			'min_width' => array( 'minimum_width', 0 ),
			'max_width' => array( 'maximum_width', 0 )
		));

		return $settings;
	}
}
