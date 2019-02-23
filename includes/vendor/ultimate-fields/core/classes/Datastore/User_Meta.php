<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Connects user containers with the user meta API.
 *
 * @since 3.0
 */
class User_Meta extends Datastore {
	protected $user_id = null;

    /**
	 * Retrieve a single value from the database.
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	function get_value_from_db( $key ) {
		if( ! metadata_exists( 'user', $this->user_id, $key ) ) {
			return null;
		}

		return get_user_meta( $this->user_id, $key, true );
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
		return update_user_meta( $this->user_id, $key, $value );
	}

	/**
	 * Deletes values based on their key, no matter if they are multiple or not
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the setting
	 */
	function delete_value_from_db( $key ) {
		return delete_user_meta( $this->user_id, $key, true );
	}

	/**
	 * Commits the changes
	 *
	 * @since 3.0
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
	 * Sets the user ID to work with.
	 *
	 * @since 3.0
	 *
	 * @param int $id The ID of the user.
	 */
	public function set_id( $id ) {
		$this->user_id = $id;
	}

	/**
	 * Returns the ID of the user the datastore gets meta for.
	 *
	 * @since 3.0
	 * @return int
	 */
	public function get_id() {
		return $this->user_id;
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

		# This option will select the current user
		$options[] = array(
			'type'    => 'user_meta',
			'keyword' => 'user',
			'item'    => false
		);

		# This option will load a specific user
		$options[] = array(
			'type'    => 'user_meta',
			'keyword' => 'user',
			'item'    => true
		);

		return $options;
	}

	/**
	 * Loads the current user for the no-item combination.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public static function load_anonymous_item() {
		return is_user_logged_in()
			? wp_get_current_user()->ID
			: false;
	}

	/**
	 * When used within the customizer, this will set temporary values to all overwritten fieds.
	 *
	 * @since 3.0
	 */
	public function set_temporary_values() {
		add_filter( "get_user_metadata", array( $this, 'overwrite_value' ), 10, 4 );
	}

	/**
	 * Overwrites a value when user meta is being retrieved.
	 * Used when values are changed in the customizer but should not be saved yet.
	 *
	 * @since 3.0
	 *
	 * @param  mixed  $value     The value that would be normally returned.
	 * @param  int    $object_id The ID othe object whose value is being retrieved.
	 * @param  string $meta_key  The key of the needed meta value.
	 * @param  bool   $single    Indiates if a single value is needed, having that key.
	 * @return mixed
	 */
	public function overwrite_value( $value, $object_id, $meta_key, $single ) {
		if( $this->user_id == $object_id ) {
			if( isset( $this->values[ $meta_key ] ) ) {
				return array( $this->values[ $meta_key ] );
			}
		}

		return $value;
	}
}
