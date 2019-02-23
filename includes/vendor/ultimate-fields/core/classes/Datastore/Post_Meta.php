<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Connects various containers with th epost meta API.
 *
 * @since 3.0
 */
class Post_Meta extends Datastore {
	protected $post_id = null;

    /**
	 * Retrieve a single value from the database.
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	function get_value_from_db( $key ) {
		if( ! metadata_exists( 'post', $this->post_id, $key ) ) {
			return null;
		}

		return get_post_meta( $this->post_id, $key, true );
	}

	/**
	 * Adds slashes deeply into an array.
	 *
	 * @since 3.0.3
	 *
	 * @param array $value The value to add slashes to.
	 * @return array
	 */
	public function addslashes_deep( $value ) {
		if( ! is_array( $value ) ) {
			return $value;
		}

		$added = array();
		foreach( $value as $key => $item ) {
			if( is_string( $item ) ) {
				$item = addslashes( $item );
			} elseif( is_array( $item ) ) {
				$item = $this->addslashes_deep( $item );
			}

			$added[ $key ] = $item;
		}

		return $added;
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
		if( is_string( $value ) ) {
			$value = addslashes( $value );
		} elseif( is_array( $value ) ) {
			$value = $this->addslashes_deep( $value );
		}

		return update_post_meta( $this->post_id, $key, $value );
	}

	/**
	 * Deletes values based on their key, no matter if they are multiple or not
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the setting
	 */
	function delete_value_from_db( $key ) {
		return delete_post_meta( $this->post_id, $key, true );
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
	 * Sets the post ID to work with.
	 *
	 * @since 3.0
	 *
	 * @param int $id The ID of the post.
	 */
	public function set_id( $id ) {
		$this->post_id = $id;
	}

	/**
	 * Returns the ID of the post the datastore gets meta for.
	 *
	 * @since 3.0
	 * @return int
	 */
	public function get_id() {
		return $this->post_id;
	}

	/**
	 * When used within the customizer, this will set temporary values to all overwritten fieds.
	 *
	 * @since 3.0
	 */
	public function set_temporary_values() {
		add_filter( "get_post_metadata", array( $this, 'overwrite_value' ), 10, 4 );
	}

	/**
	 * Overwrites a value when post meta is being retrieved.
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
		if( $this->post_id == $object_id ) {
			if( isset( $this->values[ $meta_key ] ) ) {
				return array( $this->values[ $meta_key ] );
			}
		}

		return $value;
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

		# This option will select the current post
		$options[] = array(
			'type'    => 'post_meta',
			'keyword' => false,
			'item'    => false
		);

		foreach( get_post_types() as $slug ) {
			$options[] = array(
				'type'    => 'post_meta',
				'keyword' => $slug,
				'item'    => true
			);
		}

		return $options;
	}

	/**
	 * Loads the current post for the no-keyword-no-item combination.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public static function load_anonymous_item() {
		return get_the_id();
	}
}
