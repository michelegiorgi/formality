<?php
namespace Ultimate_Fields\Helper;

use Iterator;
use Ultimate_Fields\Data_API;

/**
 * Handles the iterations over repeater groups.
 *
 * @since 3.0
 */
class Groups_Iterator implements Iterator {
	/**
	 * Holds the groups, which the iterator is going to work with.
	 *
	 * @since 3.0
	 *
	 * @var Group_Values[]
	 */
	protected $groups = array();

	/**
	 * Holds the current index.
	 *
	 * @since 3.0
	 * @var integer
	 */
	protected $position = 0;

	/**
	 * Holds the source of the value (iterator).
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Helper\Data_Source
	 */
	protected $source;

	/**
	 * Initializes the iterator.
	 *
	 * @since 3.0
	 *
	 * @param Group_Values[] The existing groups.
	 */
    public function __construct( $groups ) {
        $this->groups = $groups;
    }

	/**
	 * When working with loops like have_groups() and the_group(),
	 * the iterator must start with a negative index.
	 *
	 * @since 3.0
	 *
	 * @return Groups_Iterator
	 */
	public function loop_mode() {
		$iterator = new Groups_Iterator_Loop( $this->groups );
		$iterator->set_source( $this->source );
		return $iterator;
	}

	/**
	 * Rewinds the iterator to its beginning.
	 *
	 * @since 3.0
	 */
    public function rewind() {
        $this->position = 0;
    }

	/**
	 * Returns the current element.
	 *
	 * @since 3.0
	 *
	 * @return Group_Values
	 */
    public function current() {
        return isset( $this->groups[ $this->position ] )
			? $this->groups[ $this->position ]
			: $this->groups[ 0 ];
    }

	/**
	 * Returns the key for the current position.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
    public function key() {
        return $this->position;
    }

	/**
	 * Goes to the next group/position.
	 *
	 * @since 3.0
	 */
    public function next() {
        ++$this->position;
    }

	/**
	 * Indicates if there is a group at the current position.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
    public function valid() {
        return isset( $this->groups[ $this->position ] );
    }

	/**
	 * Returns the internal groups for debugging purposes.
	 *
	 * @since 3.0
	 *
	 * @return Group_Values[]
	 */
	public function __debugInfo() {
		return $this->groups;
	}

	/**
	 * Introduces a source (context) for the value.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Helper\Data_Source
	 */
	public function set_source( $source ) {
		$this->source = $source;
		return $this;
	}

	/**
	 * Returns the source (context) for the value.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Helper\Data_Source
	 */
	public function get_source( $source ) {
		return $this->source;
	}

	/**
	 * Sends the current data of theiterator back to the data API.
	 *
	 * @since 3.0
	 *
	 * @return bool Indicates if the save was successfull.
	 */
	public function save() {
		if( is_null( $this->source ) ) {
			return false;
		}

		$value = $this->export();
		$api = Data_API::instance();
		return $api->update( $this->source, $value );
	}

	/**
	 * Exports the value to a simple array.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$value = array();

		foreach( $this->groups as $group ) {
			$value[] = $group->get_raw_values();
		}

		return $value;
	}
}
