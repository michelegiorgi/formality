<?php
namespace Ultimate_Fields\Helper\Object;

/**
 * Works as a wrapper for objects within the object(s) field, allowing a unified
 * way of accessing links, titles, IDs, exports and etc.
 *
 * @since 3.0
 */
abstract class Item {
	/**
	 * Holds the WP_* object that the item wraps.
	 *
	 * @since 3.0
	 * @var object
	 */
	protected $item;

	/**
	 * Creates a new item.
	 *
	 * @since 3.0
	 *
	 * @param object $item The item to work with.
	 */
	public function __construct( $item ) {
		$this->item = $item;
	}

	/**
	 * Returns the ID of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	abstract public function get_id();

	/**
	 * Returns the URL of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	abstract public function get_url();

	/**
	 * Returns the title/name of the item.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	abstract public function get_title();

	/**
	 * Returns the original item.
	 *
	 * @since 3.0
	 *
	 * @return object
	 */
	public function get_original() {
		return $this->item;
	}
}
