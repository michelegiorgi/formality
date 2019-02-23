<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Handles the values of (not only) repeater groups.
 *
 * @since 3.0
 */
class Group extends Datastore {
	/**
	 * Retrieve a single value from the database.
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	function get_value_from_db( $key ) {
		return null;
	}

	/**
	 * Returns all of the values, which exist within the group.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_values() {
		return $this->values;
	}
}
