<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Works with simple values, without any additional functionality.
 * Mainly used within front-end form objects.
 *
 * @since 3.0
 */
class Values extends Datastore {
	/**
	 * Retrieve a single value from the database.
	 * For this datastore there is no database connection, so this is short-circuited.
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
	 * Returns the values from within the datastore.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_values() {
		return $this->values;
	}
}
