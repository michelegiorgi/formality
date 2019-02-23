<?php
namespace Ultimate_Fields\Helper\Object\Item;

use Ultimate_Fields\Helper\Object\Item;

/**
 * Handles a post within object fields.
 *
 * @since 3.0
 */
class Post extends Item {
	/**
	 * Returns the ID of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->item->ID;
	}

	/**
	 * Returns the URL of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_url() {
		return get_permalink( $this->item->ID );
	}

	/**
	 * Returns the title/name of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_title() {
		return get_the_title( $this->item->ID );
	}
}
