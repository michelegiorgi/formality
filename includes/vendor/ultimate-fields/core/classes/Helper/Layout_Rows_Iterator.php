<?php
namespace Ultimate_Fields\Helper;

use Iterator;
use Ultimate_Fields\Data_API;

/**
 * Handles the iterations over layout rows.
 *
 * @since 3.0
 */
class Layout_Rows_Iterator implements Iterator {
	/**
	 * Holds the rows, which the iterator is going to work with.
	 *
	 * @since 3.0
	 *
	 * @var Groups_Iterator[]
	 */
	protected $rows = array();

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
	 * @param Groups_Iterator[] The existing rows.
	 */
    public function __construct( $rows ) {
        $this->rows = $rows;
    }

	/**
	 * When working with loops like have_rows() and the_row(),
	 * the iterator must start with a negative index.
	 *
	 * @since 3.0
	 *
	 * @return Groups_Iterator
	 */
	public function loop_mode() {
		return new Layout_Rows_Iterator_Loop( $this->rows );
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
	 * @return Groups_Iterator
	 */
    public function current() {
        return $this->rows[ $this->position ];
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
	 * Goes to the next row/position.
	 *
	 * @since 3.0
	 */
    public function next() {
        ++$this->position;
    }

	/**
	 * Indicates if there is a row at the current position.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
    public function valid() {
        return isset( $this->rows[ $this->position ] );
    }

	/**
	 * Returns the internal rows for debugging purposes.
	 *
	 * @since 3.0
	 *
	 * @return Groups_Iterator[]
	 */
	public function __debugInfo() {
		return $this->rows;
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
		# If there is no source, associated with the value, this will not work
		if( is_null( $this->source ) ) {
			return false;
		}

		$value = array();
		foreach( $this->rows as $row ) {
			$value[] = $row->export();
		}

		$api = Data_API::instance();
		return $api->update( $this->source, $value );
	}
}
