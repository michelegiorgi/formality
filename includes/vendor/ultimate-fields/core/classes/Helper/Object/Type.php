<?php
namespace Ultimate_Fields\Helper\Object;

/**
 * This class works as a base for types of objects, which can be plugged to the object field.
 *
 * @since 3.0
 */
abstract class Type {
	/**
	 * Holds the arguments for loading objects.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $args;

	/**
	 * Once fetched, this holds retrieved objects.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $objects = array();

	/**
	 * Holds the per-page limit for retrieving items.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $per_page = 5;

	/**
	 * Holds the page that is to be retrieved.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $page = 1;

	/**
	 * Idicates that the filter is impossible to use.
	 *
	 * @since 3.0
	 * @var bool
	 */
	public $impossible = false;

	/**
	 * Creates an instance of the type loader.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args Args for the loader, different for each loader.
	 */
	public function __construct( $args ) {
		$this->args = $args;
	}

	/**
	 * Loads items based on the arguments, which the type was created with.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	abstract public function load();

	/**
	 * Returns items based on their IDs.
	 *
	 * @since 3.0
	 *
	 * @param int[]    $ids The IDs of the item to retrieve.
	 * @return mixed[]      The prepared original-type items.
	 */
	abstract public function prepare( $ids );

	/**
	 * Exports an item, eventually locating it by ID.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item Either the ID of the item or one that is already found/prepared.
	 * @return mixed[]    The exported item, whose data is ready for JavaScript.
	 */
	abstract public function prepare_item( $item );

	/**
	 * Returns the URL of an item.
	 *
	 * @param int $item The ID of the item.
	 * @return URL
	 */
	abstract public function get_item_link( $item );

	/**
	 * Returns the current arguments for the type.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * Replaces the internal arguments of the type.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The arguments to use.
	 */
	public function set_args( $args ) {
		$this->args = $args;
	}
}
