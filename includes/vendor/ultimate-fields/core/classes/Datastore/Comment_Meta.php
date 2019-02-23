<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Works with comment meta.
 *
 * @since 3.0
 */
class Comment_Meta extends Datastore {
	protected $comment_id = null;

    /**
	 * Retrieve a single value from the database.
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	function get_value_from_db( $key ) {
		$all = get_comment_meta( $this->comment_id );

		if( ! isset( $all[ $key ] ) ) {
			return null;
		}

		return get_comment_meta( $this->comment_id, $key, true );
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
		return update_comment_meta( $this->comment_id, $key, $value );
	}

	/**
	 * Deletes values based on their key, no matter if they are multiple or not
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the setting
	 */
	function delete_value_from_db( $key ) {
		return delete_user_meta( $this->comment_id, $key, true );
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
	 * Sets the comment ID to work with.
	 *
	 * @since 3.0
	 *
	 * @param int $id The ID of the comment.
	 */
	public function set_id( $id ) {
		$this->comment_id = $id;
	}

	/**
	 * Returns the ID of the comment the datastore gets meta for.
	 *
	 * @since 3.0
	 * @return int
	 */
	public function get_id() {
		return $this->comment_id;
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

		# Allows comments to be selected
		$options[] = array(
			'type'    => 'comment',
			'keyword' => 'comment',
			'item'    => true
		);

		return $options;
	}
}
