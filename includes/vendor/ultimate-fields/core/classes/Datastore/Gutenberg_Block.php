<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Handles the values of Gutenberg blocks.
 *
 * @since 3.0
 */
class Gutenberg_Block extends Datastore {
	/**
	 * Saves information about the current block, if any.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Datastore\Gutenberg_Block
	 */
	protected static $current_block;

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
	 * Returns all of the datastore's values.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_all() {
		return $this->values;
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

		# This option will select the current block
		$options[] = array(
			'type'    => 'block',
			'keyword' => 'block',
			'item'    => false
		);

		return $options;
	}

	/**
	 * Saves the data about the block that is currently displayed in the front-end.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $attributes The attributes of the block.
	 */
	public static function set_current_block( $attributes ) {
		return self::$current_block = new self( $attributes );
	}

	/**
	 * Returns either the datastore for the current block or a blank one.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Datastore\Gutenberg_Block;
	 */
	public static function get_current_datastore() {
		return self::$current_block
			? self::$current_block
			: new self;
	}
}
