<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Lets the options contaner work with network options.
 *
 * @since 3.0
 */
class Network_Options extends Datastore {
    /**
	 * Retrieve a single value from the database.
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	function get_value_from_db( $key ) {
        $option = get_site_option( $key );

		return $option ? $option : false;
	}

	/**
	 * Saves values in the dabase. Might as well update existing ones
	 *
	 * @since 2.0
	 *
	 * @param string $key The key which the value is saved with
	 * @param mixed $value The value to be saved
	 */
	function save_value_in_db( $key, $value ) {
		return update_site_option( $key, $value );
	}

	/**
	 * Deletes values based on their key, no matter if they are multiple or not
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the setting
	 */
	function delete_value_from_db( $key ) {
		return delete_site_option( $key );
	}

	/**
	 * Commits the changes
	 */
	public function commit() {
		# Delete values
		foreach( $this->deleted as $key => $value ) {
			$this->delete_value_from_db( $key );
		}

		# Add/update the other ones
		foreach( $this->values as $key => $value ) {
			$this->save_value_in_db( $key, $value );
		}
	}

    /**
	 * Returns the options and keywords for the data API.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_data_api_options() {
		$options = array();

		$options[] = array(
			'type'    => 'network_option',
			'keyword' => 'network',
			'item'    => false
		);

		$options[] = array(
			'type'    => 'network_option',
			'keyword' => 'network_option',
			'item'    => false
		);

		return $options;
	}
}
