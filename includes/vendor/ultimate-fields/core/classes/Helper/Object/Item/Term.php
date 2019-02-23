<?php
namespace Ultimate_Fields\Helper\Object\Item;

use Ultimate_Fields\Helper\Object\Item;

/**
 * Handles a term within object fields.
 *
 * @since 3.0
 */
class Term extends Item {
	/**
	 * Returns the ID of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->item->term_id;
	}

	/**
	 * Returns the URL of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_url() {
		return get_term_link( $this->item );
	}

	/**
	 * Returns the title/name of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_title() {
		return $this->item->name;
	}
}
