<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Handles the values of a complex fields.
 *
 * Complex field can save values as sub-arrays or merge them with the upper-level data.
 * When the first mode (sub-arrays) is active, the complex field is using a generic datastore.
 * This datastore is only used when the complex field is in 'merge' mode and proxies data to the parent.
 *
 * @since 3.0
 */
class Complex extends Datastore {
	/**
	 * Holds the parent-level datastore.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Datastore
	 */
	protected $real_datastore;

	/**
	 * This is a prefix, controlled by the complex field, allowing multiple complex fields with
	 * the same sub-values, avoiding conflicts.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Sets the parent datastore, which will be used for actual data saving.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Datastore $datastore The datastore to use.
	 * @return Ultimate_Fields\Datastore\Complex
	 */
	public function set_datastore( $datastore ) {
		$this->real_datastore = $datastore;
		return $this;
	}

	/**
	 * Changes the prefix for merging data with the parent level.
	 *
	 * @since 3.0
	 * @see $prefix
	 *
	 * @param string $prefix The prefix to use.
	 * @return Ultimate_Fields\Datastore\Complex The instance of the datastore.
	 */
	public function set_prefix( $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Retrieve a single value from the database.
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	public function get_value_from_db( $key ) {
		return $this->real_datastore->get( $this->prefix . $key );
	}

	/**
	 * Returns all of the values held within the datastore.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_values() {
		return $this->values;
	}

	/**
	 * Saves the values of the datastore into the parent level.
	 *
	 * @since 3.0
	 */
	public function save() {
		foreach( $this->get_values() as $key => $value ) {
			$this->real_datastore->set( $this->prefix . $key, $value );
		}
	}
}
